@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Pengaturan Ongkir</h3>
    <form action="{{ route('admin.shipping.update') }}" method="POST" id="shippingForm">
        @csrf
        <div class="mb-3">
            <label class="form-label">Tarif Ongkir Flat (Rp)</label>
                 <input type="number" name="flat_rate" id="flatRate"
                     value="{{ old('flat_rate', $settings->flat_rate ?? $settings->price_per_km ?? 15000) }}"
                     class="form-control" min="1" required readonly>
                 <div class="invalid-feedback d-none text-danger" id="flatRateFeedback">Tarif ongkir harus lebih besar dari 0.</div>
                 <small class="text-muted d-block mt-2">Nominal ini berlaku untuk semua pesanan delivery.</small>
        </div>

        <button type="button" class="btn btn-warning me-2" id="editBtn" onclick="enableEdit()">Edit</button>
        <button type="submit" class="btn btn-success d-none" id="saveBtn">Simpan Perubahan</button>
    </form>
</div>

<script>
    function enableEdit() {
        const flatRate = document.getElementById('flatRate');
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');

        if (flatRate) {
            flatRate.removeAttribute('readonly');
            flatRate.focus();
        }

        if (editBtn) {
            editBtn.classList.add('d-none');
        }

        if (saveBtn) {
            saveBtn.classList.remove('d-none');
        }
    }

    // Validasi client-side sebelum submit
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('shippingForm');
        const flatRate = document.getElementById('flatRate');
        const feedback = document.getElementById('flatRateFeedback');

        if (form && flatRate) {
            form.addEventListener('submit', function(e) {
                const val = parseInt(flatRate.value, 10);
                if (isNaN(val) || val <= 0) {
                    e.preventDefault();
                    flatRate.classList.add('is-invalid');
                    if (feedback) feedback.classList.remove('d-none');
                    return false;
                }
                return true;
            });

            flatRate.addEventListener('input', function() {
                const val = parseInt(flatRate.value, 10);
                if (!isNaN(val) && val > 0) {
                    flatRate.classList.remove('is-invalid');
                    if (feedback) feedback.classList.add('d-none');
                }
            });
        }
    });
</script>
@endsection