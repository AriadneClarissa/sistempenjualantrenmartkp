<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ProfileComplete
{
    /**
     * Handle an incoming request.
     * If user is a customer and missing phone/address, block checkout.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && method_exists($user, 'needsProfileCompletion') && $user->needsProfileCompletion()) {
            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json(['message' => 'Silakan lengkapi nomor WhatsApp dan alamat pengiriman di profil sebelum melanjutkan.'], 422);
            }

            return Redirect::route('profile.edit')->with('error', 'Silakan lengkapi nomor WhatsApp dan alamat pengiriman di profil Anda sebelum checkout.');
        }

        return $next($request);
    }
}
