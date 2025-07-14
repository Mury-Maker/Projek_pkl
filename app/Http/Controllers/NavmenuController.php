<?php
// File: app/Http/Controllers/NavmenuController.php

namespace App\Http\Controllers;

use App\Models\NavMenu;
use Illuminate\Http\Request;
use App\Models\DocsContent; // Pastikan ini ada untuk relasi konten
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\Collection; // Penting: Tambahkan ini jika belum ada

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
            ->where('menu_status', 0) // Hanya menu aktif yang bisa jadi parent
            ->orderBy('menu_nama');

        // PERBAIKAN: Logika untuk mengecualikan menu yang sedang diedit dan anak-anaknya.
        if ($request->has('editing_menu_id')) {
            $editingMenuId = $request->input('editing_menu_id');

            // 1. Exclude the menu itself from the parent list
            $query->where('menu_id', '!=', $editingMenuId);

            // 2. Efficiently exclude descendants of the editing menu to prevent circular references
            // This is a more robust way to get all descendants.
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
            'selectedNavItemId' => null,
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
            'menu_icon' => 'nullable|string|max:30', // Max length 30 sesuai migrasi
            'menu_status' => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            $menu = NavMenu::create([
                'menu_nama' => $request->menu_nama,
                'menu_link' => Str::slug($request->menu_nama), // Link awal akan diperbarui oleh buildTree
                'menu_icon' => $request->menu_icon,
                'menu_child' => $request->menu_child,
                'menu_order' => $request->menu_order,
                'menu_status' => $request->has('menu_status') ? 1 : 0,
                'category' => $request->category,
            ]);

            if ($menu->menu_status == 1) {
                DocsContent::create([
                    'menu_id' => $menu->menu_id, // Menggunakan menu_id
                    'content' => '# ' . $request->menu_nama . "\n\nBelum ada konten untuk halaman ini. Silakan edit untuk menambahkan."
                ]);
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
            'menu_icon' => 'nullable|string|max:30', // Max length 30 sesuai migrasi
            'menu_status' => 'boolean',
        ]);

        // Pencegahan referensi melingkar
        if ($request->menu_child == $navMenu->menu_id) {
            return response()->json(['message' => 'Menu tidak bisa menjadi parent-nya sendiri.'], 422);
        }
        if ($navMenu->isDescendantOf($request->menu_child)) {
             return response()->json(['message' => 'Tidak bisa mengatur sub-menu sebagai parent dari menu induknya.'], 422);
        }

        DB::transaction(function () use ($request, $navMenu) {
            $oldMenuStatus = $navMenu->menu_status;

            $navMenu->update([
                'menu_nama' => $request->menu_nama,
                'menu_icon' => $request->menu_icon,
                'menu_child' => $request->menu_child,
                'menu_order' => $request->menu_order,
                'menu_status' => $request->has('menu_status') ? 1 : 0,
            ]);

            // Handle DocsContent berdasarkan perubahan menu_status
            if ($oldMenuStatus == 1 && $navMenu->menu_status == 0) {
                $navMenu->docsContent()->delete();
            } elseif ($oldMenuStatus == 0 && $navMenu->menu_status == 1) {
                DocsContent::create([
                    'menu_id' => $navMenu->menu_id,
                    'content' => '# ' . $request->menu_nama . "\n\nBelum ada konten untuk halaman ini. Silakan edit untuk menambahkan."
                ]);
            } elseif ($navMenu->menu_status == 1) {
                $navMenu->docsContent()->firstOrCreate(
                    ['menu_id' => $navMenu->menu_id],
                    ['content' => '# ' . $request->menu_nama . "\n\nBelum ada konten untuk halaman ini. Silakan edit untuk menambahkan."]
                );
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
     */
    protected function deleteChildrenAndContent($parentId)
    {
        $children = NavMenu::where('menu_child', $parentId)->get();
        foreach ($children as $child) {
            $this->deleteChildrenAndContent($child->menu_id);
            $child->docsContent()->delete();
            $child->delete();
        }
    }

    /**
     * Update konten dari CKEditor.
     */
    public function updateMenuContent(Request $request, NavMenu $navMenu)
    {
        $request->validate(['content' => 'required|string']);
        $navMenu->docsContent()->update(['content' => $request->content]);
        return response()->json(['success' => 'Konten berhasil diperbarui']);
    }
   
    public function storeCategory(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:50|unique:navmenu,category', // unique:nama_tabel,nama_kolom
        ]);

        $rawCategoryName = $request->input('category');
        $newCategorySlug = Str::slug($rawCategoryName);
        $displayCategoryName = Str::headline($rawCategoryName);

        DB::transaction(function () use ($newCategorySlug, $displayCategoryName) {
            // Buat default "Beranda" menu untuk kategori baru
            $menu = NavMenu::create([
                'menu_nama' => 'Beranda ' . $displayCategoryName,
                'menu_link' => $newCategorySlug . '/beranda-' . Str::slug($displayCategoryName),
                'menu_icon' => 'fa-solid fa-home',
                'menu_child' => 0, // Top-level menu
                'menu_order' => 0,
                'menu_status' => 1, // Ini adalah halaman yang memiliki konten
                'category' => $newCategorySlug,
            ]);

            DocsContent::create([
                'menu_id' => $menu->menu_id, // Menggunakan menu_id
                'content' => '# Beranda ' . $displayCategoryName . "\n\nSelamat datang di dokumentasi untuk " . $displayCategoryName . ". Ini adalah halaman beranda Anda."
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
            // Update the category field for all menus under the old slug
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
        DB::transaction(function () use ($categorySlug) {
            $menus = NavMenu::where('category', $categorySlug)->get();

            foreach ($menus as $menu) {
                $menu->docsContent()->delete();
                $menu->delete();
            }
        });

        return response()->json(['success' => 'Kategori dan semua menu di dalamnya berhasil dihapus!']);
    }
}
