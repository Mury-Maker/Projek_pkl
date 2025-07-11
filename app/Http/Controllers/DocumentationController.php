<?php

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
                    'categories' => $categories,
                ]);
            }
        }

        // Jika tidak ada menu valid, tampilkan fallback
        return $this->renderNoContentFallback($defaultCategory, collect());
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
        // Mengambil Field Kategori
        $categories = NavMenu::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        // Mengambil semua nama menu
        $allMenus = NavMenu::where('category', $category)->orderBy('menu_order')->get();

        // Membuat Tree daru nav menu
        $navigation = NavMenu::buildTree($allMenus);


        // KASUS 1: Jika tidak ada $page yang diberikan (misalnya /docs/epesantren)
        if (is_null($page)) {
            $firstMenuInCat = $allMenus->where('menu_child', 0)->sortBy('menu_order')->first();

            if ($firstMenuInCat && trim($firstMenuInCat->menu_nama) !== '') {
                $pageSlug = Str::slug($firstMenuInCat->menu_nama);
                if ($pageSlug !== '') {
                    return redirect()->route('docs', [
                        'category' => $category,
                        'page' => $pageSlug,
                    ]);
                }
            }

            // Jika tidak ada menu valid
            return $this->renderNoContentFallback($category, $navigation);
        }



        // KASUS 2: Jika $page diberikan, cari item navigasi yang cocok
        $selectedNavItem = $allMenus->first(function ($menu) use ($page) {
            return Str::slug($menu->menu_nama) === $page;
        });

        if (!$selectedNavItem) {
            // Jika $page diberikan tetapi TIDAK ADA item navigasi yang cocok,
            // ini berarti halaman tersebut tidak ditemukan. Maka kita kembalikan 404.
            abort(404, 'Halaman dokumentasi tidak ditemukan.');
        }

        // KASUS 3: Jika $selectedNavItem ditemukan (halaman valid)
        $menuId = $selectedNavItem->menu_id;
        $menusWithDocs = NavMenu::with('docsContent')->find($menuId);

        // --- PENTING: Hapus SEMUA LOGIKA PEMBUATAN FILE BLADE DI SINI ---
        // Anda tidak lagi membutuhkan:
        // $pageSlugForFile = Str::slug($selectedNavItem->menu_nama);
        // $viewPath = "docs.pages.{$pageSlugForFile}";
        // $filePath = resource_path("views/docs/pages/{$pageSlugForFile}.blade.php");
        // File::ensureDirectoryExists(resource_path("views/docs/pages"));
        // if (!File::exists($filePath)) {
        //     File::put(...);
        // }
        // --- AKHIR PENGHAPUSAN ---
        return view('docs.index', [
            'title'             => ucfirst($selectedNavItem->menu_nama) . ' - Dokumentasi ' . Str::headline($category),
            'navigation'        => $navigation,
            'currentCategory'   => $category,
            'currentPage'       => $page,
            'selectedNavItem'   => $selectedNavItem,
            'menu_id'           => $menuId,
            'allParentMenus'    => NavMenu::where('category', $category)->orderBy('menu_nama')->get(['menu_id', 'menu_nama']),
            'contentDocs'       => $menusWithDocs,
            'categories' => $categories
        ]);
    }

    private function renderNoContentFallback($category, $navigation): View
    {
        $fallbackPageName = 'no-content-available';


        $categories = NavMenu::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        // Konten untuk fallback, bisa langsung diisi di sini
        $content = '<h3>Tidak Ada Dokumentasi</h3><p>Belum ada konten dokumentasi yang dibuat untuk kategori ini. Silakan login sebagai **Admin** untuk menambahkan menu dan konten.</p>';
        $contentDocs = (object)['docsContent' => (object)['content' => $content]];

        return view('docs.index', [
            'title'             => 'Dokumentasi ' . Str::headline($category),
            'navigation'        => $navigation,
            'currentCategory'   => $category,
            'currentPage'       => $fallbackPageName,
            'selectedNavItem'   => null,
            'menu_id'           => 0,
            'allParentMenus'    => collect(), // Tidak ada parent menus jika tidak ada konten
            'contentDocs'       => $contentDocs,
            'categories'        => $categories,
        ]);
    }

    /**
     * Menyimpan atau memperbarui konten dokumentasi untuk menu tertentu.
     * Hanya bisa diakses oleh user dengan role 'admin'.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $menu_id
     * @return \Illuminate\Http\RedirectResponse
     */
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
