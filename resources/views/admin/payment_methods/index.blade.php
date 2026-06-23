@extends('layouts.app')

@section('content')
@include('admin._header', ['activePage' => 'payment'])

<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold ms-0">Manajemen Metode Pembayaran</h4>
    </div>

    <div class="mb-4">
        <form id="formTambahMetode" action="{{ route('admin.payment_methods.store') }}" method="POST" novalidate>
            @csrf
            <div class="row g-2">
                <div class="col-md-3">
                    <input id="inputPaymentName" name="name" class="form-control" placeholder="Nama metode (Misal: BCA)" required>
                    <div class="invalid-feedback d-none text-danger">Wajib diisi.</div>
                </div>
                <div class="col-md-3">
                    <input id="inputAccountName" name="account_name" class="form-control" placeholder="Pemilik rekening" required>
                    <div class="invalid-feedback d-none text-danger">Wajib diisi.</div>
                </div>
                <div class="col-md-3">
                    <input id="inputAccountNumber" name="account_number" class="form-control" placeholder="No. rekening" required>
                    <div class="invalid-feedback d-none text-danger">Wajib diisi.</div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary px-4">Tambah</button>
                </div>
            </div>
        </form>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formTambahMetode');
    const inputs = [
        document.getElementById('inputPaymentName'),
        document.getElementById('inputAccountName'),
        document.getElementById('inputAccountNumber')
    ];

    function showInvalid(input) {
        input.classList.add('is-invalid');
        const fb = input.parentElement.querySelector('.invalid-feedback');
        if (fb) fb.classList.remove('d-none');
    }

    function clearInvalid(input) {
        input.classList.remove('is-invalid');
        const fb = input.parentElement.querySelector('.invalid-feedback');
        if (fb) fb.classList.add('d-none');
    }

    inputs.forEach(i => {
        i.addEventListener('input', function() { clearInvalid(i); });
    });

    form.addEventListener('submit', function(e) {
        let hasError = false;
        inputs.forEach(function(input) {
            if (!input.value || input.value.trim() === '') {
                showInvalid(input);
                hasError = true;
            }
        });

        if (hasError) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        return true; // allow submission
    });
});
</script>
@endpush

    <div class="card shadow-sm w-100">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle w-100 mb-0" style="font-size: 0.95rem; table-layout: fixed;">
                    <thead class="table-light">
                        <tr>
                            <th class="py-1 px-2" style="width: 5%;">#</th>
                            <th class="py-1 px-2" style="width: 25%;">Nama Metode</th>
                            <th class="py-1 px-2" style="width: 30%;">Pemilik Rekening</th>
                            <th class="py-1 px-2" style="width: 30%;">No. Rekening</th>
                            <th class="py-1 px-2" style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($methods as $index => $m)
                        <tr>
                            <td class="py-1 px-2 text-truncate">{{ $index + 1 }}</td>
                            <td class="py-1 px-2 text-truncate">{{ $m->name }}</td>
                            <td class="py-1 px-2 text-truncate">{{ $m->account_name ?? '-' }}</td>
                            <td class="py-1 px-2 text-truncate">{{ $m->account_number ?? '-' }}</td>
                            <td class="py-1 px-2 text-truncate">
                                <form action="{{ route('admin.payment_methods.destroy', $m->id) }}" method="POST" class="m-0 form-delete-payment" data-name="{{ $m->name }}" data-number="{{ $m->account_number ?? '-' }}">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger px-3 py-0">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-3 px-2 text-center text-muted">Belum ada metode pembayaran yang ditambahkan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="confirmDeleteBody">
                Apakah Anda yakin ingin menghapus rekening?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteButton">Yakin</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var formToDelete = null;
    var modalEl = document.getElementById('confirmDeleteModal');
    if (!modalEl) return;
    var bsModal = new bootstrap.Modal(modalEl);

    document.querySelectorAll('.form-delete-payment').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            formToDelete = form;
            // Set modal body text with method name and number
            var name = form.dataset.name || '';
            var number = form.dataset.number || '-';
            var body = document.getElementById('confirmDeleteBody');
            if (body) {
                body.textContent = 'Apakah Anda yakin ingin menghapus rekening ' + name + ' - ' + number + '?';
            }
            bsModal.show();
        });
    });

    document.getElementById('confirmDeleteButton').addEventListener('click', function() {
        if (formToDelete) {
            formToDelete.submit();
            formToDelete = null;
            bsModal.hide();
        }
    });
});
</script>
@endpush
@endsection