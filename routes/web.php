<?php
// File: routes/web.php (Revisi untuk Detail Page UAT/Report/Database)

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\CKEditorController;
use App\Http\Controllers\NavmenuController;
use App\Http\Controllers\UseCaseController;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

// Mengatur rute root '/'
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('docs.index');
    }
    return redirect()->route('login');
})->name('home');

// Rute Login/Logout
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Grup rute yang memerlukan autentikasi
Route::middleware('auth')->group(function () {
    // Rute Dokumentasi Utama (akan mengarahkan ke daftar use case atau halaman folder)
    Route::get('/docs', [DocumentationController::class, 'index'])->name('docs.index');
    Route::get('/docs/{category}/{page?}', [DocumentationController::class, 'show'])->name('docs'); // Ini sekarang untuk daftar UseCase (indeks tindakan)

    // Rute Detail Use Case (menampilkan satu tindakan secara rinci)
    Route::get('/docs/{category}/{page}/{useCaseSlug}', [DocumentationController::class, 'show'])
         ->name('docs.use_case_detail');

    // 👇 Rute Baru untuk Detail Halaman UAT Data 👇
    Route::get('/docs/{category}/{page}/{useCaseSlug}/uat/{uatId}', [DocumentationController::class, 'showUatDetailPage'])
         ->name('docs.use_case_uat_detail_page');

    // 👇 Rute Baru untuk Detail Halaman Report Data 👇
    Route::get('/docs/{category}/{page}/{useCaseSlug}/report/{reportId}', [DocumentationController::class, 'showReportDetailPage'])
         ->name('docs.use_case_report_detail_page');

    // 👇 Rute Baru untuk Detail Halaman Database Data 👇
    Route::get('/docs/{category}/{page}/{useCaseSlug}/database/{databaseId}', [DocumentationController::class, 'showDatabaseDetailPage'])
         ->name('docs.use_case_database_detail_page');

    // Rute API dan fungsionalitas umum
    Route::get('/api/search', [DocumentationController::class, 'search'])->name('api.search');
    Route::post('/upload', [CKEditorController::class, 'upload'])->name('ckeditor.upload');

    // Rute Kategori (CRUD)
    Route::post('/kategori', [NavmenuController::class, 'storeCategory'])->name('kategori.store');
    Route::put('/kategori/{categorySlug}', [NavmenuController::class, 'updateCategory'])->name('kategori.update');
    Route::delete('/kategori/{categorySlug}', [NavmenuController::class, 'destroyCategory'])->name('kategori.destroy');

    // --- Rute API untuk UseCase dan Data Terkait ---
    // UseCaseController sekarang mengelola individual UseCase entries
    Route::prefix('api/usecase')->group(function () {
        Route::post('/', [UseCaseController::class, 'store'])->name('usecase.store');
        Route::put('/{useCase}', [UseCaseController::class, 'update'])->name('usecase.update');
        Route::delete('/{useCase}', [UseCaseController::class, 'destroy'])->name('usecase.destroy');

        // Rute untuk UAT Data
        Route::post('/uat', [UseCaseController::class, 'storeUatData'])->name('usecase.uat.store');
        Route::post('/uat/{uatData:id_uat}', [UseCaseController::class, 'updateUatData'])->name('usecase.uat.update');
        Route::delete('/uat/{uatData:id_uat}', [UseCaseController::class, 'destroyUatData'])->name('usecase.uat.destroy');

        // Rute untuk Report Data
        Route::post('/report', [UseCaseController::class, 'storeReportData'])->name('usecase.report.store');
        Route::post('/report/{reportData:id_report}', [UseCaseController::class, 'updateReportData'])->name('usecase.report.update');
        Route::delete('/report/{reportData:id_report}', [UseCaseController::class, 'destroyReportData'])->name('usecase.report.destroy');

        // Rute untuk Database Data
        Route::post('/database', [UseCaseController::class, 'storeDatabaseData'])->name('usecase.database.store');
        Route::post('/database/{databaseData:id_database}', [UseCaseController::class, 'updateDatabaseData'])->name('usecase.database.update');
        Route::delete('/database/{databaseData:id_database}', [UseCaseController::class, 'destroyDatabaseData'])->name('usecase.database.destroy');
    });
    // --- Akhir Rute API UseCase ---

    // Rute API untuk Navigasi
    Route::prefix('api/navigasi')->group(function () {
        Route::get('/all/{category}', [NavmenuController::class, 'getAllMenusForSidebar'])->name('api.navigasi.all');
        Route::get('/parents/{category}', [NavmenuController::class, 'getParentMenus'])->name('api.navigasi.parents');
        Route::get('/{navMenu}', [NavmenuController::class, 'getMenuData'])->name('api.navigasi.get');
        Route::post('/', [NavmenuController::class, 'store'])->name('api.navigasi.store');
        Route::put('/{navMenu}', [NavmenuController::class, 'update'])->name('api.navigasi.update');
        Route::delete('/{navMenu}', [NavmenuController::class, 'destroy'])->name('api.navigasi.destroy');
    });
});