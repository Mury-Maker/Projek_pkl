<?php

namespace App\Http\Controllers;

use App\Models\NavMenu;
use App\Models\DocsContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth; // Pastikan ini ada
// use Illuminate\Support\Facades\Log; // Anda bisa un-comment ini untuk logging jika perlu debugging lanjutan

class DocumentationController extends Controller
{
    /**
     * Menampilkan halaman indeks dokumentasi default.
     */
    public function index(): View|RedirectResponse
    {
        // Pastikan user terautentikasi untuk mengakses dokumentasi.
        // Jika tidak, arahkan ke halaman login.
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $defaultCategory = 'epesantren';

        // Ambil menu pertama dari kategori default
        $firstMenu = NavMenu::where('category', $defaultCategory)
            ->where('menu_child', 0)
            ->orderBy('menu_order', 'asc')
            ->first();

        // Jika belum ada menu, tampilkan pesan selamat datang
        if (!$firstMenu) {
            return view('docs.welcome', [
                'title' => 'Selamat Datang di Dokumentasi',
                'message' => 'Belum ada konten dokumentasi yang dibuat. Silakan login sebagai admin untuk memulai.'
            ]);
        }

        // Arahkan ke halaman dokumentasi pertama dari kategori default
        $pageSlug = Str::slug($firstMenu->menu_nama);
        return redirect()->route('docs', [
            'category' => $defaultCategory,
            'page' => $pageSlug
        ]);
    }

    /**
     * Menampilkan halaman dokumentasi yang spesifik berdasarkan kategori dan halaman.
     */
    public function show($category, $page = null): View|RedirectResponse
    {
        // Pastikan user terautentikasi untuk melihat dokumentasi.
        // Jika tidak, arahkan ke halaman login.
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Jika halaman tidak spesifik, arahkan ke halaman pertama dalam kategori
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

        // Jika file Blade belum ada, buat dengan konten kondisional
        if (!File::exists($filePath)) {
            File::ensureDirectoryExists(resource_path("views/docs/pages/{$category}"));
            File::put(
                $filePath,
                <<<BLADE
{{-- Selalu tampilkan konten dokumentasi --}}
<div class="ck-content">
    {!! \$contentDocs->docsContent->content ?? "Konten Belum Tersedia" !!}
</div>

{{-- Tampilkan editor hanya jika user terautentikasi DAN memiliki role 'admin' --}}
@auth
    @if(Auth::user()->role === 'admin')
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
                        <button type="submit" class="btn btn-simpan">Update</button>
                        <a href="{{ route('docs', ['category' => \$currentCategory, 'page' => \$currentPage]) }}" class="btn btn-batal">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endauth
BLADE
            );
        }

        return view('docs.index', [
            'title'             => 'Dokumentasi ' . Str::headline($category),
            'navigation'        => $navigation,
            'currentCategory'   => $category,
            'currentPage'       => $page,
            'selectedNavItem'   => $selectedNavItem,
            'menu_id'           => $menuId,
            'allParentMenus'    => NavMenu::where('category', $category)->orderBy('menu_nama')->get(['menu_id', 'menu_nama']),
            'viewPath'          => $viewPath,
            'contentDocs'       => $menusWithDocs,
        ]);
    }

    /**
     * Menyimpan atau memperbarui konten dokumentasi.
     */
    public function saveContent(Request $request, $menu_id)
    {
        // Pemeriksaan role: Hanya user yang terautentikasi dengan role 'admin' yang bisa menyimpan konten.
        // Jika tidak, hentikan eksekusi dengan error 403 (Forbidden).
        if (!Auth::check() || (Auth::user()->role ?? '') !== 'admin') {
            // Log::warning('Unauthorized attempt to save content by user: ' . (Auth::check() ? Auth::user()->email : 'Guest') . ' with role: ' . (Auth::user()->role ?? 'NULL'));
            abort(403, 'Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }

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

    /**
     * Menghapus konten dokumentasi.
     */
    public function deleteContent($menu_id)
    {
        // Pemeriksaan role: Hanya user yang terautentikasi dengan role 'admin' yang bisa menghapus konten.
        // Jika tidak, hentikan eksekusi dengan error 403 (Forbidden).
        if (!Auth::check() || (Auth::user()->role ?? '') !== 'admin') {
            // Log::warning('Unauthorized attempt to delete content by user: ' . (Auth::check() ? Auth::user()->email : 'Guest') . ' with role: ' . (Auth::user()->role ?? 'NULL'));
            abort(403, 'Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }

        $doc = DocsContent::where('menu_id', $menu_id)->first();

        if ($doc) {
            $doc->delete();
            return redirect()->back()->with('success', 'Konten berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Konten tidak ditemukan.');
    }

    /**
     * Mencari konten dokumentasi.
     */
    public function search(Request $request)
    {
        // Untuk fungsionalitas search, Anda bisa memutuskan apakah harus diakses oleh semua user
        // (termasuk user biasa) atau hanya admin. Berdasarkan pertanyaan sebelumnya,
        // diasumsikan search juga hanya bisa diakses setelah login.
        // Middleware 'auth' pada route sudah menangani ini.
        
        $query = $request->input('query');
        
        if (!$query) {
            return response()->json(['results' => []]);
        }

        $results = [];
        $searchTerm = '%' . strtolower($query) . '%';
        
        // Cari di semua menu
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

        // Cari di konten docs
        $contentMatches = DocsContent::with('menu')
            ->where('content', 'LIKE', "%{$query}%")
            ->get();

        foreach ($contentMatches as $content) {
            if ($content->menu) {
                $key = $content->menu->menu_id . '-' . $content->menu->category;
                if (!isset($results[$key])) {
                    $results[$key] = [
                        'id' => $content->menu->menu_id,
                        'name' => $content->menu->menu_nama,
                        'category_name' => Str::headline($content->menu->category),
                        'url' => route('docs', ['category' => $content->menu->category, 'page' => Str::slug($content->menu->menu_nama)]),
                        'context' => Str::limit(strip_tags($content->content), 100),
                    ];
                }
            }
        }

        return response()->json(['results' => array_values($results)]);
    }
}