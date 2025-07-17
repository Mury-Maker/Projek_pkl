<?php
// File: app/Http/Controllers/DocumentationController.php

namespace App\Http\Controllers;

use App\Models\NavMenu;
use App\Models\DocsContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DocumentationController extends Controller
{
    /**
     * Menampilkan halaman indeks dokumentasi default.
     */
    public function index(): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $defaultCategory = 'epesantren';

        $firstMenu = NavMenu::where('category', $defaultCategory)
            ->where('menu_child', 0)
            ->orderBy('menu_order', 'asc')
            ->first();

        $categories = NavMenu::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        if ($firstMenu && trim($firstMenu->menu_nama) !== '') {
            $pageSlug = Str::slug($firstMenu->menu_nama);
            if ($pageSlug !== '') {
                return redirect()->route('docs', [
                    'category' => $defaultCategory,
                    'page' => $pageSlug,
                ]);
            }
        }

        return $this->renderNoContentFallback($defaultCategory, collect());
    }

    /**
     * Menampilkan halaman dokumentasi yang spesifik berdasarkan kategori dan halaman.
     */
    public function show($category, $page = null): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    
        $categories = NavMenu::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');
    
        $allMenus = NavMenu::where('category', $category)->orderBy('menu_order')->get();
        $navigation = NavMenu::buildTree($allMenus);
    
        // Jika belum ada page (slug)
        if (is_null($page)) {
            // Cari menu parent dengan menu_status = 1
            $parentMenus = $allMenus->where('menu_child', 0)->where('menu_status', 1);
    
            foreach ($parentMenus as $menu) {
                $hasContent = DocsContent::where('menu_id', $menu->menu_id)->exists();
                if ($hasContent) {
                    return redirect()->route('docs', [
                        'category' => $category,
                        'page' => Str::slug($menu->menu_nama)
                    ]);
                }
            }
    
            // Jika tidak ada parent menu yang punya konten, cari child menu teratas yang punya konten dan status aktif
            $childMenus = $allMenus->where('menu_child', '!=', 0)->where('menu_status', 1);
    
            foreach ($childMenus as $menu) {
                $hasContent = DocsContent::where('menu_id', $menu->menu_id)->exists();
                if ($hasContent) {
                    return redirect()->route('docs', [
                        'category' => $category,
                        'page' => Str::slug($menu->menu_nama)
                    ]);
                }
            }
    
            // Fallback kalau tidak ada konten sama sekali
            return $this->renderNoContentFallback($category, $navigation);
        }
    
        // ===================== Jika page sudah ditentukan =========================
        $selectedNavItem = $allMenus->first(function ($menu) use ($page) {
            return Str::slug($menu->menu_nama) === $page;
        });
    
        if (!$selectedNavItem) {
            abort(404, 'Halaman dokumentasi tidak ditemukan.');
        }
    
        $menuId = $selectedNavItem->menu_id;
        $contentDocs = null;
        $contentTypes = [];
        $activeContentType = request()->query('content_type', 'Default');
    
        $docsContents = DocsContent::where('menu_id', $menuId)->get();
    
        if ($docsContents->isNotEmpty()) {
            if ($selectedNavItem->menu_child !== 0) {
                $contentTypes = $docsContents->pluck('title')->toArray();
                $contentDocs = $docsContents->firstWhere('title', $activeContentType);
                if (!$contentDocs) {
                    $contentDocs = $docsContents->first();
                }
            } else {
                $contentDocs = $docsContents->firstWhere('title', 'Default');
            }
        }
    
        if (!$contentDocs) {
            $contentDocs = (object)['content' => null, 'title' => $activeContentType];
        }
    
        return view('docs.index', [
            'title'             => ucfirst($selectedNavItem->menu_nama) . ' - Dokumentasi ' . Str::headline($category),
            'navigation'        => $navigation,
            'currentCategory'   => $category,
            'currentPage'       => $page,
            'selectedNavItem'   => $selectedNavItem,
            'menu_id'           => $menuId,
            'allParentMenus'    => NavMenu::where('category', $category)->orderBy('menu_nama')->get(['menu_id', 'menu_nama']),
            'contentDocs'       => $contentDocs,
            'allDocsContents'   => $docsContents,
            'contentTypes'      => $contentTypes,
            'categories'        => $categories
        ]);
    }    

    private function renderNoContentFallback($category, $navigation): View
    {
        $fallbackPageName = 'no-content-available';

        $categories = NavMenu::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $content = '<h3>Tidak Ada Dokumentasi</h3><p>Belum ada konten dokumentasi yang dibuat untuk kategori ini. Silakan login sebagai **Admin** untuk menambahkan menu dan konten.</p>';
        $contentDocs = (object)['content' => $content, 'title' => 'Default']; // Add 'title' key

        return view('docs.index', [
            'title'             => 'Dokumentasi ' . Str::headline($category),
            'navigation'        => $navigation,
            'currentCategory'   => $category,
            'currentPage'       => $fallbackPageName,
            'selectedNavItem'   => null,
            'menu_id'           => 0,
            'allParentMenus'    => collect(),
            'contentDocs'       => $contentDocs,
            'allDocsContents'   => collect(), // Empty collection
            'contentTypes'      => [], // Empty array
            'categories'        => $categories,
        ]);
    }

    /**
     * Menyimpan atau memperbarui konten dokumentasi.
     */
    public function saveContent(Request $request, $menu_id)
    {
        if (!Auth::check() || (Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'content_type' => 'nullable|string', // Validate content_type
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        $contentType = $request->input('content_type', 'Default');

        DocsContent::updateOrCreate(
            ['menu_id' => $menu_id, 'title' => $contentType], // Use menu_id and title for unique identification
            ['content' => $request->input('content')]
        );

        return response()->json(['success' => 'Konten berhasil disimpan!']);
    }

    /**
     * Menghapus konten dokumentasi.
     */
    public function deleteContent(Request $request, $menu_id)
    {
        if (!Auth::check() || (Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }

        $contentType = $request->input('content_type', 'Default'); // Get content_type from request

        $doc = DocsContent::where('menu_id', $menu_id)
                          ->where('title', $contentType) // Specify the content type to delete
                          ->first();

        if ($doc) {
            $doc->delete();
            return response()->json(['success' => 'Konten berhasil dihapus.']);
        }

        return response()->json(['error' => 'Konten tidak ditemukan.'], 404);
    }

    /**
     * Mencari konten dokumentasi.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['results' => []]);
        }

        $results = [];
        $searchTerm = '%' . strtolower($query) . '%';

        $menuMatches = NavMenu::whereRaw('LOWER(TRIM(menu_nama)) LIKE ?', [$searchTerm])
            ->get();

        foreach ($menuMatches as $menu) {
            $results[$menu->menu_id . '-' . $menu->category] = [
                'id' => $menu->menu_id,
                'name' => $menu->menu_nama,
                'category_name' => Str::headline($menu->category),
                'url' => route('docs', ['category' => $menu->category, 'page' => Str::slug($menu->menu_nama)]),
                'context' => 'Judul Menu',
            ];
        }

        $contentMatches = DocsContent::with('menu')
            ->where('content', 'LIKE', "%{$query}%")
            ->get();

        foreach ($contentMatches as $content) {
            if ($content->menu) {
                $key = $content->menu->menu_id . '-' . $content->menu->category . '-' . Str::slug($content->title);
                if (!isset($results[$key])) {
                    $results[$key] = [
                        'id' => $content->menu->menu_id,
                        'name' => $content->menu->menu_nama,
                        'category_name' => Str::headline($content->menu->category),
                        'url' => route('docs', ['category' => $content->menu->category, 'page' => Str::slug($content->menu->menu_nama), 'content_type' => $content->title]),
                        'context' => Str::limit(strip_tags($content->content), 100),
                    ];
                }
            }
        }

        return response()->json(['results' => array_values($results)]);
    }
}