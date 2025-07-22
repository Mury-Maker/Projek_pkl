<?php
// File: app/Http/Controllers/DocumentationController.php (Full Code)

namespace App\Http\Controllers;

use App\Models\NavMenu;
use App\Models\UseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\ReportData;
use App\Models\UatData;
use App\Models\DatabaseData;



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

        // Selalu coba temukan menu pertama yang valid untuk defaultCategory
        $firstContentMenu = NavMenu::where('category', $defaultCategory)
                                   ->where('menu_status', 1) // Prioritaskan menu dengan konten
                                   ->orderBy('menu_order', 'asc')
                                   ->first();

        if ($firstContentMenu) {
            return redirect()->route('docs', [
                'category' => $defaultCategory,
                'page' => Str::slug($firstContentMenu->menu_nama),
            ]);
        } else {
            // Jika tidak ada menu dengan status 1 di defaultCategory,
            // cari menu (folder atau lainnya) yang ada di defaultCategory
            $firstAnyMenu = NavMenu::where('category', $defaultCategory)
                                    ->orderBy('menu_order', 'asc')
                                    ->first();

            if ($firstAnyMenu) {
                // Redirect ke menu pertama yang ditemukan di kategori default
                return redirect()->route('docs', [
                    'category' => $defaultCategory,
                    'page' => Str::slug($firstAnyMenu->menu_nama),
                ]);
            }
        }

        // Fallback terakhir jika tidak ada menu sama sekali di kategori default
        // Ini akan menampilkan halaman "Tidak Ada Konten"
        return $this->renderNoContentFallback($defaultCategory);
    }

    /**
     * Menampilkan halaman dokumentasi yang spesifik.
     * Dapat menampilkan daftar use case atau detail satu use case.
     */
    public function show($category, $page = null, $useCaseSlug = null): View|RedirectResponse
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

        // Temukan NavMenu yang sedang aktif berdasarkan $page slug
        $selectedNavItem = $allMenus->first(function ($menu) use ($page) {
            return Str::slug($menu->menu_nama) === $page;
        });

        // 👇 PERBAIKAN DI SINI: Jika selectedNavItem tidak ditemukan, redirect ke category default atau fallback
        if (!$selectedNavItem) {
            // Coba redirect ke halaman index kategori yang diminta
            $firstMenuInCategory = NavMenu::where('category', $category)
                                         ->orderBy('menu_order')
                                         ->first();

            if ($firstMenuInCategory) {
                return redirect()->route('docs', [
                    'category' => $category,
                    'page' => Str::slug($firstMenuInCategory->menu_nama),
                ]);
            } else {
                // Jika bahkan tidak ada menu di kategori ini, gunakan fallback umum
                return $this->renderNoContentFallback($category);
            }
        }
        // 👆 AKHIR PERBAIKAN 👆


        $menuId = $selectedNavItem->menu_id;
        $viewData = [
            'title'             => ucfirst(Str::headline($selectedNavItem->menu_nama)) . ' - Dokumentasi ' . Str::headline($category),
            'navigation'        => $navigation,
            'currentCategory'   => $category,
            'currentPage'       => $page,
            'selectedNavItem'   => $selectedNavItem,
            'menu_id'           => $menuId,
            'allParentMenus'    => NavMenu::where('category', $category)->orderBy('menu_nama')->get(['menu_id', 'menu_nama']),
            'categories'        => $categories,
            'activeContentType' => request()->query('content_type', 'UAT'),
        ];

        // SKENARIO 1: Menampilkan Daftar Use Case untuk suatu NavMenu (index tindakan)
        if ($selectedNavItem->menu_status == 1 && is_null($useCaseSlug)) {
            $useCases = UseCase::where('menu_id', $selectedNavItem->menu_id)->orderBy('id', 'desc')->get();
            $viewData['useCases'] = $useCases;
            return view('docs.use_case_index', $viewData);
        }
        // SKENARIO 2: Menampilkan Detail SATU Use Case
        elseif ($selectedNavItem->menu_status == 1 && !is_null($useCaseSlug)) {
            $singleUseCase = UseCase::with(['uatData', 'reportData', 'databaseData'])
                                    ->where('menu_id', $selectedNavItem->menu_id)
                                    ->where(function($query) use ($useCaseSlug) {
                                        $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])
                                              ->orWhere('usecase_id', $useCaseSlug);
                                    })
                                    ->first();

            if (!$singleUseCase) {
                // Jika use case spesifik tidak ditemukan, redirect ke halaman daftar use case menu ini
                return redirect()->route('docs.use_case_detail', [
                    'category' => $category,
                    'page' => Str::slug($selectedNavItem->menu_nama) // Kembali ke halaman daftar use case menu ini
                ]);
            }

            // Memaksa load relasi dan konversi ke array untuk pengiriman data yang bersih
            $viewData['singleUseCase'] = $singleUseCase->toArray();
            $viewData['singleUseCase']['uat_data'] = $singleUseCase->uatData->toArray();
            $viewData['singleUseCase']['report_data'] = $singleUseCase->reportData->toArray();
            $viewData['singleUseCase']['database_data'] = $singleUseCase->databaseData->toArray();

            $viewData['contentTypes'] = ['UAT', 'Report', 'Database'];
            return view('docs.use_case_detail_page', $viewData);
        }
        // SKENARIO 3: Menu adalah Folder (menu_status == 0)
        elseif ($selectedNavItem->menu_status == 0) {
            return view('docs.folder_page', $viewData);
        }

        // Fallback jika tidak sesuai skenario di atas
        // Ini seharusnya jarang terpanggil jika logika di atas sudah baik
        return $this->renderNoContentFallback($category);
    }

    /**
     * Menampilkan halaman detail untuk satu entri UAT.
     */
    public function showUatDetailPage($category, $page, $useCaseSlug, $uatId): View
    {
        if (!Auth::check()) { // Pastikan pengguna terautentikasi
            return redirect()->route('login');
        }

        // Temukan NavMenu yang sedang aktif
        $selectedNavItem = NavMenu::where('category', $category)
                                  ->where(function($query) use ($page) {
                                      $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($page)]);
                                  })
                                  ->firstOrFail();

        // Temukan UseCase utama
        $useCase = UseCase::where('menu_id', $selectedNavItem->menu_id)
                          ->where(function($query) use ($useCaseSlug) {
                              $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])
                                    ->orWhere('usecase_id', $useCaseSlug);
                          })
                          ->firstOrFail();

        // Temukan data UAT spesifik
        $uatData = UatData::where('use_case_id', $useCase->id)
                          ->where('id_uat', $uatId)
                          ->firstOrFail();

        // Siapkan data untuk view
        $allMenus = NavMenu::where('category', $category)->orderBy('menu_order')->get();
        $navigation = NavMenu::buildTree($allMenus);
        $categories = NavMenu::select('category')->whereNotNull('category')->distinct()->pluck('category');

        return view('docs.uat_detail_page', [ // Anda perlu membuat view ini
            'title'             => 'Detail UAT: ' . ($uatData->nama_proses_usecase ?: 'N/A'),
            'navigation'        => $navigation,
            'currentCategory'   => $category,
            'currentPage'       => $page,
            'selectedNavItem'   => $selectedNavItem,
            'menu_id'           => $selectedNavItem->menu_id,
            'allParentMenus'    => NavMenu::where('category', $category)->orderBy('menu_nama')->get(['menu_id', 'menu_nama']),
            'categories'        => $categories,
            'uatData'           => $uatData, // Kirim objek UatData
            'parentUseCase'     => $useCase, // Kirim UseCase induk jika perlu informasi tambahan
        ]);
    }

    /**
     * Menampilkan halaman detail untuk satu entri Report.
     */
    public function showReportDetailPage($category, $page, $useCaseSlug, $reportId): View
    {
        if (!Auth::check()) { // Pastikan pengguna terautentikasi
            return redirect()->route('login');
        }

        $selectedNavItem = NavMenu::where('category', $category)
                                  ->where(function($query) use ($page) {
                                      $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($page)]);
                                  })
                                  ->firstOrFail();

        $useCase = UseCase::where('menu_id', $selectedNavItem->menu_id)
                          ->where(function($query) use ($useCaseSlug) {
                              $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])
                                    ->orWhere('usecase_id', $useCaseSlug);
                          })
                          ->firstOrFail();

        $reportData = ReportData::where('use_case_id', $useCase->id)
                                ->where('id_report', $reportId)
                                ->firstOrFail();

        $allMenus = NavMenu::where('category', $category)->orderBy('menu_order')->get();
        $navigation = NavMenu::buildTree($allMenus);
        $categories = NavMenu::select('category')->whereNotNull('category')->distinct()->pluck('category');

        return view('docs.report_detail_page', [ // Anda perlu membuat view ini
            'title'             => 'Detail Report: ' . ($reportData->nama_report ?: 'N/A'),
            'navigation'        => $navigation,
            'currentCategory'   => $category,
            'currentPage'       => $page,
            'selectedNavItem'   => $selectedNavItem,
            'menu_id'           => $selectedNavItem->menu_id,
            'allParentMenus'    => NavMenu::where('category', $category)->orderBy('menu_nama')->get(['menu_id', 'menu_nama']),
            'categories'        => $categories,
            'reportData'        => $reportData, // Kirim objek ReportData
            'parentUseCase'     => $useCase,
        ]);
    }

    /**
     * Menampilkan halaman detail untuk satu entri Database.
     */
    public function showDatabaseDetailPage($category, $page, $useCaseSlug, $databaseId): View
    {
        if (!Auth::check()) { // Pastikan pengguna terautentikasi
            return redirect()->route('login');
        }

        $selectedNavItem = NavMenu::where('category', $category)
                                  ->where(function($query) use ($page) {
                                      $query->whereRaw('LOWER(REPLACE(menu_nama, " ", "-")) = ?', [strtolower($page)]);
                                  })
                                  ->firstOrFail();

        $useCase = UseCase::where('menu_id', $selectedNavItem->menu_id)
                          ->where(function($query) use ($useCaseSlug) {
                              $query->whereRaw('LOWER(REPLACE(nama_proses, " ", "-")) = ?', [strtolower($useCaseSlug)])
                                    ->orWhere('usecase_id', $useCaseSlug);
                          })
                          ->firstOrFail();

        $databaseData = DatabaseData::where('use_case_id', $useCase->id)
                                    ->where('id_database', $databaseId)
                                    ->firstOrFail();

        $allMenus = NavMenu::where('category', $category)->orderBy('menu_order')->get();
        $navigation = NavMenu::buildTree($allMenus);
        $categories = NavMenu::select('category')->whereNotNull('category')->distinct()->pluck('category');

        return view('docs.database_detail_page', [ // Anda perlu membuat view ini
            'title'             => 'Detail Database: ' . ($databaseData->keterangan ?: 'N/A'),
            'navigation'        => $navigation,
            'currentCategory'   => $category,
            'currentPage'       => $page,
            'selectedNavItem'   => $selectedNavItem,
            'menu_id'           => $selectedNavItem->menu_id,
            'allParentMenus'    => NavMenu::where('category', $category)->orderBy('menu_nama')->get(['menu_id', 'menu_nama']),
            'categories'        => $categories,
            'databaseData'      => $databaseData, // Kirim objek DatabaseData
            'parentUseCase'     => $useCase,
        ]);
    }

    private function renderNoContentFallback($category): View
    {
        $fallbackPageName = 'tidak-ada-konten';

        $categories = NavMenu::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $content = '<h3>Selamat Datang di Dokumentasi!</h3><p>Belum ada menu atau konten yang dibuat untuk kategori ini. Silakan login sebagai **Admin** untuk menambahkan kategori, menu, dan detail aksi.</p><p>Gunakan tombol **+ Tambah Menu Utama Baru** di sidebar atau tombol **+ Tambah Kategori** di dropdown kategori untuk memulai.</p>';

        return view('docs.folder_page', [
            'title'             => 'Dokumentasi ' . Str::headline($category),
            'navigation'        => NavMenu::buildTree(NavMenu::where('category', $category)->orderBy('menu_order')->get()), // Pastikan navigasi dimuat
            'currentCategory'   => $category,
            'currentPage'       => $fallbackPageName,
            'selectedNavItem'   => null,
            'menu_id'           => 0,
            'allParentMenus'    => NavMenu::where('category', $category)->orderBy('menu_nama')->get(['menu_id', 'menu_nama']),
            'categories'        => $categories,
            'useCaseData'       => null,
            'contentTypes'      => [],
            'activeContentType' => 'UAT',
            'fallbackMessage'   => $content,
        ]);
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

        // 1. Cari di NavMenu (nama menu)
        $menuMatches = NavMenu::whereRaw('LOWER(TRIM(menu_nama)) LIKE ?', [$searchTerm])
            ->get();

        foreach ($menuMatches as $menu) {
            $key = 'menu-' . $menu->menu_id . '-' . $menu->category;
            $results[$key] = [
                'id' => $menu->menu_id,
                'name' => $menu->menu_nama,
                'category_name' => Str::headline($menu->category),
                'url' => route('docs', ['category' => $menu->category, 'page' => Str::slug($menu->menu_nama)]),
                'context' => 'Menu Navigasi', // Lebih spesifik
            ];
        }

        // 2. Cari di UseCase (nama proses, deskripsi aksi, aktor, dll.)
        $useCaseMatches = UseCase::with('menu')
            ->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(nama_proses) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(deskripsi_aksi) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(aktor) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(tujuan) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(kondisi_awal) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(kondisi_akhir) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(aksi_reaksi) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(reaksi_sistem) LIKE ?', [$searchTerm]);
            })
            ->get();

        foreach ($useCaseMatches as $useCase) {
            if ($useCase->menu) {
                // Link ke halaman detail use case
                $useCaseDetailUrl = route('docs.use_case_detail', [
                    'category' => $useCase->menu->category,
                    'page' => Str::slug($useCase->menu->nama_proses), // menu_slug dari navmenu
                    'useCaseSlug' => Str::slug($useCase->nama_proses) // slug dari nama proses usecase
                ]);

                $key = 'usecase-' . $useCase->id . '-' . $useCase->menu->category;
                $results[$key] = [
                    'id' => $useCase->id, // ID UseCase
                    'name' => $useCase->nama_proses . ' (Tindakan di ' . $useCase->menu->menu_nama . ')', // Tampilkan dari mana
                    'category_name' => Str::headline($useCase->menu->category),
                    'url' => $useCaseDetailUrl,
                    'context' => Str::limit(strip_tags($useCase->deskripsi_aksi ?: $useCase->nama_proses), 100),
                ];
            }
        }

        return response()->json(['results' => array_values($results)]);
    }
}