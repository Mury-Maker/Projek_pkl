<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\NavMenu; // Tambahkan ini

class LoginController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // DIUBAH: Mengarahkan ke halaman dokumentasi setelah login berhasil
            $defaultCategory = 'epesantren';

            // Temukan menu pertama yang berstatus 1 (memiliki daftar use case) di kategori default
            $firstContentMenu = NavMenu::where('category', $defaultCategory)
                                       ->where('menu_status', 1)
                                       ->orderBy('menu_order', 'asc')
                                       ->first();

            if ($firstContentMenu) {
                // Redirect ke halaman daftar use case pertama
                return redirect()->route('docs', [
                    'category' => $defaultCategory,
                    'page' => \Illuminate\Support\Str::slug($firstContentMenu->menu_nama),
                ]);
            } else {
                // Jika tidak ada menu dengan status 1, coba cari menu parent pertama (folder)
                $firstMenu = NavMenu::where('category', $defaultCategory)
                                    ->where('menu_child', 0)
                                    ->orderBy('menu_order', 'asc')
                                    ->first();

                if ($firstMenu && trim($firstMenu->menu_nama) !== '') {
                     // Redirect ke menu pertama (folder) jika ada
                     return redirect()->route('docs', [
                        'category' => $defaultCategory,
                        'page' => \Illuminate\Support\Str::slug($firstMenu->menu_nama),
                    ]);
                }
            }

            // Jika tidak ada menu sama sekali, redirect ke docs.index (yang akan menangani fallback)
            return redirect()->route('docs.index');
        }

        throw ValidationException::withMessages([
            'email' => ('Email atau password yang Anda masukkan salah.'),
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}