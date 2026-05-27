@extends('layouts.app')

@section('content')
<style>
    /* Card Auth Style */
    .auth-card {
        max-width: 450px; 
        margin: 50px auto; 
        border: none;
        border-radius: 25px; 
        background-color: #fcf8f8; 
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .card-header-custom { 
        background-color: white;
        border-bottom: 1px solid #f1f1f1; 
        padding: 25px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
    }

    .form-label { font-weight: 600; color: #444; font-size: 0.9rem; }
    
    .form-control-custom { 
        border-radius: 12px; 
        border: 1.5px solid #eee; 
        padding: 12px 15px; 
        background-color: white;
        transition: 0.3s;
    }
    
    .form-control-custom:focus {
        border-color: #800000;
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.1);
        outline: none;
    }
    
    /* Tombol Login Utama */
    .btn-masuk-trenmart { 
        background-color: #800000 !important; 
        color: white !important; 
        border-radius: 12px; 
        padding: 14px; 
        width: 100%; 
        font-weight: bold; 
        border: none; 
        margin-top: 10px;
        display: block; 
        transition: all 0.2s ease;
        letter-spacing: 1px;
        cursor: pointer;
    }
    
    .btn-masuk-trenmart:active { 
        background-color: #b52b2b !important; 
        transform: scale(0.98); 
    }

    .password-wrapper { position: relative; }
    .no-native-password-reveal::-ms-reveal,
    .no-native-password-reveal::-ms-clear {
        display: none;
    }

    .no-native-password-reveal::-webkit-credentials-auto-fill-button,
    .no-native-password-reveal::-webkit-password-toggle-button {
        display: none !important;
        visibility: hidden;
    }

    .password-toggle { 
        position: absolute; 
        right: 15px; 
        top: 50%; 
        transform: translateY(-50%); 
        color: #800000; 
        cursor: pointer; 
        font-size: 1.2rem;
    }

    .register-link { text-align: center; font-size: 0.9rem; padding-bottom: 10px; }
    .auth-links { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: -6px; }
    .forgot-link { font-size: 0.88rem; color: #800000; text-decoration: none; font-weight: 600; }
    .forgot-link:hover { text-decoration: underline; }
</style>

<div class="container mt-4">
    {{-- Card Login --}}
    <div class="card auth-card">
        <div class="card-header-custom">
            <h5 class="mb-0 fw-bold" style="color: #800000;">Masuk Akun</h5>
            <a href="/" class="text-muted fs-4 text-decoration-none">&times;</a>
        </div>
        
        <div class="card-body p-4">
            @if (session('status'))
                <div class="alert alert-success border-0 small mb-4">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 small mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email atau Kode Pelanggan</label>
                    <input type="text" name="login" class="form-control form-control-custom" placeholder="Masukkan email atau kode pelanggan" value="{{ old('login') }}" required autofocus>
                </div>

                <div class="mb-4">
                    <label class="form-label">Kata Sandi</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="form-control form-control-custom no-native-password-reveal" placeholder="Masukkan kata sandi" required>
                        <i class="bi bi-eye password-toggle" onclick="togglePassword()"></i>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small text-muted" for="remember">Ingat Saya</label>
                </div>

                <div class="auth-links mb-3">
                    <a href="{{ Route::has('password.request') ? route('password.request') : url('/forgot-password') }}" class="forgot-link">Lupa password?</a>
                </div>

                <button type="submit" class="btn-masuk-trenmart shadow">MASUK</button>
            </form>

            <div class="register-link">
                <span class="text-muted">Belum punya akun?</span> 
                <a href="{{ route('register') }}" class="fw-bold text-decoration-none" style="color: #800000;">Daftar Sekarang</a>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passInput = document.getElementById('password');
        const icon = document.querySelector('.password-toggle');
        if (passInput.type === 'password') {
            passInput.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            passInput.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
</script>
@endsection