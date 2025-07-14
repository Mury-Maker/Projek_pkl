<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\CKEditorController;
use App\Http\Controllers\NavmenuController;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

// Mengatur rute root '/'
Route::get('/', function () {
    if (Auth::check()) {
        // If logged in, redirect to default documentation page
        return redirect()->route('docs.index');
    }
    // If not logged in, redirect to login page
    return redirect()->route('login');
})->name('home');

// Rute Login - hanya bisa diakses oleh tamu (belum login)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');

// Rute Logout - hanya bisa diakses oleh pengguna yang sudah login
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Grup rute yang memerlukan autentikasi untuk mengakses dokumentasi dan fungsionalitas terkait
Route::middleware('auth')->group(function () {
    // Rute Dokumentasi - sekarang hanya bisa diakses setelah login
    Route::get('/docs', [DocumentationController::class, 'index'])->name('docs.index');
    Route::get('/docs/{category}/{page?}', [DocumentationController::class, 'show'])->name('docs');

    // Rute API dan fungsionalitas terkait dokumentasi yang memerlukan autentikasi
    Route::get('/api/search', [DocumentationController::class, 'search'])->name('api.search');
    Route::post('/upload', [CKEditorController::class, 'upload'])->name('ckeditor.upload');
    Route::post('/docs/save/{menu_id}', [DocumentationController::class, 'saveContent'])->name('docs.save');
    Route::delete('/docs/delete/{menu_id}', [DocumentationController::class, 'deleteContent'])->name('docs.delete');

    // Rute Kategori (CRUD) - dipindahkan ke dalam grup middleware 'auth'
    Route::post('/kategori', [NavmenuController::class, 'storeCategory'])->name('kategori.store'); // Changed route path for store
    Route::put('/kategori/{categorySlug}', [NavmenuController::class, 'updateCategory'])->name('kategori.update');
    Route::delete('/kategori/{categorySlug}', [NavmenuController::class, 'destroyCategory'])->name('kategori.destroy');
});


// Rute API untuk Navigasi - sudah dalam grup middleware 'auth', jadi tidak perlu diubah
Route::middleware('auth')->prefix('api')->group(function () {
    Route::prefix('navigasi')->group(function () {
        Route::get('/all/{category}', [NavmenuController::class, 'getAllMenusForSidebar'])->name('api.navigasi.all');
        Route::get('/parents/{category}', [NavmenuController::class, 'getParentMenus'])->name('api.navigasi.parents');
        Route::get('/{navMenu}', [NavmenuController::class, 'getMenuData'])->name('api.navigasi.get');
        Route::post('/', [NavmenuController::class, 'store'])->name('api.navigasi.store');
        Route::put('/{navMenu}', [NavmenuController::class, 'update'])->name('api.navigasi.update');
        Route::delete('/{navMenu}', [NavmenuController::class, 'destroy'])->name('api.navigasi.destroy');
        Route::put('/{navMenu}/content', [NavmenuController::class, 'updateMenuContent'])->name('api.navigasi.content.update');
    });
});