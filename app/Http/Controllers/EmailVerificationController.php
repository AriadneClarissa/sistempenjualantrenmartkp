<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;

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

        return Redirect::route('login')->with('success', 'Email berhasil diverifikasi. Silakan masuk.');
    }

    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return redirect()->back()->with('error', 'Email tidak ditemukan.');
        }

        if (! is_null($user->email_verified_at)) {
            return redirect()->back()->with('success', 'Alamat email sudah terverifikasi.');
        }

        $verifyUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        try {
            \Illuminate\Support\Facades\Mail::raw("Silakan verifikasi email Anda: {$verifyUrl}", function ($mail) use ($user) {
                $mail->to($user->email)->subject('Verifikasi Email - Trenmart');
            });
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', 'Gagal mengirim ulang email verifikasi.');
        }

        return redirect()->back()->with('success', 'Tautan verifikasi telah dikirim ulang ke email Anda.');
    }
}
