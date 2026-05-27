<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    private const RESET_CODE_TTL_MINUTES = 15;

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email:rfc,dns'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with('status', 'Jika email terdaftar, kode reset password telah dikirim. Silakan cek inbox/spam email Anda.');
        }

        $code = (string) random_int(100000, 999999);

        Cache::put(
            $this->cacheKey($user->email),
            ['code' => Hash::make($code)],
            now()->addMinutes(self::RESET_CODE_TTL_MINUTES)
        );

        $user->notify(new PasswordResetCodeNotification($code, self::RESET_CODE_TTL_MINUTES));

        return back()->with('status', 'Jika email terdaftar, kode reset password telah dikirim. Silakan cek inbox/spam email Anda.');
    }

    private function cacheKey(string $email): string
    {
        return 'password-reset-code:'.strtolower($email);
    }
}
