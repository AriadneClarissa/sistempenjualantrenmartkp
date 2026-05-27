<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    /**
     * Verify email from signed URL
     */
    public function verify(Request $request, $id, $hash)
    {
        // Validate signature
        if (! $request->hasValidSignature()) {
            return Redirect::route('login')->with('error', 'Tautan verifikasi tidak valid atau telah kedaluwarsa.');
        }

        $user = User::find($id);
        if (! $user) {
            return Redirect::route('login')->with('error', 'Pengguna tidak ditemukan.');
        }

        if (sha1($user->email) !== $hash) {
            return Redirect::route('login')->with('error', 'Hash verifikasi tidak cocok.');
        }

        $user->email_verified_at = Carbon::now();
        $user->save();

        return Redirect::route('login')->with('success', 'Email berhasil diverifikasi. Silakan masuk kembali menggunakan email dan password Anda.');
    }

    public function verifyFromProfile(Request $request, $id, $hash)
    {
        if (! $request->hasValidSignature()) {
            return Redirect::route('profile.edit')->with('error', 'Tautan verifikasi tidak valid atau telah kedaluwarsa.');
        }

        $user = User::find($id);

        if (! $user) {
            return Redirect::route('profile.edit')->with('error', 'Pengguna tidak ditemukan.');
        }

        if (sha1($user->email) !== $hash) {
            return Redirect::route('profile.edit')->with('error', 'Hash verifikasi tidak cocok.');
        }

        $user->email_verified_at = Carbon::now();
        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Email berhasil diverifikasi. Status email sekarang terverifikasi.');
    }

    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email:rfc,dns']);
        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return redirect()->back()->with('error', 'Email tidak ditemukan.');
        }

        if (! is_null($user->email_verified_at)) {
            return redirect()->back()->with('success', 'Alamat email sudah terverifikasi.');
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim ulang email verifikasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengirim ulang email verifikasi.');
        }

        return redirect()->back()->with('success', 'Tautan verifikasi telah dikirim ulang ke email Anda.');
    }

    public function resendFromProfile(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->isCustomer()) {
            return Redirect::route('profile.edit')->with('error', 'Akses tidak valid.');
        }

        if ($user->hasVerifiedEmail()) {
            return Redirect::route('profile.edit')->with('success', 'Alamat email sudah terverifikasi.');
        }

        try {
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify_profile',
                now()->addMinutes(60),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->email),
                ]
            );

            Mail::raw("Halo {$user->name},\n\nSilakan verifikasi email Anda dengan membuka tautan berikut:\n{$verificationUrl}\n\nJika Anda tidak meminta ini, abaikan email ini.\n\nSalam hangat, Trenmart", function ($message) use ($user) {
                $message->to($user->email)->subject('Verifikasi Email Trenmart');
            });

            return Redirect::route('profile.edit')->with('success', 'Email verifikasi telah dikirim. Silakan cek inbox/spam Anda.');
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim verifikasi email dari profil: ' . $e->getMessage(), ['exception' => $e]);

            return Redirect::route('profile.edit')->with('error', 'Gagal mengirim verifikasi email: ' . $e->getMessage());
        }
    }
}
