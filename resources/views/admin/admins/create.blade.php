@extends('layouts.app')

@section('content')
@include('admin._header', ['activePage' => 'internal_users'])

<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold ms-0" id="internal-user-title">Buat Akun User Internal Baru</h4>
    </div>

    <div class="card p-4 shadow-sm w-100">
        <form action="{{ route('admin.admins.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                
                <div class="col-md-6">
                    <label class="form-label" id="name-label">Nama Lengkap User Internal</label>
                    <input type="text" name="name" class="form-control" id="name-input" placeholder="Nama user internal" value="{{ old('name') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label" id="email-label">Email User Internal</label>
                    <input type="email" name="email" class="form-control" id="email-input" placeholder="Email user internal" value="{{ old('email') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role" id="role-select" class="form-select" required>
                        <option value="" {{ old('role') ? '' : 'selected' }}>-- Pilih Role --</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="kasir" {{ old('role') === 'kasir' ? 'selected' : '' }}>Kasir</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="default_password" id="default-password" class="form-control" placeholder="Masukkan password manual" required>
                        <button type="button" class="btn btn-outline-secondary" id="toggle-password" aria-label="Tampilkan atau sembunyikan password">
                            <i class="bi bi-eye" id="toggle-password-icon"></i>
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">Minimal 8 karakter, wajib diisi manual.</small>
                </div>

                <div class="col-12 mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_email" id="send_email" value="1" checked>
                        <label class="form-check-label" for="send_email" id="send-email-label">
                            Kirim email berisi kredensial ke user internal baru
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('role-select');
        const titleEl = document.getElementById('internal-user-title');
        const nameLabel = document.getElementById('name-label');
        const emailLabel = document.getElementById('email-label');
        const nameInput = document.getElementById('name-input');
        const emailInput = document.getElementById('email-input');
        const sendEmailLabel = document.getElementById('send-email-label');
        const passwordInput = document.getElementById('default-password');
        const togglePasswordButton = document.getElementById('toggle-password');
        const togglePasswordIcon = document.getElementById('toggle-password-icon');

        const applyRoleCopy = (role) => {
            if (role === 'admin') {
                titleEl.textContent = 'Buat Akun Admin Baru';
                nameLabel.textContent = 'Nama Lengkap Admin';
                emailLabel.textContent = 'Email Admin';
                nameInput.placeholder = 'Nama admin';
                emailInput.placeholder = 'Email admin';
                sendEmailLabel.textContent = 'Kirim email berisi kredensial ke admin baru';
                return;
            }

            if (role === 'kasir') {
                titleEl.textContent = 'Buat Akun Kasir Baru';
                nameLabel.textContent = 'Nama Lengkap Kasir';
                emailLabel.textContent = 'Email Kasir';
                nameInput.placeholder = 'Nama kasir';
                emailInput.placeholder = 'Email kasir';
                sendEmailLabel.textContent = 'Kirim email berisi kredensial ke kasir baru';
                return;
            }

            titleEl.textContent = 'Buat Akun User Internal Baru';
            nameLabel.textContent = 'Nama Lengkap User Internal';
            emailLabel.textContent = 'Email User Internal';
            nameInput.placeholder = 'Nama user internal';
            emailInput.placeholder = 'Email user internal';
            sendEmailLabel.textContent = 'Kirim email berisi kredensial ke user internal baru';
        };

        applyRoleCopy(roleSelect.value);
        roleSelect.addEventListener('change', function () {
            applyRoleCopy(this.value);
        });

        if (togglePasswordButton && passwordInput && togglePasswordIcon) {
            togglePasswordButton.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                togglePasswordIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }
    });
</script>
@endsection