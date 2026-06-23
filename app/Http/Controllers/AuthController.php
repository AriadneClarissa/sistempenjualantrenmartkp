<?php

namespace App\Http\Controllers;

use App\Helpers\MediaStorage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * 1. PENDAFTARAN MANUAL (One-Stop Registration)
     * Langsung meminta data lengkap agar tidak muncul layar 'Pilih Jenis' lagi.
     */
    public function register(Request $request)
{
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email:rfc,dns|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        // Tambahkan regex: wajib angka dan diawali 08
        'phone_number' => ['required', 'string', 'min:10', 'max:13', 'regex:/^08[0-9]{8,11}$/'],
        'home_address' => 'required|string|max:500',
    ];

    if ($request->customer_type === 'langganan') {
        $rules['organization_name'] = 'required|string|max:255';
        $rules['organization_type'] = 'required|string|max:255';
    }

    $validated = $request->validate($rules, [
        'email.unique' => 'Email sudah terdaftar. Silakan gunakan email lain.',
        'phone_number.regex' => 'Nomor WhatsApp harus diawali dengan 08.',
    ]);

        $customerType = $request->customer_type === 'langganan' ? 'langganan' : 'regular';
        $customerCode = $customerType === 'regular'
            ? User::generateCustomerCode()
            : null;

        $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => 'customer',
        'customer_type' => $customerType,
        'kd_pelanggan' => $customerCode,
        'phone_number' => $validated['phone_number'],
        'home_address' => $validated['home_address'],
        'is_active' => true,
    ]);
        // Kirim verifikasi hanya untuk pelanggan reguler.
        if ($user->role === 'customer' && $user->customer_type !== 'langganan') {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim email verifikasi: ' . $e->getMessage());
            }
        }

        $successMessage = $user->customer_type === 'langganan'
            ? 'Pendaftaran berhasil! Silakan login menggunakan kode pelanggan dan password Anda.'
            : 'Pendaftaran berhasil! Kode pelanggan Anda: ' . ($user->kd_pelanggan ?? '-') . '. Silakan cek email Anda untuk tautan verifikasi.';

        return redirect()->route('login')->with('success', $successMessage);
    }

    /**
     * 2. LOGIN MANUAL
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => ['required', 'string', 'max:255'],
            'password' => ['required'],
        ]);

        $loginInput = $credentials['login'];
        $isEmailLogin = filter_var($loginInput, FILTER_VALIDATE_EMAIL) !== false;

        $user = User::where('email', $loginInput)
            ->orWhere('kd_pelanggan', $loginInput)
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if (!$user->isActive()) {
                return back()->withErrors(['login' => 'Akun Anda telah dinonaktifkan oleh pemilik sistem.'])->onlyInput('login');
            }

            // Pelanggan langganan boleh langsung login dengan kode pelanggan + password.
            // Pelanggan reguler tetap wajib verifikasi email.
            if ($user->isCustomer() && $user->customer_type !== 'langganan' && ! $user->hasVerifiedEmail()) {
                $isInternal = method_exists($user, 'isInternalStaff') ? $user->isInternalStaff() : false;

                if (! $isInternal) {
                    try {
                        $this->sendVerificationEmailIfAllowed($user);
                    } catch (\Throwable $e) {
                        return back()->withErrors([
                            'login' => 'Email verifikasi gagal dikirim: ' . $e->getMessage(),
                        ])->onlyInput('login');
                    }

                    return back()->withErrors(['login' => 'Akun Anda belum terverifikasi. Email konfirmasi telah dikirim ke alamat yang Anda daftarkan.'])->onlyInput('login');
                }
            }

            Auth::login($user, $request->boolean('remember'));

            if ($user->status === 'rejected') {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Akun Anda ditolak oleh admin.');
            }

            if ($user->isCashier()) {
                return $this->redirectToLoading($request, route('admin.orders.index'));
            }

            $targetUrl = $request->session()->pull('url.intended', route('beranda'));

            return $this->redirectToLoading($request, $targetUrl);
        }

        return back()->withErrors(['login' => 'Email atau kode pelanggan tidak sesuai.'])->onlyInput('login');
    }

    private function sendVerificationEmailIfAllowed(User $user): void
    {
        $cacheKey = 'verification-email-sent:'.strtolower($user->email);

        if (Cache::has($cacheKey)) {
            return;
        }

        $user->sendEmailVerificationNotification();
        Cache::put($cacheKey, true, now()->addMinutes(5));
    }

    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email:rfc,dns'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with('error', 'Email tidak ditemukan.');
        }

        if ($user->hasVerifiedEmail()) {
            return back()->with('success', 'Alamat email sudah terverifikasi.');
        }

        try {
            $user->sendEmailVerificationNotification();
            Cache::put('verification-email-sent:'.strtolower($user->email), true, now()->addMinutes(1));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim ulang email verifikasi: ' . $e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Gagal mengirim ulang email verifikasi: ' . $e->getMessage());
        }

        return back()->with('success', 'Email verifikasi telah dikirim ulang ke alamat yang terdaftar.');
    }

    public function loadingRedirect(Request $request)
    {
        $targetUrl = $request->session()->pull('post_login_redirect', route('beranda'));

        return view('auth.loading', [
            'targetUrl' => $this->sanitizeRedirectUrl($targetUrl),
        ]);
    }

    private function redirectToLoading(Request $request, string $targetUrl)
    {
        $request->session()->put('post_login_redirect', $this->sanitizeRedirectUrl($targetUrl));

        return redirect()->route('auth.loading');
    }

    private function sanitizeRedirectUrl(string $targetUrl): string
    {
        $appHost = parse_url(url('/'), PHP_URL_HOST);
        $targetHost = parse_url($targetUrl, PHP_URL_HOST);

        if ($targetHost !== null && $targetHost !== $appHost) {
            return route('beranda');
        }

        return $targetUrl;
    }

    public function handlePilihJenis(Request $request)
    {
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Jika data profil belum lengkap
        if (empty($user->phone_number)) {
            // Langsung arahkan ke form umum
            return redirect()->route('form.umum');
        }

        // Default: Ke Beranda (User yang profilnya sudah lengkap)
        return redirect()->route('beranda');
    }
    /**
     * 4. CENTRALIZED REDIRECT LOGIC
     * Fungsi kunci untuk menghilangkan layar ganda.
     */
    private function handleRedirectAfterLogin($user)
    {
        // Jika Pemilik toko
        if ($user->isOwner()) {
            return redirect()->route('beranda');
        }

        if (method_exists($user, 'isCashier') && $user->isCashier()) {
            return redirect()->route('admin.orders.index');
        }

        // Jika Admin biasa
        if ($user->isAdmin()) {
            return redirect()->route('beranda');
        }

        // Jika data profil belum lengkap
        if (empty($user->phone_number)) {
            // Langsung arahkan ke form umum
            return redirect()->route('form.umum');
        }
        // Default: Ke Beranda (Regular atau Langganan yang sudah aktif)
        return redirect()->route('beranda');
    }

    /**
     * 5. UPDATE PROFIL AWAL (Lengkapi Data)
     */
    public function updateInitialProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rules = [
            'phone_number' => 'required|string|min:10|max:15',
            'home_address' => 'required|string',
        ];

        $validated = $request->validate($rules);
        
        // Update data profil dasar dari modal lengkapi profil.
        $user->update([
            'name' => $user->name,
            'phone_number' => $validated['phone_number'],
            'home_address' => $validated['home_address'],
            'is_approved' => true, 
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil dilengkapi.',
            ]);
        }

        return redirect()->route('beranda')->with('success', 'Profil berhasil dilengkapi.');
    }

    /**
     * 6. PROFIL UMUM (Untuk halaman edit profil mandiri)
     */
    public function profile()
    {
        return view('auth.profil', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
        ];

        if ($user->isCustomer()) {
            $rules['phone_number'] = 'required|string|min:10|max:15';
            $rules['home_address'] = 'required|string|max:500';
        }

        if ($user->customer_type === 'langganan' && $user->isCustomer()) {
            $rules['email'] = 'required|email|max:255|unique:users,email,' . $user->id;
            $rules['organization_name'] = 'required|string|max:255';
            $rules['organization_type'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        $emailChanged = array_key_exists('email', $validated) && $validated['email'] !== $user->email;

        $user->name = $validated['name'];

        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }

        if (array_key_exists('phone_number', $validated)) {
            $user->phone_number = $validated['phone_number'];
        }

        if (array_key_exists('home_address', $validated)) {
            $user->home_address = $validated['home_address'];
        }

        if (array_key_exists('organization_name', $validated)) {
            $user->organization_name = $validated['organization_name'];
        }

        if (array_key_exists('organization_type', $validated)) {
            $user->organization_type = $validated['organization_type'];
        }

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();
        $user->refresh();
        Auth::setUser($user);

        if ($emailChanged && $user->isCustomer()) {
            return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui. Silakan klik tombol Kirim Verifikasi Email untuk mengirim tautan verifikasi ke alamat baru Anda.');
        }

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update user's password from profile
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak cocok.']);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }

    /**
     * 7. ADMIN FEATURES
     */
    public function adminDashboard(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Sekarang $user->isAdmin() tidak akan merah lagi
        if (!$user || !$user->isAdmin()) { 
            abort(403); 
        }

        // Fetch sales data for the last 30 days
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        // Only count revenue from orders that are accepted/processing/ready_to_ship/completed
        $validStatusesForRevenue = ['processing', 'ready_to_ship', 'completed'];
        $salesData = Order::where('created_at', '>=', $thirtyDaysAgo)
            ->whereIn('order_status', $validStatusesForRevenue)
            ->where('payment_status', 'confirmed')
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as order_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Convert to format for chart
        $chartLabels = [];
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = Carbon::now()->subDays($i)->format('d/m');
            $revenue = $salesData->firstWhere('date', $date)?->revenue ?? 0;
            $chartData[] = floatval($revenue);
        }

        // Calculate metrics — revenue only from accepted/processing/ready_to_ship/completed orders
        $totalRevenue = Order::where('created_at', '>=', $thirtyDaysAgo)
            ->whereIn('order_status', $validStatusesForRevenue)
            ->where('payment_status', 'confirmed')
            ->sum('total');
        $totalOrders = Order::where('created_at', '>=', $thirtyDaysAgo)->count();
        $revenueOrdersCount = Order::where('created_at', '>=', $thirtyDaysAgo)
            ->whereIn('order_status', $validStatusesForRevenue)
            ->where('payment_status', 'confirmed')
            ->count();

        // Orders that contribute to revenue (for debug / inspection)
        $revenueOrders = Order::where('created_at', '>=', $thirtyDaysAgo)
            ->whereIn('order_status', $validStatusesForRevenue)
            ->where('payment_status', 'confirmed')
            ->orderBy('created_at', 'desc')
            ->get();
        $averageOrderValue = $revenueOrdersCount > 0 ? round($totalRevenue / $revenueOrdersCount, 2) : 0;

        // Order status breakdown
        $statusBreakdown = Order::selectRaw('order_status, COUNT(*) as count')
            ->groupBy('order_status')
            ->pluck('count', 'order_status');

        // Friendly labels for chart/legend
        $statusLabels = $statusBreakdown->keys()->map(function ($s) {
            $k = strtolower($s);
            return match ($k) {
                'pending' => 'Menunggu',
                'processing' => 'Diproses',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                'payment_rejected' => 'Ditolak',
                'new' => 'Baru',
                default => ucfirst(str_replace('_', ' ', $k)),
            };
        })->toArray();

        $pendingUsers = User::where('customer_type', 'langganan')
                            ->where('is_approved', false)
                            ->get();
        $allUsers = User::all();
        $methods = PaymentMethod::orderBy('created_at', 'desc')->get();

        return view('admin.dashboard', compact(
            'pendingUsers', 
            'allUsers', 
            'methods',
            'chartLabels',
            'chartData',
            'totalRevenue',
            'totalOrders',
            'averageOrderValue',
            'statusBreakdown'
        ));
    }

    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_approved' => true]);

        $this->sendReviewStatusEmail($user, 'accepted');

        return back()->with('success', 'User telah disetujui.');
    }

    public function reject($id)
    {
        $user = \App\Models\User::findOrFail($id);

        $this->sendReviewStatusEmail($user, 'rejected');
        
        // Hapus permanen akun yang ditolak
        $user->delete(); 

        return redirect()->back()->with('success', 'Pendaftaran pelanggan telah ditolak.');
    }
    
    public function showUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user_detail', compact('user'));
    }

    /**
     * 8. OTHER HELPERS
     */
    public function statusTinjau()
    {
        /** @var \App\Models\User $user */ 
        $user = Auth::user();

        if (!$user || !$user->isPendingMember()) {
            return redirect()->route('beranda');
        }
        return view('auth.status_tinjau', compact('user'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function sendReviewStatusEmail(User $user, string $status): void
    {
        $isAccepted = $status === 'accepted';

        $subject = $isAccepted
            ? 'Admin Trenmart: Akun Anda Diterima'
            : 'Admin Trenmart: Akun Anda Ditolak';

        $loginUrl = url('/login');

        $message = $isAccepted
            ? "Halo {$user->name},\n\nPendaftaran akun pelanggan langganan Anda telah kami tinjau dan dinyatakan DITERIMA.\nAkun Anda sekarang sudah aktif dan dapat digunakan untuk login ke sistem Trenmart.\n\nSilakan masuk melalui tautan berikut:\n{$loginUrl}\n\nTerima kasih telah bergabung bersama Trenmart.\n\nSalam,\nAdmin Trenmart"
            : "Halo {$user->name},\n\nPendaftaran akun pelanggan langganan Anda telah kami tinjau dan untuk saat ini dinyatakan DITOLAK.\nJika Anda ingin mengajukan ulang, silakan cek informasi akun atau hubungi CS Trenmart.\n\nAnda dapat mengunjungi halaman login di:\n{$loginUrl}\n\nSalam,\nAdmin Trenmart";

        try {
            Mail::raw($message, function ($mail) use ($user, $subject) {
                $mail->to($user->email)
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function updateBanner(Request $request)
    {
        $request->validate([
            'tentang_banner' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('tentang_banner')) {
            MediaStorage::delete($user->tentang_banner);
            $upload = app(\Cloudinary\Cloudinary::class)->uploadApi()->upload($request->file('tentang_banner')->getRealPath(), [
                'upload_preset' => 'produk',
                'folder' => 'banners',
            ]);
            $path = (string) ($upload->offsetGet('secure_url') ?? $upload['secure_url'] ?? null);
            $user->tentang_banner = $path;
            $user->save();

            return back()->with('success', 'Banner berhasil diperbarui!');
        }

        return back()->with('error', 'Gagal upload.');
    }

    // Alur penunjang view pilih jenis
    public function showPilihJenis() { return view('auth.pilih_jenis'); }
    public function formUmum() { return view('auth.form_umum', ['user' => Auth::user()]); }
    public function formLangganan() { return view('auth.form_langganan', ['user' => Auth::user()]); }
}