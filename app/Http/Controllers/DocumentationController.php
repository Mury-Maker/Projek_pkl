<?php
// File: app/Http/Controllers/DocumentationController.php

namespace App\Http\Controllers;

use App\Models\NavMenu;
use App\Models\DocsContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;

class DocumentationController extends Controller
{

    public function index(): View|RedirectResponse
    {
        $defaultCategory = 'epesantren';

        $firstMenu = NavMenu::where('category', $defaultCategory)
            ->where('menu_child', 0)
            ->orderBy('menu_order', 'asc')
            ->first();

        if (!$firstMenu) {
            return view('docs.welcome', [
                'title' => 'Selamat Datang di Dokumentasi',
                'message' => 'Belum ada konten dokumentasi yang dibuat. Silakan login sebagai admin untuk memulai.'
            ]);
        }
        
        $pageSlug = Str::slug($firstMenu->menu_nama);

        return redirect()->route('docs', [
            'category' => $defaultCategory,
            'page' => $pageSlug
        ]);
    }

    /**
     * Menampilkan halaman dokumentasi yang spesifik.
     */
    public function show($category, $page = null): View|RedirectResponse
    {
        if (is_null($page)) {
            $firstMenu = NavMenu::where('category', $category)
                ->where('menu_child', 0)
                ->orderBy('menu_order', 'asc')
                ->first();

            if (!$firstMenu) {
                abort(404, 'Dokumentasi untuk kategori ini tidak ditemukan.');
            }

            $pageSlug = Str::slug($firstMenu->menu_nama);
            
            return redirect()->route('docs', ['category' => $category, 'page' => $pageSlug]);
        }

        $allMenus = NavMenu::where('category', $category)->orderBy('menu_order')->get();
        $navigation = NavMenu::buildTree($allMenus);

        $selectedNavItem = $allMenus->first(function ($menu) use ($page) {
            if (Str::slug($menu->menu_nama) === $page) {
                return true;
            }
            $menuLinkPath = parse_url($menu->menu_link, PHP_URL_PATH);
            $requestedPath = "docs/{$menu->category}/{$page}";
            if ($menuLinkPath && Str::endsWith($menuLinkPath, $requestedPath)) {
                return true;
            }
            return false;
        });

        $menuId = $selectedNavItem->menu_id ?? 0;
        $menusWithDocs = NavMenu::with('docsContent')->find($menuId);

        $viewPath = "docs.pages.{$category}.{$page}";
        $filePath = resource_path("views/".str_replace('.', '/', $viewPath).".blade.php");

        if (!File::exists($filePath)) {
            File::ensureDirectoryExists(resource_path("views/docs/pages/{$category}"));
            File::put(
                $filePath,
                <<<BLADE
@guest
<div class="ck-content">
    {!! \$contentDocs->docsContent->content ?? "Konten Belum Tersedia" !!}
</div>
@endguest

@auth
<div class="menuid">
    </div>
    <div class="main-container">
        <div class="editor-container" id="editor-container">
            <form action="{{ route('docs.save', ['menu_id' => \$menu_id]) }}" method="POST">
                @csrf
                <textarea name="content" id="editor" class="ckeditor">
                    {{ \$contentDocs->docsContent->content ?? "Konten Belum Tersedia" }}
                </textarea>
                <div class="buttons">
                    <button type="submit" class="btn btn-simpan">Simpan</button>
                    <a href="{{ route('docs', ['category' => \$currentCategory, 'page' => \$currentPage]) }}" class="btn btn-batal">Batal</a>
                </form>
                <form action="{{ route('docs.delete', ['menu_id' => \$menu_id]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus konten ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-hapus">Hapus</button>
                </form>
            </div>
        </div>
    </div>
@endauth
BLADE
            );
        }

        return view('docs.index', [
            'title'           => 'Dokumentasi ' . Str::headline($category),
            'navigation'      => $navigation,
            'currentCategory' => $category,
            'currentPage'     => $page,
            'selectedNavItem' => $selectedNavItem,
            'menu_id'         => $menuId,
            // Perbaikan ini sudah benar di sini
            'allParentMenus'  => NavMenu::where('category', $category)->orderBy('menu_nama')->get(['menu_id', 'menu_nama']),
            'viewPath'        => $viewPath,
            'contentDocs'     => $menusWithDocs,
        ]);
    }

    public function saveContent(Request $request, $menu_id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DocsContent::updateOrCreate(
            ['menu_id' => $menu_id],
            ['content' => $request->input('content')]
        );

        return redirect()->back()->with('success', 'Konten berhasil disimpan.');
    }

    public function deleteContent($menu_id)
    {
        $doc = DocsContent::where('menu_id', $menu_id)->first();

        if ($doc) {
            $doc->delete();
            return redirect()->back()->with('success', 'Konten berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Konten tidak ditemukan.');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $category = $request->input('category', 'epesantren');

        if (!$query) {
            return response()->json(['results' => []]);
        }

        $results = [];

        $searchTerm = '%' . strtolower($query) . '%';
        
        $menuMatches = NavMenu::where('category', $category)
            ->whereRaw('LOWER(TRIM(menu_nama)) LIKE ?', [$searchTerm])
            ->get();

        foreach ($menuMatches as $menu) {
            $results[$menu->menu_id] = [
                'id' => $menu->menu_id,
                'name' => $menu->menu_nama,
                'url' => route('docs', ['category' => $menu->category, 'page' => Str::slug($menu->menu_nama)]),
                'context' => 'Judul Menu',
            ];
        }

        $contentMatches = DocsContent::with('menu')
            ->whereHas('menu', function ($q) use ($category) {
                $q->where('category', $category);
            })
            ->where('content', 'LIKE', "%{$query}%")
            ->get();

        foreach ($contentMatches as $content) {
            if ($content->menu && !isset($results[$content->menu->menu_id])) {
                $results[$content->menu->menu_id] = [
                    'id' => $content->menu->menu_id,
                    'name' => $content->menu->menu_nama,
                    'url' => route('docs', ['category' => $content->menu->category, 'page' => Str::slug($content->menu->menu_nama)]),
                    'context' => Str::limit(strip_tags($content->content), 100),
                ];
            }
        }

        return response()->json(['results' => array_values($results)]);
    }
}