@extends('layouts.app')

@push('styles')
<style>
    :root { 
        --maroon-trenmart: #800000; 
        --accent-red: #e61e4d;
    }

    body { overflow-x: hidden; }

    /* Layout Wrapper */
    .cart-wrapper { 
        display: flex; 
        align-items: flex-start !important; 
    }

    /* Cards */
    .card-custom { border-radius: 15px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.05); background: white; margin-bottom: 20px; }
    .product-img { width: 85px; height: 85px; object-fit: cover; border-radius: 12px; background: #f1f1f1; }
    
    /* Qty Control (Diperbarui menyerupai gambar) */
    .qty-container { 
        border: 1px solid #eee; 
        border-radius: 10px; 
        padding: 4px 8px; 
        background: #fff; 
        display: inline-flex; 
        align-items: center; 
        gap: 4px; 
    }
    .qty-input { 
        width: 40px; 
        text-align: center; 
        border: none; 
        font-weight: 700; 
        background: transparent; 
        outline: none; 
        appearance: textfield; 
    }
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    
    .qty-btn { 
        border: none; 
        background: transparent; 
        color: #333; 
        width: 24px; 
        height: 24px; 
        border-radius: 6px; 
        font-weight: bold; 
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s; 
        cursor: pointer; 
    }
    .qty-btn:hover { background: #f8f9fa; color: var(--maroon-trenmart); }
    .qty-btn:disabled { opacity: 0.3; cursor: not-allowed; }

    .stock-out-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 999px;
        background: #ffe5e5;
        color: #c5163e;
        font-size: 0.72rem;
        font-weight: 700;
    }

    /* Sidebar Sticky */
    .summary-card { 
        background: white; 
        border-radius: 18px; 
        padding: 24px; 
        position: -webkit-sticky;
        position: sticky; 
        top: 20px; 
        border: none; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        will-change: transform; 
    }

    /* Button Bayar */
    .btn-checkout { 
        background: var(--accent-red); 
        color: white !important; 
        border-radius: 12px; 
        padding: 16px; 
        width: 100%; 
        font-weight: 700; 
        border: none; 
        transition: 0.3s; 
        display: flex; 
        justify-content: center; 
        align-items: center;
        text-decoration: none;
    }
    .btn-checkout:hover { background: #c5163e; transform: translateY(-2px); }

    /* Utility */
    .text-maroon { color: var(--maroon-trenmart); }
    .text-accent { color: var(--accent-red); }
    .btn-link-custom { text-decoration: none; font-weight: 600; font-size: 0.85rem; }
</style>
@endpush

@section('content')
<div class="container main-container pb-5">
    
    <div class="mb-3">
        <a href="{{ route('katalog') }}" class="text-muted text-decoration-none small">
            <i class="bi bi-chevron-left"></i> Kembali ke Belanja
        </a>
        <div class="d-flex align-items-center mt-1">
            <i class="bi bi-cart3 text-black-custom fs-2 me-3"></i> <div>
                <h3 class="fw-bold mb-0">Keranjang Belanja</h3>
            </div>
        </div>
    </div>

    {{-- Tampilkan Alert jika ada pesan error dari Controller --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row cart-wrapper g-4">
        
        {{-- KOLOM KIRI: DAFTAR BARANG --}}
        <div class="col-lg-8">
            <div class="card card-custom p-4">
                {{-- JUMLAH ITEM & HAPUS SEMUA --}}
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                    <h6 class="fw-bold mb-0">Produk ({{ count($items) }} item)</h6>
                    @if(count($items) > 0)
                        <form action="{{ route('cart.clear') }}" method="POST" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link text-danger btn-link-custom p-0 d-flex align-items-center" onclick="return confirm('Hapus semua item di keranjang?');">
                                <i class="bi bi-trash-fill me-1"></i> Hapus Semua
                            </button>
                        </form>
                    @endif
                </div>

                @forelse($items as $item)
                <div class="d-flex align-items-center py-3 border-bottom {{ $loop->last ? 'border-0' : '' }}">
                    @php
                        $isBundling = $item->bundling_id != null && $item->bundling;
                        // Dapatkan max stok spesifik untuk input validasi
                        $maxStock = $isBundling 
                            ? $item->bundling->availableStock() 
                            : ($item->produk->stok_tersedia ?? 0);
                        $stokHabis = $maxStock <= 0;
                    @endphp
                    
                    {{-- LOGIKA PEMISAHAN TAMPILAN BUNDLING & REGULER --}}
                    @if($isBundling)
                        @php
                            $gambarBundling = null;
                            if($item->bundling->items && $item->bundling->items->count() > 0) {
                                $produkPertama = $item->bundling->items->first()->produk;
                                $gambarBundling = $produkPertama ? $produkPertama->gambar : null;
                            }
                        @endphp
                        <img src="{{ \App\Helpers\StorageProxy::url($gambarBundling ?? 'images/no-image.png') }}" class="product-img me-3" style="object-fit: cover;">
                        
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h6 class="fw-bold mb-0 {{ $stokHabis ? 'text-muted' : 'text-danger' }}">{{ $item->bundling->name }}</h6>
                                @if($stokHabis)
                                    <span class="stock-out-badge"><i class="bi bi-exclamation-circle"></i> Habis</span>
                                @endif
                            </div>
                            <p class="text-muted small mb-1">Paket Bundling Hemat</p>
                            <h6 class="text-accent fw-bold mb-0">Rp {{ number_format($item->harga_at_time, 0, ',', '.') }}</h6>
                        </div>
                    @else
                        <img src="{{ \App\Helpers\StorageProxy::url($item->produk->gambar ?? 'images/no-image.png') }}" class="product-img me-3">
                        
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h6 class="fw-bold mb-0 {{ $stokHabis ? 'text-muted' : '' }}">{{ $item->produk->nama_produk }}</h6>
                                @if($stokHabis)
                                    <span class="stock-out-badge"><i class="bi bi-exclamation-circle"></i> Habis</span>
                                @endif
                            </div>
                            <p class="text-muted small mb-1">{{ $item->produk->merk->nama_merk ?? 'Trenmart' }}</p>
                            <h6 class="text-accent fw-bold mb-0">Rp {{ number_format($item->harga_at_time, 0, ',', '.') }}</h6>
                        </div>
                    @endif

                    <div class="text-end">
                        @unless($stokHabis)
                            {{-- Form input manual dengan tombol minus dan plus --}}
                            <form action="{{ route('cart.update', $item->id) }}" method="POST" class="qty-container mb-2 qty-form">
                                @csrf
                                @method('PUT')
                                <button type="button" class="qty-btn btn-minus">&minus;</button>
                                <input type="number"
                                       name="quantity"
                                       class="qty-input"
                                       value="{{ $item->jumlah }}"
                                       min="1"
                                       max="{{ $maxStock }}"
                                       step="1"
                                       inputmode="numeric"
                                       data-max="{{ $maxStock }}"
                                       aria-label="Jumlah produk">
                                <button type="button" class="qty-btn btn-plus">&plus;</button>
                            </form>
                        @endunless
                        <div class="fw-bold d-block">Rp {{ number_format($item->harga_at_time * $item->jumlah, 0, ',', '.') }}</div>
                        <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="m-0 mt-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link p-0 text-muted small shadow-none" onclick="return confirm('Hapus produk ini dari keranjang?');">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">Keranjang Anda kosong</div>
                @endforelse
            </div>
        </div>

        {{-- KOLOM KANAN: RINGKASAN PESANAN --}}
        <div class="col-lg-4">
            <div class="summary-card shadow-sm">
                <h6 class="fw-bold mb-4">Ringkasan Pesanan</h6>
                
                <div id="items-list">
                    @foreach($items as $item)
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span class="text-truncate" style="max-width: 160px;">
                            @if($item->bundling_id != null && $item->bundling)
                                {{ $item->bundling->name }}
                            @else
                                {{ $item->produk->nama_produk }}
                            @endif
                            ×{{ $item->jumlah }}
                        </span>
                        <span>Rp {{ number_format($item->harga_at_time * $item->jumlah, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
                
                <hr class="my-4 opacity-25">
                
                <div class="d-flex justify-content-between mb-2 text-muted small">
                    <span>Subtotal</span>
                    <span class="fw-bold text-dark">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-4 text-muted small">
                    <span>Ongkos Kirim</span>
                    <span class="fw-bold text-dark">Dihitung di checkout</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Total Bayar</h5>
                    <h4 class="fw-bold text-accent mb-0" id="total-label">Rp {{ number_format($total, 0, ',', '.') }}</h4>
                </div>

                @if(count($items) > 0)
                <a href="{{ route('checkout.index') }}" id="btnProceedCheckout" class="btn-checkout shadow-sm">
                    Lanjut ke Pembayaran <i class="bi bi-chevron-right ms-2"></i>
                </a>
                @else
                <button class="btn btn-secondary w-100 py-3 fw-bold border-0 opacity-50" disabled style="border-radius:12px;">Keranjang Kosong</button>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnProceedCheckout');
    if (btn) {
        btn.addEventListener('click', function(e) {
            try {
                if (window.currentUserNeedsProfileCompletion === true) {
                    e.preventDefault();
                    if (window.showProfileModal) window.showProfileModal(true);
                    return false;
                }
            } catch (err) {}
        });
    }

    // Handle form kuantitas dengan validasi Frontend
    document.querySelectorAll('.qty-form').forEach(function(form) {
        const input = form.querySelector('.qty-input');
        const btnMinus = form.querySelector('.btn-minus');
        const btnPlus = form.querySelector('.btn-plus');
        
        if (!input) return;

        const maxStock = parseInt(input.getAttribute('data-max'), 10);
        let lastValidValue = parseInt(input.value, 10) || 1;

        // Fungsi Validasi & Submit
        const validateAndSubmit = (newValue) => {
            if (newValue > maxStock) {
                alert('Produk tidak dapat melebihi stok yang ada (Sisa stok: ' + maxStock + ').');
                input.value = lastValidValue; // Kembalikan ke angka aman sebelumnya
            } else if (newValue < 1) {
                input.value = 1;
                if (lastValidValue !== 1) form.requestSubmit();
            } else {
                input.value = newValue;
                form.requestSubmit();
            }
        };

        // Event: Saat User Mengetik Langsung dan Tekan Enter
        input.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                validateAndSubmit(parseInt(input.value));
            }
        });

        // Event: Saat User Klik di Luar Kolom Input (Blur/Change)
        input.addEventListener('change', function() {
            if (input.value !== '') {
                validateAndSubmit(parseInt(input.value));
            }
        });

        // Event: Tombol Minus Ditekan
        if (btnMinus) {
            btnMinus.addEventListener('click', function() {
                let currentValue = parseInt(input.value) || 1;
                if (currentValue > 1) {
                    validateAndSubmit(currentValue - 1);
                }
            });
        }

        // Event: Tombol Plus Ditekan
        if (btnPlus) {
            btnPlus.addEventListener('click', function() {
                let currentValue = parseInt(input.value) || 1;
                validateAndSubmit(currentValue + 1);
            });
        }
    });
});
</script>
@endpush