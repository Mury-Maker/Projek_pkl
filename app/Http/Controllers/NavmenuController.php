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
        $children = NavMenu::where('menu_child', $parentId)->pluck('menu_id')->toArray();

        foreach ($children as $childId) {
            $descendantIds[] = $childId;
            $descendantIds = array_merge($descendantIds, $this->getDescendantIds($childId));
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
            'selectedNavItemId' => null, // This might need to be dynamic if you want the active state to persist
            'currentCategory' => $category, // Pass current category for correct link generation
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
            'menu_order' => 'required|integer',
            'category' => 'required|string',
            'menu_icon' => 'nullable|string|max:30',
            'menu_status' => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            $menu = NavMenu::create([
                'menu_nama' => $request->menu_nama,
                'menu_link' => Str::slug($request->menu_nama),
                'menu_icon' => $request->menu_icon,
                'menu_child' => $request->menu_child,
                'menu_order' => $request->menu_order,
                'menu_status' => $request->has('menu_status') ? 1 : 0,
                'category' => $request->category,
            ]);

            // If menu has content status, create content entries
            if ($menu->menu_status == 1) {
                if ($menu->menu_child !== 0) { // If it's a child menu with content
                    $contentTypes = ['UAT', 'Pengkodean', 'Database'];
                    foreach ($contentTypes as $type) {
                        DocsContent::create([
                            'menu_id' => $menu->menu_id,
                            'content' => '# ' . $request->menu_nama . ' - ' . $type . "\n\nBelum ada konten untuk halaman ini. Silakan edit untuk menambahkan.",
                            'title' => $type
                        ]);
                    }
                } else { // If it's a top-level menu with content
                    DocsContent::create([
                        'menu_id' => $menu->menu_id,
                        'content' => '# ' . $request->menu_nama . "\n\nBelum ada konten untuk halaman ini. Silakan edit untuk menambahkan.",
                        'title' => 'Default'
                    ]);
                }
            }
        });

        return response()->json(['success' => 'Menu berhasil ditambahkan!']);
    }

    /**
     * Memperbarui menu yang ada.
     */
    public function update(Request $request, NavMenu $navMenu)
    {
        $request->validate([
            'menu_nama' => 'required|string|max:50',
            'menu_child' => 'required|integer',
            'menu_order' => 'required|integer',
            'menu_icon' => 'nullable|string|max:30',
            'menu_status' => 'boolean',
        ]);

        if ($request->menu_child == $navMenu->menu_id) {
            return response()->json(['message' => 'Menu tidak bisa menjadi parent-nya sendiri.'], 422);
        }
        if (method_exists($navMenu, 'isDescendantOf') && $navMenu->isDescendantOf($request->menu_child)) {
             return response()->json(['message' => 'Tidak bisa mengatur sub-menu sebagai parent dari menu induknya.'], 422);
        }


        DB::transaction(function () use ($request, $navMenu) {
            $oldMenuStatus = $navMenu->menu_status;
            $oldMenuChild = $navMenu->menu_child;

            $navMenu->update([
                'menu_nama' => $request->menu_nama,
                'menu_link' => Str::slug($request->menu_nama), // 🚨 PERBAIKAN: Pastikan link diperbarui
                'menu_icon' => $request->menu_icon,
                'menu_child' => $request->menu_child,
                'menu_order' => $request->menu_order,
                'menu_status' => $request->has('menu_status') ? 1 : 0,
            ]);

            $isNowContentMenu = $navMenu->menu_status == 1;
            $wasContentMenu = $oldMenuStatus == 1;
            $isNowChildMenu = $navMenu->menu_child !== 0;

            if ($isNowContentMenu) {
                if ($isNowChildMenu) {
                    $contentTypes = ['UAT', 'Pengkodean', 'Database'];
                    foreach ($contentTypes as $type) {
                        DocsContent::firstOrCreate(
                            ['menu_id' => $navMenu->menu_id, 'title' => $type],
                            ['content' => '# ' . $request->menu_nama . ' - ' . $type . "\n\nBelum ada konten untuk halaman ini. Silakan edit untuk menambahkan."]
                        );
                    }
                    // 🚨 PERBAIKAN: Jika sebelumnya top-level, hapus konten 'Default'
                    if (!$oldMenuChild && $wasContentMenu) {
                        DocsContent::where('menu_id', $navMenu->menu_id)->where('title', 'Default')->delete();
                    }
                } else { // Is now a top-level menu with content
                    DocsContent::firstOrCreate(
                        ['menu_id' => $navMenu->menu_id, 'title' => 'Default'],
                        ['content' => '# ' . $request->menu_nama . "\n\nBelum ada konten untuk halaman ini. Silakan edit untuk menambahkan."]
                    );
                    // 🚨 PERBAIKAN: Jika sebelumnya child, hapus konten UAT/Pengkodean/Database
                    if ($oldMenuChild !== 0 && $wasContentMenu) {
                           DocsContent::where('menu_id', $navMenu->menu_id)
                               ->whereIn('title', ['UAT', 'Pengkodean', 'Database'])
                               ->delete();
                    }
                }
            } else { // No longer a content menu, delete all associated content
                $navMenu->docsContent()->delete();
            }
        });

        return response()->json(['success' => 'Menu berhasil diperbarui!']);
    }



    /**
     * Menghapus menu dan semua sub-menunya secara rekursif.
     */
    public function destroy(NavMenu $navMenu)
    {
        DB::transaction(function () use ($navMenu) {
            $navMenu->docsContent()->delete(); // Hapus konten menu ini
            $navMenu->children()->each(function ($child) {
                $this->destroy($child); // Rekursif untuk anak-anak
            });
            $navMenu->delete(); // Hapus menu itu sendiri
        });

        return response()->json(['success' => 'Menu dan semua sub-menu berhasil dihapus!']);
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
        // 🚨 PERBAIKAN 1: Guard sisi server untuk kategori default
        if ($categorySlug === 'epesantren') {
            return response()->json(['message' => 'Kategori "ePesantren" tidak dapat dihapus.'], 403);
        }

        try {
            DB::transaction(function () use ($categorySlug) {
                // Ambil semua menu yang termasuk dalam kategori ini
                $menusInThisCategory = NavMenu::where('category', $categorySlug)->get();

                // 🚨 PERBAIKAN 2: Iterasi dan panggil metode destroy rekursif
                foreach ($menusInThisCategory as $menu) {
                    // Hanya panggil destroy pada menu level teratas (menu_child = 0)
                    // karena metode destroy() itu sendiri akan menangani anak-anaknya secara rekursif
                    if ($menu->menu_child === 0) {
                        $this->destroy($menu); // Panggil metode destroy di controller ini
                    }
                }
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