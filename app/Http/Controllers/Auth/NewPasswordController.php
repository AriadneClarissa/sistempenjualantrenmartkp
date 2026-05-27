<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    private const RESET_CODE_TTL_MINUTES = 15;

    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email:rfc,dns'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email tidak ditemukan. Pastikan email yang dimasukkan sudah terdaftar.']);
        }

        $cacheKey = $this->cacheKey($user->email);
        $payload = Cache::get($cacheKey);

        if (! $payload || ! Hash::check($request->code, $payload['code'] ?? '')) {
            return back()->withInput($request->only('email'))
                ->withErrors(['code' => 'Kode reset tidak valid atau sudah kedaluwarsa. Silakan minta kode baru.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => null,
        ])->save();

        Cache::forget($cacheKey);

        return redirect()->route('login')->with('status', 'Password berhasil diperbarui. Silakan login dengan password baru Anda.');
    }

    private function cacheKey(string $email): string
    {
        return 'password-reset-code:'.strtolower($email);
    }
}
