@extends('layouts.app')

@push('styles')
<style>
    :root { 
        --maroon-trenmart: #800000; 
        --accent-red: #e61e4d;
    }

    body { overflow-x: hidden; }

    .cart-wrapper { 
        display: flex; 
        align-items: flex-start !important; 
    }

    .card-custom { border-radius: 15px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.05); background: white; margin-bottom: 20px; }
    .product-img { width: 85px; height: 85px; object-fit: cover; border-radius: 12px; background: #f1f1f1; transition: 0.3s; }
    
    .product-link { text-decoration: none; color: inherit; transition: 0.2s; }
    .product-link:hover .hover-text-accent { color: var(--accent-red) !important; }
    .product-link:hover .product-img { transform: scale(1.03); }

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

    .stock-out-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 6px;
        background: #ffe5e5;
        color: #c5163e;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .badge-disabled {
        background: #e9ecef;
        color: #6c757d;
    }

    /* Efek visual jika barang bermasalah */
    .item-invalid {
        opacity: 0.55;
        background-color: #fcfcfc;
        border-radius: 10px;
        padding: 10px;
        position: relative;
    }

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
    
    .btn-checkout-disabled {
        background: #d6d6d6 !important;
        color: #888 !important;
        cursor: not-allowed;
        transform: none !important;
    }

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
            <i class="bi bi-cart3 text-black-custom fs-2 me-3"></i> 
            <div>
                <h3 class="fw-bold mb-0">Keranjang Belanja</h3>
            </div>
        </div>
    </div>

    {{-- ALERT UTAMA JIKA ADA BARANG BERMASALAH (Langsung Muncul di Atas) --}}
    @if(isset($hasInvalidItem) && $hasInvalidItem)
        <div class="alert alert-danger d-flex align-items-center shadow-sm border-0 mb-4" role="alert" style="border-radius: 12px;">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>Perhatian!</strong> Ada produk di keranjang Anda yang saat ini <strong>Dinonaktifkan</strong> atau <strong>Stoknya Habis</strong>. 
                Anda harus menghapus produk tersebut dari keranjang sebelum dapat melanjutkan pembayaran.
            </div>
        </div>
    @endif

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
                @php
                    $isBundling = $item->bundling_id != null && $item->bundling;
                    $maxStock = $isBundling 
                        ? $item->bundling->availableStock() 
                        : ($item->produk->stok_tersedia ?? 0);
                @endphp

                <div class="d-flex align-items-center py-3 border-bottom {{ $loop->last ? 'border-0' : '' }} {{ $item->is_invalid ? 'item-invalid' : '' }}">
                    
                    {{-- AREA SEBELAH KIRI (INFO PRODUK) --}}
                    @if($isBundling)
                        <a href="{{ route('bundling.detail', $item->bundling_id) }}" class="product-link d-flex align-items-center flex-grow-1" style="{{ $item->is_invalid ? 'pointer-events: none;' : '' }}">
                            @php
                                $gambarBundling = null;
                                if($item->bundling->items && $item->bundling->items->count() > 0) {
                                    $produkPertama = $item->bundling->items->first()->produk;
                                    $gambarBundling = $produkPertama ? $produkPertama->gambar : null;
                                }
                            @endphp
                            <img src="{{ \App\Helpers\StorageProxy::url($gambarBundling ?? 'images/no-image.png') }}" class="product-img me-3">
                            
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <h6 class="fw-bold mb-0 hover-text-accent text-dark">{{ $item->bundling->name }}</h6>
                                </div>
                                <p class="text-muted small mb-1">Paket Bundling Hemat</p>
                                
                                {{-- LABEL KHUSUS BARANG BERMASALAH --}}
                                @if($item->is_invalid)
                                    <div class="mb-1 mt-1">
                                        @if($item->invalid_reason == 'nonaktif')
                                            <span class="stock-out-badge badge-disabled"><i class="bi bi-slash-circle"></i> Produk Dinonaktifkan</span>
                                        @else
                                            <span class="stock-out-badge"><i class="bi bi-exclamation-circle"></i> Stok Habis Dibeli</span>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-muted mb-1" style="font-size: 0.75rem;">Sisa stok: {{ $maxStock }}</p>
                                @endif
                                
                                <h6 class="text-accent fw-bold mb-0">
                                    {{ $item->is_invalid ? 'Tidak Tersedia' : 'Rp ' . number_format($item->harga_at_time, 0, ',', '.') }}
                                </h6>
                            </div>
                        </a>
                    @else
                        <a href="{{ route('produk.detail', $item->produk->kd_produk) }}" class="product-link d-flex align-items-center flex-grow-1" style="{{ $item->is_invalid ? 'pointer-events: none;' : '' }}">
                            <img src="{{ \App\Helpers\StorageProxy::url($item->produk->gambar ?? 'images/no-image.png') }}" class="product-img me-3">
                            
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <h6 class="fw-bold mb-0 hover-text-accent text-dark">{{ $item->produk->nama_produk }}</h6>
                                </div>
                                <p class="text-muted small mb-1">{{ $item->produk->merk->nama_merk ?? 'Trenmart' }}</p>
                                
                                {{-- LABEL KHUSUS BARANG BERMASALAH --}}
                                @if($item->is_invalid)
                                    <div class="mb-1 mt-1">
                                        @if($item->invalid_reason == 'nonaktif')
                                            <span class="stock-out-badge badge-disabled"><i class="bi bi-slash-circle"></i> Produk Dinonaktifkan</span>
                                        @else
                                            <span class="stock-out-badge"><i class="bi bi-exclamation-circle"></i> Stok Habis Dibeli</span>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-muted mb-1" style="font-size: 0.75rem;">Sisa stok: {{ $maxStock }}</p>
                                @endif
                                
                                <h6 class="text-accent fw-bold mb-0">
                                    {{ $item->is_invalid ? 'Tidak Tersedia' : 'Rp ' . number_format($item->harga_at_time, 0, ',', '.') }}
                                </h6>
                            </div>
                        </a>
                    @endif

                    {{-- AREA SEBELAH KANAN (QTY & HAPUS) --}}
                    <div class="text-end ms-3">
                        @if(!$item->is_invalid)
                            {{-- Jika normal, tampilkan kontrol kuantitas --}}
                            <form action="{{ route('cart.update', $item->id) }}" method="POST" class="qty-container mb-2 qty-form">
                                @csrf
                                @method('PUT')
                                <button type="button" class="qty-btn btn-minus">&minus;</button>
                                <input type="number" name="quantity" class="qty-input" value="{{ $item->jumlah }}" min="1" max="{{ $maxStock }}" step="1" inputmode="numeric" data-max="{{ $maxStock }}">
                                <button type="button" class="qty-btn btn-plus">&plus;</button>
                            </form>
                            <div class="fw-bold d-block">Rp {{ number_format($item->harga_at_time * $item->jumlah, 0, ',', '.') }}</div>
                        @else
                            {{-- Jika bermasalah, paksa pelanggan menghapus --}}
                            <div class="text-danger small fw-bold mb-2">Harap Hapus Item <i class="bi bi-arrow-down"></i></div>
                        @endif

                        <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="m-0 mt-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link p-0 text-muted small shadow-none" onclick="return confirm('Hapus produk ini dari keranjang?');">
                                <i class="bi bi-trash fs-5"></i>
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
                        @if(!$item->is_invalid)
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
                        @endif
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

                {{-- LOGIKA TOMBOL BAYAR (TERKUNCI ATAU BISA DIKLIK) --}}
                @if(isset($hasInvalidItem) && $hasInvalidItem)
                    {{-- TERKUNCI KARENA ADA BARANG BERMASALAH --}}
                    <button class="btn btn-checkout btn-checkout-disabled shadow-sm" disabled>
                        Lanjut ke Pembayaran <i class="bi bi-lock-fill ms-2"></i>
                    </button>
                    <div class="text-center text-danger small mt-2 fw-bold">
                        Hapus produk bermasalah untuk melanjutkan
                    </div>
                @elseif(count($items) > 0)
                    {{-- AMAN BISA DIKLIK --}}
                    <a href="{{ route('checkout.index') }}" id="btnProceedCheckout" class="btn btn-checkout shadow-sm">
                        Lanjut ke Pembayaran <i class="bi bi-chevron-right ms-2"></i>
                    </a>
                @else
                    {{-- KOSONG --}}
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

    document.querySelectorAll('.qty-form').forEach(function(form) {
        const input = form.querySelector('.qty-input');
        const btnMinus = form.querySelector('.btn-minus');
        const btnPlus = form.querySelector('.btn-plus');
        
        if (!input) return;

        const maxStock = parseInt(input.getAttribute('data-max'), 10);
        let lastValidValue = parseInt(input.value, 10) || 1;

        const validateAndSubmit = (newValue) => {
            if (newValue > maxStock) {
                alert('Produk tidak dapat melebihi stok yang ada (Sisa stok: ' + maxStock + ').');
                input.value = lastValidValue; 
            } else if (newValue < 1) {
                input.value = 1;
                if (lastValidValue !== 1) form.requestSubmit();
            } else {
                input.value = newValue;
                form.requestSubmit();
            }
        };

        input.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                validateAndSubmit(parseInt(input.value));
            }
        });

        input.addEventListener('change', function() {
            if (input.value !== '') {
                validateAndSubmit(parseInt(input.value));
            }
        });

        if (btnMinus) {
            btnMinus.addEventListener('click', function() {
                let currentValue = parseInt(input.value) || 1;
                if (currentValue > 1) {
                    validateAndSubmit(currentValue - 1);
                }
            });
        }

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