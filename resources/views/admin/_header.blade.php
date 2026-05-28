<style>
    :root { --maroon: #660000; }
    .admin-header { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; }
    .admin-header h2 { color: var(--maroon); font-weight: bold; }
    
    .admin-nav-btn { 
        border-radius: 50px; 
        padding: 0.4rem 0.72rem; 
        font-weight: 500;
        font-size: 0.86rem;
        transition: all 0.2s ease;
        white-space: nowrap;
        min-height: 36px;
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    
    .admin-header-actions {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.4rem;
        align-items: center;
        justify-content: flex-end; /* Memaksa ke kanan */
        width: auto;
        max-width: 100%;
        overflow-x: auto;
        padding-bottom: 2px;
        margin-left: auto !important; 
        -webkit-overflow-scrolling: touch;
        
        scrollbar-width: none !important; 
        -ms-overflow-style: none !important; 
    }

    .admin-header-actions::-webkit-scrollbar,
    .custom-scrollbar::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        background: transparent !important;
        -webkit-appearance: none !important;
    }
    
    .admin-header > .container-fluid > .d-flex {
        display: flex !important;
        flex-wrap: nowrap;
        gap: 1rem;
        align-items: center;
        justify-content: space-between !important;
        width: 100% !important; 
    }
    .admin-header > .container-fluid > .d-flex > h2 {
        flex: 0 0 auto;
        margin-bottom: 0;
        white-space: nowrap;
    }
    .admin-header > .container-fluid > .d-flex > .admin-header-actions {
        flex: 1 1 auto;
    }
    
    @media (max-width: 992px) {
        .admin-header > .container-fluid > .d-flex {
            flex-wrap: wrap !important;
        }
        .admin-header > .container-fluid > .d-flex > h2 {
            flex: 1 1 100%;
            margin-bottom: 0.5rem !important;
        }
        .admin-header-actions {
            margin-left: 0 !important;
            width: 100%;
            justify-content: flex-start;
        }
        .admin-nav-btn {
            width: 100%;
        }
        .admin-header-actions .admin-nav-btn {
            flex: 0 0 auto;
            width: auto;
            min-width: 120px;
        }
    }
    
    /* GAYA TOMBOL AKTIF */
    .admin-nav-btn.active {
        background-color: var(--maroon) !important;
        color: white !important;
        border-color: var(--maroon) !important;
        border-width: 2px;
    }
    .admin-nav-btn.active:hover {
        background-color: #550000 !important;
        border-color: #550000 !important;
    }
    
    /* GAYA TOMBOL TIDAK AKTIF (TRANSPARAN) */
    .admin-nav-btn:not(.active) {
        border: 2px solid #dee2e6;
        color: #495057;
        background-color: transparent !important;
    }
    .admin-nav-btn:not(.active):hover {
        border-color: #adb5bd;
        background-color: transparent !important;
    }
</style>

<div class="admin-header py-3 mb-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                @if(auth()->user()->isOwner())
                    Panel Pemilik
                @elseif(method_exists(auth()->user(), 'isCashier') && auth()->user()->isCashier())
                    Panel Kasir
                @else
                    Panel Admin
                @endif
                - Trenmart
            </h2>
            <div class="admin-header-actions custom-scrollbar">
                @if(method_exists(auth()->user(), 'isCashier') && auth()->user()->isCashier())
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('admin.orders.*') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-receipt me-1"></i> Pesanan
                    </a>
                    <a href="{{ route('produk.index') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('produk.*') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-box-seam me-1"></i> Produk
                    </a>
                
                @elseif(auth()->user()->isOwner())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.users.index') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-people me-1"></i> Semua Pengguna
                    </a>
                    <a href="{{ route('admin.users.internal') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('admin.users.internal') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-shield-lock me-1"></i> User Internal
                    </a>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('admin.customers.*') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-person-badge me-1"></i> Pelanggan
                    </a>
                    <a href="{{ route('admin.payment_methods.index') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('admin.payment_methods.*') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-credit-card-2-back me-1"></i> Metode Pembayaran
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('admin.users.create') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-person-plus me-1"></i> Buat Pelanggan
                    </a>
                    <a href="{{ route('admin.admins.create') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('admin.admins.create') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-shield-check me-1"></i> Buat User Internal
                    </a>
                
                @elseif(auth()->user()->isAdmin())
                    <a href="{{ route('admin.users.internal') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('admin.users.internal') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-shield-lock me-1"></i> User Internal
                    </a>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('admin.customers.*') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-person-badge me-1"></i> Pelanggan
                    </a>
                    <a href="{{ route('admin.payment_methods.index') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('admin.payment_methods.*') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-credit-card-2-back me-1"></i> Metode Pembayaran
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('admin.users.create') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-person-plus me-1"></i> Buat Pelanggan
                    </a>
                    <a href="{{ route('admin.admins.create') }}" class="btn btn-sm admin-nav-btn {{ request()->routeIs('admin.admins.create') ? 'active' : 'btn-outline-secondary' }}">
                        <i class="bi bi-shield-check me-1"></i> Buat User Internal
                    </a>
                @endif
                
                <a href="{{ route('beranda') }}" class="btn btn-sm btn-outline-secondary admin-nav-btn">
                    <i class="bi bi-house-door-fill me-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>