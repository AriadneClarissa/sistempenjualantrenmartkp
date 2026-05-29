<div class="col">
    <div class="card h-100 border-0 shadow-sm p-2 product-card" style="border-radius: 16px; position: relative; cursor: pointer;"
            onclick="if(event.target.closest('form')) return; window.location.href='{{ route('produk.detail', ['id' => $item->kd_produk, 'from' => 'beranda']) }}'">

        @php
            $stokMinimal = $item->stok_minimal ?? $item->satuanModel?->stok_minimal ?? 0;
            $isLowStock = $stokMinimal > 0 && $item->stok_tersedia <= $stokMinimal;
            $isCustomerViewer = auth()->check() && auth()->user()->isCustomer();
            $isOutOfStock = $item->stok_tersedia <= 0;
            $satuanNama = $item->satuan?->nama_satuan ?? $item->satuan ?? 'pcs';
        @endphp
        
        {{-- Badge Status Stok --}}
        @if($item->stok_tersedia > 0)
            <div class="position-absolute" style="top: 12px; left: 12px; z-index: 10;">
                <span class="badge bg-success px-2 py-1" style="border-radius: 7px; font-size: 0.68rem;">
                    Tersedia
                </span>
            </div>
        @endif

        @if($isLowStock)
            @php
                $showWarning = true;
                // Jangan tampilkan warning stok untuk user yang merupakan pelanggan (umum atau langganan)
                if(auth()->check() && auth()->user()->isCustomer()) {
                    $showWarning = false;
                }
            @endphp

            @if($showWarning)
                <div class="position-absolute" style="top: 44px; left: 12px; z-index: 10;">
                    <span class="badge bg-warning text-dark px-2 py-1" style="border-radius: 7px; font-size: 0.68rem;">
                        Warning Stok
                    </span>
                </div>
            @endif
        @endif

           {{-- Area Foto Produk --}}
           <div class="d-flex align-items-center justify-content-center bg-light mb-3 position-relative"
               style="height: 150px; border-radius: 12px; overflow: hidden;">
                <div class="w-100 h-100 d-flex align-items-center justify-content-center"
                    style="{{ $isCustomerViewer && $isOutOfStock ? 'filter: blur(3px); opacity: 0.45;' : '' }}">
                   <img src="{{ \App\Helpers\StorageProxy::url($item->gambar) }}"
                     class="img-fluid"
                     alt="{{ $item->nama_produk }}"
                     style="max-height: 100%; object-fit: contain; mix-blend-mode: multiply;">
                </div>

                @if($isCustomerViewer && $isOutOfStock)
                   <div class="position-absolute top-50 start-50 translate-middle text-center px-3 py-2 bg-white shadow-sm"
                       style="border-radius: 999px; z-index: 11; pointer-events: none;">
                      <span class="fw-bold text-danger">Stok Habis</span>
                   </div>
                @endif
        </div>

        <div class="card-body p-0 d-flex flex-column flex-grow-1">
            {{-- Merk Produk --}}
            <p class="text-muted mb-1 text-truncate" style="font-size: 0.78rem;">
                {{ $item->merk->nama_merk ?? 'Tanpa Merk' }}
            </p>

            {{-- Nama Produk --}}
            <h5 class="fw-bold text-dark product-title-clamp mb-2" style="font-size: 0.95rem;">
                {{ $item->nama_produk }}
            </h5>

            {{-- Harga Utama & Satuan --}}
            <h4 class="fw-bold mb-1" style="color: #800000; font-size: 1.15rem;">
                Rp {{ number_format($item->harga_tampil, 0, ',', '.') }}
                <span class="text-muted fw-normal" style="font-size: 0.75rem;">/ {{ $satuanNama }}</span>
            </h4>

            {{-- Harga Langganan hanya untuk staf internal --}}
            @auth
                @if(auth()->user()->isInternalStaff())
                    <p class="mb-2 fw-semibold" style="color: #f08a24; font-size: 0.85rem;">
                        Langganan: Rp {{ number_format($item->harga_jual_langganan ?? $item->harga_jual_umum, 0, ',', '.') }}
                    </p>
                @endif
            @endauth

            {{-- Tombol Tambah (Hanya untuk Pelanggan) --}}
            @auth
                @if(auth()->user()->isCustomer())
                    @if($item->stok_tersedia > 0)
                        <form action="{{ route('cart.add', $item->kd_produk) }}" method="POST" class="add-to-cart-form mt-2">
                            @csrf
                            <button type="submit" class="btn w-100 py-2 d-flex align-items-center justify-content-center gap-1"
                                    style="background-color: #800000; color: white; border-radius: 10px; font-weight: 600; font-size: 0.9rem;">
                                <i class="bi bi-plus-lg"></i> Tambah
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn w-100 py-2 d-flex align-items-center justify-content-center gap-1" disabled
                                style="background-color: #d9d9d9; color: #7a7a7a; border-radius: 10px; font-weight: 600; font-size: 0.9rem; cursor: not-allowed;">
                            <i class="bi bi-x-circle"></i> Stok Habis
                        </button>
                    @endif
                @endif
            @endauth
        </div>
    </div>
</div>

<style>
    .product-card {
        transition: all 0.3s ease;
    }
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(128, 0, 0, 0.1) !important;
    }
    .product-card .btn:hover {
        background-color: #600000 !important;
        filter: brightness(1.1);
    }
    .product-card form {
        position: relative;
        z-index: 2;
    }
</style>

<style>
    .product-card {
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }

    .product-card .card-body {
        flex: 1 1 auto;
    }

    .product-title-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.7em;
        line-height: 1.35;
    }
</style>