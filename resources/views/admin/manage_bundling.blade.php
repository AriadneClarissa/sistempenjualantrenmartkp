@extends('layouts.app')

@section('content')
<div class="container mt-4 mb-5">
    <div class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
        <h4 class="fw-bold mb-4">Tambah Paket Bundling</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('bundling.store') }}" method="POST">
            @csrf
            <input type="hidden" name="source" value="{{ $source }}">
            <div class="row">
                {{-- Data Utama Bundling --}}
                <div class="col-md-5">
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <label class="form-label fw-bold">Tipe Paket Bundling</label>
                        <select id="bundling_type" class="form-select mb-3 border-primary fw-bold" onchange="adjustProductRows()">
                            <option value="2" {{ old('bundling_type') == '2' ? 'selected' : '' }}>Bundling 2 Barang</option>
                            <option value="3" {{ old('bundling_type') == '3' ? 'selected' : '' }}>Bundling 3 Barang</option>
                        </select>

                        <label class="form-label fw-bold">Nama Paket</label>
                        <input type="text" name="name" class="form-control mb-3" 
                               placeholder="Contoh: Paket Alat Tulis Hemat" required 
                               value="{{ old('name') }}">

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-bold">Mulai Promo</label>
                                <input type="datetime-local" name="promo_start_at" class="form-control mb-3"
                                       value="{{ old('promo_start_at', now()->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">Selesai Promo</label>
                                <input type="datetime-local" name="promo_end_at" class="form-control mb-3"
                                       value="{{ old('promo_end_at') }}">
                            </div>
                        </div>

                        <div class="small text-muted mb-2">
                            Jika diisi, bundling hanya tampil selama periode promo ini aktif.
                        </div>
                    </div>
                </div>

                {{-- SEARCH PRODUK AJAX --}}
                <div class="col-md-7">
                    <div class="p-3 border rounded-3 shadow-sm bg-white mb-3">
                        <h6 class="fw-bold mb-2"><i class="bi bi-search me-2 text-primary"></i>Cari & Tambah Produk Cepat</h6>
                        <p class="text-muted small mb-3">Cari berdasarkan nama atau merk, lalu klik produk untuk memasukkannya ke baris paket.</p>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <input type="text" id="inputNamaProduk" class="form-control rounded-pill px-4 shadow-sm border-0 bg-light" placeholder="Ketik Nama Produk...">
                            </div>
                            <div class="col-md-6">
                                <input type="text" id="inputMerkProduk" class="form-control rounded-pill px-4 shadow-sm border-0 bg-light" placeholder="Ketik Merk...">
                            </div>
                        </div>

                        {{-- Dropdown Hasil Pencarian --}}
                        <div class="position-relative">
                            <div id="hasilPencarian" class="list-group shadow position-absolute w-100" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto; border-radius: 12px;"></div>
                        </div>
                    </div>

                    {{-- BARIS PRODUK BUNDLING --}}
                    <div class="p-3 border rounded-3 shadow-sm bg-white">
                        <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-box-seam me-2"></i>Daftar Produk dalam Paket</h6>
                        <div id="bundling-container">
                            {{-- Baris produk diatur oleh JavaScript --}}
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4 opacity-25">

            <div class="row justify-content-end">
                <div class="col-md-5">
                    <div class="card p-3 bg-light border-0 shadow-sm">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Harga Normal:</span>
                            <div class="fw-bold h5 mb-0">
                                Rp <span id="display_total_normal">0</span>
                                <input type="hidden" name="total_normal_price" id="input_total_normal" value="{{ old('total_normal_price', 0) }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">Harga Bundling (Harga Jual Baru)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success text-white border-0">Rp</span>
                                <input type="number" name="bundling_price" class="form-control border-success" 
                                       required value="{{ old('bundling_price') }}">
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <a href="{{ $source == 'beranda' ? route('beranda') : route('produk.index') }}" class="btn btn-outline-secondary px-4 fw-bold py-2">Batal</a>
                            <button type="submit" class="btn btn-primary py-3 fw-bold rounded-3 shadow-sm">
                                <i class="bi bi-check-lg me-2"></i>Simpan Paket Bundling
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Script & Select2 --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const produkData = @json($produk);

    $(document).ready(function() {
        adjustProductRows(); 
    });

    function adjustProductRows() {
        const type = document.getElementById('bundling_type').value;
        const container = document.getElementById('bundling-container');
        
        const currentSelections = [];
        document.querySelectorAll('.product-id').forEach(function(el) {
            currentSelections.push(el.value || '');
        });

        container.innerHTML = '';

        for (let i = 1; i <= type; i++) {
            const oldValue = currentSelections[i-1] || '';
            const rowHtml = `
                <div class="row g-2 mb-3 align-items-end item-row">
                    <div class="col-8">
                        <label class="small text-muted fw-bold">Slot Produk ${i}</label>
                        <div class="product-slot" style="position: relative;">
                            <input type="hidden" name="product_id[]" class="product-id" value="${oldValue}">
                            <input type="text" class="form-control product-display" placeholder="Belum ada produk" readonly ${oldValue ? 'value="' + oldValue + '"' : ''}>
                        </div>
                    </div>
                    <div class="col-4">
                        <input type="text" class="form-control bg-light price-display fw-bold text-dark" readonly placeholder="Rp 0">
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', rowHtml);
        }

        // If old selections exist (editing), populate display names and prices
        document.querySelectorAll('.item-row').forEach(function(row) {
            const hid = row.querySelector('.product-id');
            const disp = row.querySelector('.product-display');
            if (hid && hid.value) {
                const prod = produkData.find(p => p.kd_produk == hid.value);
                if (prod) {
                    const merkText = prod.merk ? prod.merk.nama_merk : 'Tanpa Merk';
                    disp.value = prod.nama_produk + ' (' + merkText + ')';
                    disp.dataset.price = prod.harga_jual_umum || 0;
                    disp.dataset.id = prod.kd_produk;
                    calculatePricesForRow(row);
                }
            }
        });
    }

    function calculatePricesForRow($row) {
        // read product-id and price from data-price attribute on product-display (set when selected)
        const priceDisplay = $row.querySelector('.price-display');
        const prodDisplay = $row.querySelector('.product-display');
        const price = prodDisplay ? parseFloat(prodDisplay.dataset.price || 0) : 0;
        priceDisplay.value = "Rp " + (isNaN(price) ? 0 : price).toLocaleString('id-ID');
        updateGrandTotal();
    }

    function updateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(function(row) {
            const prodDisplay = row.querySelector('.product-display');
            if (prodDisplay && prodDisplay.dataset.price) {
                total += parseFloat(prodDisplay.dataset.price || 0);
            }
        });
        document.getElementById('display_total_normal').innerText = total.toLocaleString('id-ID');
        document.getElementById('input_total_normal').value = total;
    }
    // --- LOGIC PENCARIAN AJAX DENGAN HARGA ---
    $('#inputNamaProduk, #inputMerkProduk').on('keyup', function() {
        let nama = $('#inputNamaProduk').val();
        let merk = $('#inputMerkProduk').val();

        if (nama.length >= 3 || merk.length >= 3) {
            $.ajax({
                url: "{{ route('bundling.search_ajax') }}", // Pastikan nama route ini sesuai di web.php
                method: "GET",
                data: { 
                    q: $('#inputNamaProduk').val(),
                    merk: $('#inputMerkProduk').val()
                },
                success: function(data) {
                    let html = '';
                    if (data.length > 0) {
                        data.forEach(function(item) {
                            // Mengambil nilai price langsung dari respons Controller
                            let rawPrice = item.price; 
                            
                            // Format ke Rupiah
                            let formattedPrice = new Intl.NumberFormat('id-ID', {
                                style: 'currency', 
                                currency: 'IDR', 
                                minimumFractionDigits: 0
                            }).format(rawPrice);

                            // Gunakan item.text, item.id, item.merk, dan rawPrice
                            html += `
                                <a href="javascript:void(0)" class="list-group-item list-group-item-action item-pencarian" 
                                    data-id="${item.id}" 
                                    data-nama="${item.text}" 
                                    data-price="${rawPrice}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold d-block text-dark">${item.text}</span>
                                            <small class="text-muted">ID: ${item.id} | Merk: ${item.merk}</small>
                                            <small class="d-block text-success fw-bold">${formattedPrice}</small>
                                        </div>
                                        <i class="bi bi-plus-circle-fill text-success fs-5"></i>
                                    </div>
                                </a>`;
                        });
                        $('#hasilPencarian').html(html).show();
                    } else {
                        $('#hasilPencarian').html('<div class="list-group-item text-danger small">Produk tidak ditemukan.</div>').show();
                    }
                },
                error: function(xhr) {
                    console.log("Error AJAX: ", xhr.responseText);
                }
            });
        } else {
            $('#hasilPencarian').hide();
        }
    });

    // MASUKKAN PRODUK KE BARIS SAAT HASIL PENCARIAN DIKLIK
    $(document).on('click', '.item-pencarian', function() {
        let id = $(this).data('id');
        let nama = $(this).data('nama');
        let price = $(this).data('price');

        let targetRow = null;
        document.querySelectorAll('.item-row').forEach(function(row) {
            const hidden = row.querySelector('.product-id');
            if (hidden && !hidden.value && !targetRow) {
                targetRow = row;
            }
        });

        if (targetRow) {
            const hidden = targetRow.querySelector('.product-id');
            const display = targetRow.querySelector('.product-display');
            hidden.value = id;
            display.value = nama;
            display.dataset.id = id;
            display.dataset.price = price;
            // Update price display for this row
            calculatePricesForRow(targetRow);

            $('#hasilPencarian').hide();
            $('#inputNamaProduk, #inputMerkProduk').val('');
        } else {
            alert('Semua baris bundling sudah terisi!');
        }
    });

    // Klik di luar hasil pencarian untuk menutup dropdown
    $(document).on('click', function (e) {
        if (!$(e.target).closest("#hasilPencarian, #inputNamaProduk, #inputMerkProduk").length) {
            $("#hasilPencarian").hide();
        }
    });
</script>
@endsection