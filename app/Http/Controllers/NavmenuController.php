<?php
// File: app/Http/Controllers/NavmenuController.php (Revisi)

namespace App\Http\Controllers;

use App\Models\NavMenu;
use App\Models\UseCase;
use Illuminate\Http\Request;
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
     */
    public function getParentMenus(Request $request, $category)
    {
        $query = NavMenu::where('category', $category)
            ->where('menu_status', 0) // Hanya menu "folder" yang bisa jadi parent
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
        $queue = new Collection([$parentId]);

        while (!$queue->isEmpty()) {
            $currentParentId = $queue->shift();

            $children = NavMenu::where('menu_child', $currentParentId)->pluck('menu_id')->toArray();

            foreach ($children as $childId) {
                if (!in_array($childId, $descendantIds)) {
                    $descendantIds[] = $childId;
                    $queue->push($childId);
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
            'menu_status' => 'boolean', // 0 = folder, 1 = memiliki daftar use case
        ]);

        $menuExists = NavMenu::where('menu_nama', $request->menu_nama)
            ->where('category', $request->category)
            ->exists();

        if ($menuExists) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'menu_nama' => ['Nama menu sudah digunakan dalam kategori ini!']
                ]
            ], 422);
        }

        $newlyCreatedMenu = null;

        DB::transaction(function () use ($request, &$newlyCreatedMenu) {
            $menu = NavMenu::create([
                'menu_nama' => $request->menu_nama,
                'menu_link' => Str::slug($request->menu_nama), // Link akan menunjuk ke halaman daftar use case
                'menu_icon' => $request->menu_icon,
                'menu_child' => $request->menu_child,
                'menu_order' => $request->menu_order ?? 0,
                'menu_status' => $request->has('menu_status') ? 1 : 0, // 1 jika ini akan punya daftar use case
                'category' => $request->category,
            ]);

            $newlyCreatedMenu = $menu;

            // Jika menu_status adalah 1 (berarti ini akan punya daftar UseCase),
            // kita tidak perlu membuat UseCase default langsung di sini.
            // UseCase akan dibuat ketika admin menambahkannya dari halaman daftar use case.
        });

        return response()->json([
            'success' => 'Menu berhasil ditambahkan!',
            'menu_id' => $newlyCreatedMenu->menu_id,
            'new_menu_nama' => $newlyCreatedMenu->menu_nama,
            'new_menu_link' => $newlyCreatedMenu->menu_link,
            'current_category' => $newlyCreatedMenu->category,
            'menu_status' => $newlyCreatedMenu->menu_status,
        ], 201);
    }    

    /**
     * Memperbarui menu yang ada.
     */
    public function update(Request $request, NavMenu $navMenu)
    {
        $request->validate([
            'menu_nama' => [
                'required',
                'string',
                'max:50',
                Rule::unique('navmenu', 'menu_nama')
                    ->where(function ($query) use ($request) {
                        return $query->where('category', $request->category);
                    })
                    ->ignore($navMenu->menu_id, 'menu_id'),
            ],
            'menu_child' => 'required|integer',
            'menu_order' => 'required|integer',    
            'menu_icon' => 'nullable|string|max:30',
            'menu_status' => 'boolean', // 0 = folder, 1 = memiliki daftar use case
            'category' => 'required|string',
        ]);

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

        DB::transaction(function () use ($request, $navMenu, $oldMenuNama, $oldMenuStatus) {
            $newMenuLink = Str::slug($request->menu_nama);
            $newMenuStatus = $request->has('menu_status') ? 1 : 0;

            $navMenu->update([
                'menu_nama' => $request->menu_nama,
                'menu_link' => $newMenuLink,
                'menu_icon' => $request->menu_icon,
                'menu_child' => $request->menu_child,
                'menu_order' => $request->menu_order ?? 0,    
                'menu_status' => $newMenuStatus,
                'category' => $request->category,
            ]);

            // Jika status menu berubah dari 1 (punya daftar UseCase) menjadi 0 (folder)
            if ($oldMenuStatus == 1 && $newMenuStatus == 0) {
                // Hapus SEMUA UseCase terkait dan data turunannya
                $navMenu->useCases()->delete(); // Gunakan relasi hasMany useCases()
            }
            // Jika status menu berubah dari 0 (folder) menjadi 1 (akan punya daftar UseCase)
            // Tidak perlu ada aksi di sini, UseCase akan dibuat ketika admin menambahkannya
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
        $currentCategory = $navMenu->category;

        try {
            DB::transaction(function () use ($navMenu) {
                // Hapus SEMUA UseCase terkait dan data turunannya (akan di-cascade oleh foreign key)
                $navMenu->useCases()->delete(); // Gunakan relasi hasMany useCases()
                
                // Hapus anak-anak secara rekursif. Asumsi 'children' adalah relasi Eloquent
                $navMenu->children()->each(function ($child) {
                    $this->destroy($child); // Rekursif untuk anak-anak
                });
                $navMenu->delete(); // Hapus menu itu sendiri
            });

            // Redirect ke menu pertama yang tersedia di kategori yang sama
            $nextAvailableMenu = NavMenu::where('category', $currentCategory)
                                         ->orderBy('menu_order')
                                         ->orderBy('menu_id')
                                         ->first();

            $redirectUrl = route('docs', ['category' => 'epesantren']); // Default jika kategori kosong

            if ($nextAvailableMenu) {
                $redirectUrl = route('docs', [
                    'category' => $currentCategory,
                    'page' => Str::slug($nextAvailableMenu->menu_nama)
                ]);
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
     * Mengelola kategori.
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:50|unique:navmenu,category',
        ]);

        $rawCategoryName = $request->input('category');
        $newCategorySlug = Str::slug($rawCategoryName);
        $displayCategoryName = Str::headline($rawCategoryName);

        $newlyCreatedMenu = null; // Inisialisasi variabel

        DB::transaction(function () use ($newCategorySlug, $displayCategoryName, &$newlyCreatedMenu) {
            $menu = NavMenu::create([
                'menu_nama' => 'Beranda ' . $displayCategoryName,
                'menu_link' => $newCategorySlug . '/beranda-' . Str::slug($displayCategoryName),
                'menu_icon' => 'fa-solid fa-home',
                'menu_child' => 0,
                'menu_order' => 0,
                'menu_status' => 1, // Beranda kategori akan memiliki daftar use case
                'category' => $newCategorySlug,
            ]);

            $newlyCreatedMenu = $menu; // Simpan menu yang baru dibuat

            // Buat UseCase default "Pengantar" untuk halaman beranda kategori baru
            UseCase::create([
                'menu_id' => $menu->menu_id,
                'usecase_id' => 'INFO-BERANDA',
                'nama_proses' => 'Informasi Umum',
                'deskripsi_aksi' => '# Informasi Umum Kategori ' . $displayCategoryName . "\n\nIni adalah informasi pengantar untuk kategori ini. Anda dapat menambahkan tindakan-tindakan lain (use cases) di sini.",
                'aktor' => 'Sistem',
                'tujuan' => 'Memberikan gambaran umum kategori.',
                'kondisi_awal' => 'Pengguna mengakses halaman beranda kategori.',
                'kondisi_akhir' => 'Informasi umum ditampilkan.',
                'aksi_reaksi' => 'Pengguna membaca konten.',
                'reaksi_sistem' => 'Sistem menyajikan informasi.',
            ]);
        });

        // 👇 PERBAIKAN DI SINI: Redirect ke halaman beranda kategori yang baru dibuat
        return response()->json([
            'success' => 'Kategori berhasil ditambahkan!',
            'redirect_url' => route('docs', [
                'category' => $newCategorySlug,
                'page' => Str::slug($newlyCreatedMenu->menu_nama) // Menggunakan slug dari nama menu yang baru dibuat
            ])
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

            // Coba temukan 'Informasi Umum' use case untuk kategori ini dan perbarui
            $homeMenu = NavMenu::where('category', $newCategorySlug)
                               ->where('menu_child', 0)
                               ->where('menu_order', 0)
                               ->where('menu_status', 1) // Pastikan ini adalah menu yang punya daftar UseCase
                               ->first();

            if ($homeMenu) {
                // Perbarui nama menu
                if (Str::startsWith($homeMenu->menu_nama, 'Beranda ' . $oldDisplayCategoryName)) {
                    $homeMenu->update([
                        'menu_nama' => 'Beranda ' . $newDisplayCategoryName,
                        'menu_link' => $newCategorySlug . '/beranda-' . Str::slug($newDisplayCategoryName),
                    ]);
                }

                // Perbarui UseCase 'Informasi Umum' jika ada
                $infoUseCase = $homeMenu->useCases()->where('usecase_id', 'INFO-BERANDA')->first();
                if ($infoUseCase) {
                    $infoUseCase->update([
                        'nama_proses' => 'Informasi Umum Kategori ' . $newDisplayCategoryName,
                        'deskripsi_aksi' => '# Informasi Umum Kategori ' . $newDisplayCategoryName . "\n\nIni adalah informasi pengantar untuk kategori ini.",
                    ]);
                }
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
        if ($categorySlug === 'epesantren') {
            return response()->json(['message' => 'Kategori "ePesantren" tidak dapat dihapus.'], 403);
        }

        try {
            DB::transaction(function () use ($categorySlug) {
                // Ambil semua menu ID dalam kategori ini
                $menuIdsInThisCategory = NavMenu::where('category', $categorySlug)->pluck('menu_id')->toArray();

                // Hapus semua UseCase yang terkait dengan menu-menu ini
                if (!empty($menuIdsInThisCategory)) {
                    // Ini akan memicu cascade delete untuk UAT, Report, Database Data
                    UseCase::whereIn('menu_id', $menuIdsInThisCategory)->delete();
                }

                // Hapus semua menu di kategori ini
                NavMenu::where('category', $categorySlug)->delete();
            });

            // 👇👇👇 PERBAIKAN DI SINI: Logika Redirect Setelah Hapus Kategori 👇👇👇
            // Coba temukan menu pertama di kategori 'epesantren' yang valid
            $defaultCategoryRedirect = route('docs.index'); // Default fallback ke rute docs.index (yang akan me-redirect ke menu pertama yang valid)

            $firstMenuInEpesantren = NavMenu::where('category', 'epesantren')
                                             ->orderBy('menu_order')
                                             ->orderBy('menu_id')
                                             ->first();

            if ($firstMenuInEpesantren) {
                // Jika ada menu di epesantren, arahkan ke sana
                $defaultCategoryRedirect = route('docs', [
                    'category' => 'epesantren',
                    'page' => Str::slug($firstMenuInEpesantren->menu_nama)
                ]);
            } else {
                // Jika tidak ada menu sama sekali di epesantren, arahkan ke root docs
                // DocumentationController->index() akan menangani redirect ke kategori/halaman yang valid atau fallback
                $defaultCategoryRedirect = route('docs.index');
            }

            return response()->json([
                'success' => 'Kategori dan semua menu di dalamnya berhasil dihapus!',
                'redirect_url' => $defaultCategoryRedirect // Kirim URL redirect yang sudah pasti valid
            ]);
            // 👆👆👆 AKHIR PERBAIKAN 👇👇👇

        } catch (QueryException $e) {
            Log::error('Database error deleting category ' . $categorySlug . ': ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus kategori. Kesalahan database.'], 500);
        } catch (\Exception $e) {
            Log::error('General error deleting category ' . $categorySlug . ': ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan tidak terduga saat menghapus kategori.'], 500);
        }
    }
}