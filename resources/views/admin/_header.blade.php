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
        justify-content: flex-start;
        width: auto;
        max-width: 100%;
        overflow-x: auto;
        padding-bottom: 6px; /* space for thin scrollbar */
        -webkit-overflow-scrolling: touch;

        /* show a thin scrollbar in Firefox */
        scrollbar-width: thin;
        scrollbar-color: rgba(0,0,0,0.15) transparent;
    }

    /* Thin horizontal scrollbar for WebKit browsers */
    .admin-header-actions::-webkit-scrollbar,
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
    }
    .admin-header-actions::-webkit-scrollbar-track,
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .admin-header-actions::-webkit-scrollbar-thumb,
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.12);
        border-radius: 6px;
    }
    .admin-header-actions::-webkit-scrollbar-thumb:hover,
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(0,0,0,0.2);
    }
    
    .admin-header > .container-fluid > .d-flex {
        flex-wrap: nowrap;
        gap: 1rem;
    }
    .admin-header > .container-fluid > .d-flex > h2 {
        flex: 0 0 auto;
        min-width: 0;
        white-space: nowrap;
    }
    .admin-header > .container-fluid > .d-flex > .admin-header-actions {
        flex: 1 1 auto;
        min-width: 0;
    }
    
    @media (max-width: 768px) {
        .admin-header > .container-fluid > .d-flex {
            flex-wrap: wrap;
        }
        .admin-header > .container-fluid > .d-flex > h2 {
            flex: 1 1 100%;
        }
        .admin-header > .container-fluid > .d-flex > .admin-header-actions {
            flex: 1 1 100%;
            width: 100%;
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
    
    /* Keep horizontal scroll for medium/large screens; only stack on smaller devices */
    @media (max-width: 992px) {
        .admin-header > .container-fluid > .d-flex {
            flex-wrap: wrap;
        }
        .admin-header > .container-fluid > .d-flex > h2 {
            flex: 1 1 100%;
        }
        .admin-header > .container-fluid > .d-flex > .admin-header-actions {
            flex: 1 1 100%;
            width: 100%;
        }
    }
    
    @media (max-width: 576px) {
        .admin-nav-btn {
            width: 100%;
        }
        .admin-header-actions .admin-nav-btn {
            flex: 0 0 auto;
            width: auto;
            min-width: 120px;
        }
    }
    
    .admin-nav-btn.active {
        background-color: var(--maroon);
        color: white;
        border-color: var(--maroon);
        border-width: 2px;
    }
    .admin-nav-btn.active:hover {
        background-color: #800000;
        border-color: #800000;
    }
    .admin-nav-btn:not(.active) {
        border: 2px solid #dee2e6;
        color: #495057;
        background-color: transparent !important;
    }
    .admin-nav-btn:not(.active):hover {
        border-color: #adb5bd;
        background-color: transparent !important;
    }

    /* Make admin tables horizontally scrollable and show a thin horizontal scrollbar */
    .table-responsive {
        overflow-x: auto;
        overflow-y: hidden;
        padding-bottom: 6px; /* space for thin scrollbar */
        border-radius: 6px;
    }
    .table-responsive .table {
        margin-bottom: 0; /* avoid extra gap below table inside scroll container */
        white-space: nowrap;
    }
    .table-responsive .table th,
    .table-responsive .table td {
        white-space: nowrap;
    }
    /* Thin horizontal scrollbar for WebKit */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: transparent;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.12);
        border-radius: 6px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: rgba(0,0,0,0.18);
    }
    /* Firefox */
    .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: rgba(0,0,0,0.12) transparent;
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
                    @php
                        $isAllUsersPage = request()->routeIs('admin.dashboard', 'admin.users.index') || ($activePage ?? null) === 'dashboard' || ($activePage ?? null) === 'users';
                    @endphp
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm admin-nav-btn {{ $isAllUsersPage ? 'active' : 'btn-outline-secondary' }}">
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
                    <a href="{{ route('admin.users.internal') }}" class="btn btn-sm admin-nav-btn {{ $activePage === 'internal_users' ? 'active btn-outline-secondary' : 'btn-outline-secondary' }}">
                        <i class="bi bi-shield-lock me-1"></i> User Internal
                    </a>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm admin-nav-btn {{ $activePage === 'customers' ? 'active btn-outline-secondary' : 'btn-outline-secondary' }}">
                        <i class="bi bi-person-badge me-1"></i> Pelanggan
                    </a>
                    <a href="{{ route('admin.payment_methods.index') }}" class="btn btn-sm admin-nav-btn {{ $activePage === 'payment' ? 'active btn-outline-secondary' : 'btn-outline-secondary' }}">
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
