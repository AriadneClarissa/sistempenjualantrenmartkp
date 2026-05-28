@extends('layouts.app')

@section('content')
@include('admin._header', ['activePage' => 'internal_users'])

<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold ms-0">Buat Akun Admin Baru</h4>
    </div>

    <div class="card p-4 shadow-sm w-100">
        <form action="{{ route('admin.admins.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap Admin</label>
                    <input type="text" name="name" class="form-control" placeholder="Nama admin" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Admin</label>
                    <input type="email" name="email" class="form-control" placeholder="Email admin" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Admin</option>
                        <option value="kasir">Kasir</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="default_password" class="form-control" placeholder="Masukkan password manual" required>
                    <small class="text-muted d-block mt-1">Minimal 8 karakter, wajib diisi manual.</small>
                </div>

                <div class="col-12 mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_email" id="send_email" value="1" checked>
                        <label class="form-check-label" for="send_email">
                            Kirim email berisi kredensial ke admin baru
                        </label>
                    </div>
                </div>

                <div class="col-12 text-end mt-4">
                    <a href="{{ route('admin.users.internal') }}" class="btn btn-outline-secondary me-2 px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">Buat Akun</button>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection