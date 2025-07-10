<?php
// File: app/Http/Controllers/NavmenuController.php

namespace App\Http\Controllers;

use App\Models\NavMenu;
use Illuminate\Http\Request;
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
            'menu_icon' => 'nullable|string|max:255',
            'menu_status' => 'boolean', // Pastikan ini ada untuk validasi status
        ]);

        DB::transaction(function () use ($request) {
            $menu = NavMenu::create([
                'menu_nama' => $request->menu_nama,
                'menu_link' => '#',
                'menu_icon' => $request->menu_icon,
                'menu_child' => $request->menu_child,
                'menu_order' => $request->menu_order,
                'menu_status' => $request->has('menu_status') ? 1 : 0,
                'category' => $request->category,
            ]);

            $menu->docsContent()->create(['content' => '# ' . $request->menu_nama]);
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
            'menu_icon' => 'nullable|string|max:255',
            'menu_status' => 'boolean', // Pastikan ini ada untuk validasi status
        ]);

        $navMenu->update([
            'menu_nama' => $request->menu_nama,
            'menu_icon' => $request->menu_icon,
            'menu_child' => $request->menu_child,
            'menu_order' => $request->menu_order,
            'menu_status' => $request->has('menu_status') ? 1 : 0,
        ]);

        return response()->json(['success' => 'Menu berhasil diperbarui!']);
    }

    /**
     * Menghapus menu dan semua sub-menunya secara rekursif.
     */
    public function destroy(NavMenu $navMenu)
    {
        DB::transaction(function () use ($navMenu) {
            $this->deleteChildrenAndContent($navMenu->menu_id);
            $navMenu->docsContent()->delete();
            $navMenu->delete();
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
        // Validasi input
        $request->validate([
            'category' => 'required|string|max:50',
        ]);

        // Cek apakah kategori sudah ada
        $existing = NavMenu::where('category', $request->category)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori sudah ada.'
            ]);
        }

        // Buat root menu untuk kategori baru
        $menu = NavMenu::create([
            'menu_nama' => '',
            'menu_link' => '#',
            'menu_icon' => '',
            'menu_child' => 0,
            'menu_order' => 0,
            'menu_status' => 1,
            'category' => $request->category,
        ]);

        // Buat konten default untuk root menu ini
        $menu->docsContent()->create([
            'content' => '# Beranda ' . $request->category,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan.',
        ]);
    }


}