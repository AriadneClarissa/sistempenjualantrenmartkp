<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        
        return view('admin.users.index', [
            'users' => $users,
            'page' => 'all',
        ]);
    }

    public function internalUsers()
    {
        $users = User::whereIn('role', ['owner', 'admin', 'kasir'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.users.index', [
            'users' => $users,
            'page' => 'internal',
        ]);
    }

    public function toggleInternalUserActiveState(\Illuminate\Http\Request $request, int $id)
    {
        abort_unless(Auth::check() && Auth::user()->isOwner(), 403);

        $user = User::whereIn('role', ['owner', 'admin', 'kasir'])->findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Akun sendiri tidak bisa dinonaktifkan dari halaman ini.');
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        try {
            ActivityLog::create([
                'actor_id' => Auth::id(),
                'action' => $user->is_active ? 'activate_internal_user' : 'deactivate_internal_user',
                'details' => ($user->is_active ? 'Mengaktifkan' : 'Menonaktifkan') . ' user internal ' . $user->email,
                'ip_address' => $request->ip(),
                'subject_type' => 'user',
                'subject_id' => $user->id,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Status user berhasil diperbarui.');
    }

    public function customers()
    {
        $customers = User::where('role', 'customer')->orderBy('created_at', 'desc')->get();

        return view('admin.customers.index', [
            'customers' => $customers,
            'page' => 'customers',
        ]);
    }

    public function create()
    {
        // Tambahkan 'page' => 'customers' agar layout-nya membentang penuh
        return view('admin.users.create', [
            'page' => 'customers' 
        ]);
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $customerType = $request->input('customer_type', 'langganan');

        $rules = [
            'name' => 'required|string|max:255',
            'customer_type' => 'required|in:regular,langganan',
            'default_password' => 'required|string|min:8',
            'organization_name' => 'nullable|string|max:255',
            'organization_type' => 'nullable|string|max:255',
        ];

        if ($customerType === 'langganan') {
            $rules['kd_pelanggan'] = 'required|string|max:50|unique:users,kd_pelanggan';
        }

        $data = $request->validate($rules);

        $kdPelanggan = $customerType === 'regular'
            ? User::generateCustomerCode()
            : $data['kd_pelanggan'];

        $generatedEmail = strtolower(trim($kdPelanggan)) . '@trenmart.local';

        User::create([
            'name' => $data['name'],
            'email' => $generatedEmail,
            'kd_pelanggan' => $kdPelanggan,
            'password' => \Illuminate\Support\Facades\Hash::make($data['default_password']),
            'role' => 'customer',
            'customer_type' => $customerType,
            'organization_name' => $data['organization_name'] ?? null,
            'organization_type' => $data['organization_type'] ?? null,
            'is_approved' => true,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Akun pelanggan berhasil dibuat.');
    }

    public function createAdmin()
    {
        // Tambahkan 'page' => 'internal' agar layout-nya membentang penuh
        return view('admin.admins.create', [
            'page' => 'internal'
        ]);
    }

    public function storeAdmin(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,kasir',
            'default_password' => 'required|string|min:8',
            'send_email' => 'nullable|boolean',
        ]);

        $admin = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['default_password']),
            'role' => $data['role'],
            'is_approved' => true,
        ]);

        // Akun internal (admin/kasir) tidak perlu verifikasi email manual.
        $admin->email_verified_at = now();
        $admin->save();

        try {
            ActivityLog::create([
                'actor_id' => Auth::id(),
                'action' => 'create_internal_user',
                'details' => 'Created user ' . $admin->email . ' with role ' . $data['role'],
                'ip_address' => $request->ip(),
                'subject_type' => 'user',
                'subject_id' => $admin->id,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->boolean('send_email')) {
            try {
                $roleLabel = $data['role'] === 'kasir' ? 'Kasir' : 'Admin';
                \Illuminate\Support\Facades\Mail::raw("Halo {$admin->name},\n\nAkun {$roleLabel} Anda telah dibuat.\nEmail: {$admin->email}\nPassword: {$data['default_password']}\n\nSilakan masuk di: " . url('/login') . " dan segera ganti kata sandi melalui halaman profil.", function ($m) use ($admin, $roleLabel) {
                    $m->to($admin->email)->subject('Akun ' . $roleLabel . ' Trenmart');
                });
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'Akun ' . $data['role'] . ' berhasil dibuat.');
    }
}