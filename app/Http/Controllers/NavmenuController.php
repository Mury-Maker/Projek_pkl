<?php
// File: app/Http/Controllers/NavmenuController.php

namespace App\Http\Controllers;

use App\Models\NavMenu;
use Illuminate\Http\Request;
use App\Models\DocsContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class NavmenuController extends Controller
{
    /**
     * Mengambil data satu menu untuk form edit.
     */
    public function getMenuData(NavMenu $navMenu)
    {
        return response()->json($navMenu);
    }

    /**
     * Mengambil daftar parent menu yang *potensial* untuk dropdown.
     * Mengembalikan semua menu aktif dalam kategori yang bisa menjadi parent,
     * kecuali menu yang sedang diedit dan turunannya.
     */
    public function getParentMenus(Request $request, $category)
    {
        $query = NavMenu::where('category', $category)
            ->where('menu_status', 0) // Hanya menu aktif yang bisa jadi parent (yaitu, tidak punya konten sendiri)
            ->orderBy('menu_nama');

        if ($request->has('editing_menu_id')) {
            $editingMenuId = $request->input('editing_menu_id');
            $query->where('menu_id', '!=', $editingMenuId);
            $descendantIds = $this->getDescendantIds($editingMenuId);

            if (!empty($descendantIds)) {
                $query->whereNotIn('menu_id', $descendantIds);
            }
        }

        $parents = $query->get(['menu_id', 'menu_nama']);
        return response()->json($parents);
    }

    /**
     * Helper function to recursively get all descendant IDs for a given parent.
     */
    private function getDescendantIds($parentId): array
    {
        $descendantIds = [];
        $queue = new Collection([$parentId]); // Mulai dengan parent yang diberikan

        while (!$queue->isEmpty()) {
            $currentParentId = $queue->shift(); // Ambil elemen pertama dari antrian

            $children = NavMenu::where('menu_child', $currentParentId)->pluck('menu_id')->toArray();

            foreach ($children as $childId) {
                if (!in_array($childId, $descendantIds)) {
                    $descendantIds[] = $childId;
                    $queue->push($childId); // Tambahkan anak ke antrian untuk diproses lebih lanjut
                }
            }
        }

        return array_unique($descendantIds);
    }


    /**
     * Mengambil semua menu untuk refresh sidebar (dalam bentuk HTML).
     */
    public function getAllMenusForSidebar($category)
    {
        $allMenus = NavMenu::where('category', $category)->orderBy('menu_order')->get();
        $navigation = NavMenu::buildTree($allMenus);

        $html = View::make('docs._menu_item', [
            'items' => $navigation,
            'editorMode' => true,
            'selectedNavItemId' => null,
            'currentCategory' => $category,
        ])->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Menyimpan menu baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'menu_nama' => 'required|string|max:50',
            'menu_child' => 'required|integer',
            'menu_order' => 'nullable|integer',
            'category' => 'required|string',
            'menu_icon' => 'nullable|string|max:30',
            'menu_status' => 'boolean',
        ]);

        // Cek apakah menu dengan nama dan kategori yang sama sudah ada
        // Menggunakan validasi unique Laravel lebih disarankan untuk konsistensi
        // 'unique:nav_menus,menu_nama,NULL,id,category,' . $request->category,
        // Namun, jika Anda ingin tetap menggunakan cara manual ini, pastikan responsnya sama dengan validasi Laravel
        $menuExists = NavMenu::where('menu_nama', $request->menu_nama)
            ->where('category', $request->category)
            ->exists();

        if ($menuExists) {
            // Mengembalikan respons error validasi yang konsisten dengan Laravel
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'menu_nama' => ['Nama menu sudah digunakan dalam kategori ini!']
                ]
            ], 422);
        }

        // Variabel untuk menyimpan instance menu yang baru dibuat agar bisa diakses di luar transaction
        $newlyCreatedMenu = null;

        DB::transaction(function () use ($request, &$newlyCreatedMenu) { // Gunakan '&' untuk pass-by-reference
            $menu = NavMenu::create([
                'menu_nama' => $request->menu_nama,
                'menu_link' => Str::slug($request->menu_nama),
                'menu_icon' => $request->menu_icon,
                'menu_child' => $request->menu_child,
                'menu_order' => $request->menu_order ?? 0,
                'menu_status' => $request->has('menu_status') ? 1 : 0,
                'category' => $request->category,
            ]);

            $newlyCreatedMenu = $menu; // Simpan instance menu yang baru dibuat

            // --- START: Logika pembuatan DocsContent yang disamakan dengan update ---
            if ($menu->menu_status == 1) {
                $contentTypesToManage = ['UAT', 'Pengkodean', 'Database'];
                $defaultContentPlaceholder = "\n\nBelum ada konten untuk halaman ini. Silakan edit untuk menambahkan.";

                foreach ($contentTypesToManage as $type) {
                    DocsContent::create([ // Gunakan create karena ini menu baru, tidak perlu firstOrCreate
                        'menu_id' => $menu->menu_id,
                        'title' => $type,
                        'content' => '# ' . $request->menu_nama . ' - ' . $type . $defaultContentPlaceholder,
                    ]);
                }
            }
            // --- END: Logika pembuatan DocsContent yang disamakan dengan update ---
        });

        // --- PENTING: Pastikan semua data ini dikembalikan ---
        // Mengembalikan data dari $newlyCreatedMenu yang sudah diinisialisasi di dalam transaction
        return response()->json([
            'success' => 'Menu berhasil ditambahkan!',
            'menu_id' => $newlyCreatedMenu->menu_id,
            'new_menu_nama' => $newlyCreatedMenu->menu_nama,
            'new_menu_link' => $newlyCreatedMenu->menu_link,
            'current_category' => $newlyCreatedMenu->category,
            'menu_status' => $newlyCreatedMenu->menu_status,
        ], 201); // Menggunakan status 201 Created untuk resource baru
    }      

    /**
     * Memperbarui menu yang ada.
     */
    public function update(Request $request, NavMenu $navMenu)
    {
        // --- START: Perubahan Validasi untuk menu_nama unik (DENGAN SCOPE KATEGORI) ---
        $request->validate([
            'menu_nama' => [
                'required',
                'string',
                'max:50',
                // Menggunakan Rule::unique dengan kondisi where untuk category
                // Ini akan memeriksa keunikan menu_nama HANYA dalam category yang sama,
                // dan mengabaikan record yang sedang di-update.
                Rule::unique('navmenu', 'menu_nama')
                    ->where(function ($query) use ($request) {
                        return $query->where('category', $request->category);
                    })
                    ->ignore($navMenu->menu_id, 'menu_id'),
            ],
            'menu_child' => 'required|integer',
            'menu_order' => 'required|integer', 
            'menu_icon' => 'nullable|string|max:30',
            'menu_status' => 'boolean',
            'category' => 'required|string', // Pastikan category juga divalidasi
        ]);
        // --- END: Perubahan Validasi ---

        if ($request->menu_child == $navMenu->menu_id) {
            return response()->json(['message' => 'Menu tidak bisa menjadi parent-nya sendiri.'], 422);
        }

        $descendantIds = $this->getDescendantIds($navMenu->menu_id);
        if (in_array($request->menu_child, $descendantIds)) {
            return response()->json(['message' => 'Tidak bisa mengatur sub-menu sebagai parent dari menu induknya.'], 422);
        }

        $oldMenuNama = $navMenu->menu_nama;
        $oldMenuLink = $navMenu->menu_link;
        $oldMenuStatus = $navMenu->menu_status;
        $oldMenuChild = $navMenu->menu_child;

        $defaultContentPlaceholder = "\n\nBelum ada konten untuk halaman ini. Silakan edit untuk menambahkan.";

        DB::transaction(function () use ($request, $navMenu, $oldMenuNama, $oldMenuLink, $oldMenuStatus, $oldMenuChild, $defaultContentPlaceholder) {
            $newMenuLink = Str::slug($request->menu_nama);

            $navMenu->update([
                'menu_nama' => $request->menu_nama,
                'menu_link' => $newMenuLink,
                'menu_icon' => $request->menu_icon,
                'menu_child' => $request->menu_child,
                'menu_order' => $request->menu_order ?? 0, 
                'menu_status' => $request->has('menu_status') ? 1 : 0,
                'category' => $request->category, // Pastikan category juga diupdate jika perlu
            ]);

            $isNowContentMenu = $navMenu->menu_status == 1;
            $wasContentMenu = $oldMenuStatus == 1;
            $isNowChildMenu = $navMenu->menu_child !== 0;
            $wasChildMenu = $oldMenuChild !== 0;
            $menuNamaChanged = ($request->menu_nama !== $oldMenuNama);

            $contentTypesToManage = ['UAT', 'Pengkodean', 'Database'];

            if ($isNowContentMenu) {
                if ($wasContentMenu) {
                    if (!$isNowChildMenu && $wasChildMenu === false) {
                        DocsContent::where('menu_id', $navMenu->menu_id)->where('title', 'Default')->delete();
                    }

                    foreach ($contentTypesToManage as $type) {
                        $docsContent = DocsContent::firstOrNew(
                            ['menu_id' => $navMenu->menu_id, 'title' => $type]
                        );

                        $newContentHeader = '# ' . $request->menu_nama . ' - ' . $type;
                        $oldContentHeaderPrefix = '# ' . $oldMenuNama . ' - ' . $type;

                        if ($docsContent->exists) {
                            if ($menuNamaChanged && Str::startsWith($docsContent->content, $oldContentHeaderPrefix)) {
                                $docsContent->content = substr_replace($docsContent->content, $newContentHeader, 0, strlen($oldContentHeaderPrefix));
                            }
                        } else {
                            $docsContent->content = $newContentHeader . $defaultContentPlaceholder;
                        }
                        $docsContent->save();
                    }

                } else {
                    foreach ($contentTypesToManage as $type) {
                        $docsContent = DocsContent::firstOrNew(
                            ['menu_id' => $navMenu->menu_id, 'title' => $type]
                        );
                        $docsContent->content = '# ' . $request->menu_nama . ' - ' . $type . $defaultContentPlaceholder;
                        $docsContent->save();
                    }
                    DocsContent::where('menu_id', $navMenu->menu_id)->where('title', 'Default')->delete();
                }
            } else {
                $navMenu->docsContent()->delete();
            }
        });

        $currentCategory = $navMenu->category;

        return response()->json([
            'success' => 'Menu berhasil diperbarui!',
            'menu_id' => $navMenu->menu_id,
            'new_menu_nama' => $navMenu->menu_nama,
            'new_menu_link' => $navMenu->menu_link,
            'old_menu_link' => $oldMenuLink,
            'current_category' => $currentCategory,
            'menu_status' => $navMenu->menu_status,
        ]);
    }

    /**
     * Menghapus menu dan semua sub-menunya secara rekursif.
     */
    public function destroy(NavMenu $navMenu)
    {
        $currentCategory = $navMenu->category; // Simpan kategori menu yang akan dihapus

        try {
            DB::transaction(function () use ($navMenu) {
                $navMenu->docsContent()->delete(); // Hapus konten menu ini
                // Hapus anak-anak secara rekursif. Asumsi 'children' adalah relasi Eloquent
                $navMenu->children()->each(function ($child) {
                    $this->destroy($child); // Rekursif untuk anak-anak
                });
                $navMenu->delete(); // Hapus menu itu sendiri
            });

            // Setelah penghapusan, temukan menu pertama yang tersisa di kategori yang sama
            $nextAvailableMenu = NavMenu::where('category', $currentCategory)
                                        ->orderBy('menu_order')
                                        ->orderBy('menu_id') // Tambahkan order by id untuk konsistensi
                                        ->first();

            $redirectUrl = route('docs', ['category' => $currentCategory]); // Default ke kategori utama

            if ($nextAvailableMenu) {
                // Jika menu yang tersisa memiliki status konten, arahkan ke halamannya
                if ($nextAvailableMenu->menu_status == 1) {
                    $redirectUrl = route('docs', [
                        'category' => $currentCategory,
                        'page' => $nextAvailableMenu->menu_link // Menggunakan menu_link yang sudah ada slug
                    ]);
                } else {
                    // Jika menu yang tersisa tidak memiliki konten, arahkan ke kategori itu saja
                    $redirectUrl = route('docs', ['category' => $currentCategory]);
                }
            } else {
                // Jika tidak ada menu tersisa di kategori ini, arahkan ke kategori 'epesantren'
                // atau kategori default lainnya
                $redirectUrl = route('docs', ['category' => 'epesantren']);
            }
            
            return response()->json([
                'success' => 'Menu dan semua sub-menu berhasil dihapus!',
                'redirect_url' => $redirectUrl
            ]);

        } catch (QueryException $e) {
            Log::error('Database error deleting menu ' . $navMenu->menu_id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus menu. Kesalahan database.'], 500);
        } catch (\Exception $e) {
            Log::error('General error deleting menu ' . $navMenu->menu_id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan tidak terduga saat menghapus menu.'], 500);
        }
    }

    /**
     * Helper untuk menghapus children dan konten secara rekursif.
     * This method is redundant since `destroy` handles recursion.
     * It's good to keep helper methods within the class they're primarily used,
     * but the main `destroy` method is already recursive.
     * I'm keeping this commented out for reference, but it's not used.
     */
    /*
    protected function deleteChildrenAndContent($parentId)
    {
        $children = NavMenu::where('menu_child', $parentId)->get();
        foreach ($children as $child) {
            $this->deleteChildrenAndContent($child->menu_id);
            $child->docsContent()->delete();
            $child->delete();
        }
    }
    */

    /**
     * Update konten dari CKEditor.
     */
    public function updateMenuContent(Request $request, NavMenu $navMenu)
    {
        $request->validate([
            'content' => 'required|string',
            'content_type' => 'nullable|string' // Add content_type validation
        ]);

        $contentType = $request->input('content_type', 'Default'); // Get content_type or default to 'Default'

        // Update or create content based on menu_id and title
        DocsContent::updateOrCreate(
            ['menu_id' => $navMenu->menu_id, 'title' => $contentType],
            ['content' => $request->input('content')]
        );

        return response()->json(['success' => 'Konten berhasil diperbarui']);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:50|unique:navmenu,category',
        ]);

        $rawCategoryName = $request->input('category');
        $newCategorySlug = Str::slug($rawCategoryName);
        $displayCategoryName = Str::headline($rawCategoryName);

        DB::transaction(function () use ($newCategorySlug, $displayCategoryName) {
            $menu = NavMenu::create([
                'menu_nama' => 'Beranda ' . $displayCategoryName,
                'menu_link' => $newCategorySlug . '/beranda-' . Str::slug($displayCategoryName),
                'menu_icon' => 'fa-solid fa-home',
                'menu_child' => 0,
                'menu_order' => 0,
                'menu_status' => 1,
                'category' => $newCategorySlug,
            ]);

            DocsContent::create([
                'menu_id' => $menu->menu_id,
                'content' => '# Beranda ' . $displayCategoryName . "\n\nSelamat datang di dokumentasi untuk " . $displayCategoryName . ". Ini adalah halaman beranda Anda.",
                'title' => 'Default'
            ]);
        });

        return response()->json([
            'success' => 'Kategori berhasil ditambahkan!',
            'new_slug' => $newCategorySlug,
        ]);
    }

    /**
     * Memperbarui kategori yang ada.
     */
    public function updateCategory(Request $request, $categorySlug)
    {
        $request->validate([
            'category' => 'required|string|max:50',
        ]);

        $newCategoryName = $request->input('category');
        $newCategorySlug = Str::slug($newCategoryName);

        $existingCategory = NavMenu::where('category', $newCategorySlug)
                                    ->where('category', '!=', $categorySlug)
                                    ->first();

        if ($existingCategory) {
            return response()->json([
                'success' => false,
                'message' => 'Nama kategori baru sudah ada.'
            ], 409);
        }

        DB::transaction(function () use ($categorySlug, $newCategorySlug, $newCategoryName) {
            NavMenu::where('category', $categorySlug)->update(['category' => $newCategorySlug]);

            $oldDisplayCategoryName = Str::headline(str_replace('-', ' ', $categorySlug));
            $newDisplayCategoryName = Str::headline($newCategoryName);

            $homeMenu = NavMenu::where('category', $newCategorySlug)
                               ->where('menu_child', 0)
                               ->where('menu_order', 0)
                               ->first();

            if ($homeMenu && Str::startsWith($homeMenu->menu_nama, 'Beranda ' . $oldDisplayCategoryName)) {
                $homeMenu->update([
                    'menu_nama' => 'Beranda ' . $newDisplayCategoryName,
                    'menu_link' => $newCategorySlug . '/beranda-' . Str::slug($newDisplayCategoryName),
                ]);
                $homeMenu->docsContent()->update([
                    'content' => '# Beranda ' . $newDisplayCategoryName
                ]);
            }
        });

        return response()->json([
            'success' => 'Kategori berhasil diperbarui!',
            'new_slug' => $newCategorySlug
        ]);
    }

    /**
     * Menghapus kategori dan semua menu serta kontennya.
     */
    public function destroyCategory($categorySlug)
    {
        // Guard sisi server untuk kategori default
        if ($categorySlug === 'epesantren') {
            return response()->json(['message' => 'Kategori "ePesantren" tidak dapat dihapus.'], 403);
        }

        try {
            DB::transaction(function () use ($categorySlug) {
                // Ambil semua menu ID dalam kategori ini
                $menuIdsInThisCategory = NavMenu::where('category', $categorySlug)->pluck('menu_id')->toArray();

                // Hapus semua konten dokumentasi yang terkait dengan menu-menu ini
                if (!empty($menuIdsInThisCategory)) {
                    DocsContent::whereIn('menu_id', $menuIdsInThisCategory)->delete();
                }

                // Hapus semua menu di kategori ini (tidak perlu rekursif karena kita menghapus berdasarkan kategori)
                NavMenu::where('category', $categorySlug)->delete();
            });

            return response()->json(['success' => 'Kategori dan semua menu di dalamnya berhasil dihapus!']);

        } catch (QueryException $e) {
            Log::error('Database error deleting category ' . $categorySlug . ': ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus kategori. Pastikan tidak ada data terkait yang mencegah penghapusan (cek foreign key constraints atau logic rekursif).'], 500);
        } catch (\Exception $e) {
            Log::error('General error deleting category ' . $categorySlug . ': ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan tidak terduga saat menghapus kategori.'], 500);
        }
    }
}