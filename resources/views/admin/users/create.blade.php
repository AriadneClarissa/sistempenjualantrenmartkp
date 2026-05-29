@extends('layouts.app')

@section('content')
@include('admin._header', ['activePage' => 'create_customer'])

<style>
    .password-field-wrap {
        position: relative;
    }

    .password-field-wrap .form-control {
        padding-right: 44px;
    }

    .password-eye-btn {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        border: 0;
        background: transparent;
        color: #6c757d;
        padding: 0;
        line-height: 1;
        z-index: 3;
    }

    .password-eye-btn:hover,
    .password-eye-btn:focus {
        color: #495057;
        outline: none;
        box-shadow: none;
    }

    .no-native-password-reveal::-ms-reveal,
    .no-native-password-reveal::-ms-clear,
    .no-native-password-reveal::-webkit-password-toggle-button,
    .no-native-password-reveal::-webkit-credentials-auto-fill-button {
        display: none;
        visibility: hidden;
        pointer-events: none;
    }
</style>

<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold ms-0">Buat Akun Pelanggan Langganan</h4>
    </div>

    <div class="card p-4 shadow-sm">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kode Pelanggan <span class="text-danger">*</span></label>
                    <input type="text" name="kd_pelanggan" class="form-control @error('kd_pelanggan') is-invalid @enderror" value="{{ old('kd_pelanggan') }}" placeholder="Contoh: PL0001" required>
                    @error('kd_pelanggan')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Jenis Pelanggan</label>
                    <input type="text" class="form-control bg-light text-muted" value="Langganan (Grosir)" style="pointer-events: none;" readonly>
                    <input type="hidden" name="customer_type" value="langganan">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <div class="password-field-wrap">
                        <input type="password" name="default_password" id="default-password" class="form-control no-native-password-reveal" placeholder="Masukkan password manual" required>
                        <button type="button" class="password-eye-btn" id="toggle-password" aria-label="Tampilkan atau sembunyikan password">
                            <i class="bi bi-eye" id="toggle-password-icon"></i>
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">Minimal 8 karakter, wajib diisi manual.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Organisasi <span class="text-danger">*</span></label>
                    <input type="text" name="organization_name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tipe Organisasi (opsional)</label>
                    <input type="text" name="organization_type" class="form-control">
                </div>

                <div class="col-12 text-end">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                    <button class="btn btn-primary">Buat Akun</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
function validateEmail(input) {
    const val = input.value;
    const errorElement = document.getElementById('email-error');
    
    // Regex standar untuk validasi format email
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (val.length > 0) {
        if (!emailPattern.test(val)) {
            // Jika format salah
            errorElement.style.display = 'block';
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
        } else {
            // Jika format sudah benar
            errorElement.style.display = 'none';
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    } else {
        // Jika kosong
        errorElement.style.display = 'none';
        input.classList.remove('is-invalid', 'is-valid');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('default-password');
    const togglePasswordButton = document.getElementById('toggle-password');
    const togglePasswordIcon = document.getElementById('toggle-password-icon');

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
