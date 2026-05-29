@extends('layouts.app')

@section('content')
@include('admin._header', ['activePage' => 'customers'])

<div class="container-fluid">
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="fw-bold ms-0 mb-0">Daftar Pelanggan (Langganan & Umum)</h4>

        <form method="GET" action="{{ route('admin.customers.index') }}" class="ms-auto">
            <div class="position-relative">
                <select name="jenis" class="form-select shadow-sm"
                        onchange="this.form.submit()"
                        style="min-width: 160px; border-radius: 999px; border: 1px solid #e5e5e5; padding-left: 16px; padding-right: 40px; height: 42px; color: #495057; background-color: #fff;">
                    <option value="all" {{ ($customerType ?? 'all') === 'all' ? 'selected' : '' }}>Semua Pelanggan</option>
                    <option value="regular" {{ ($customerType ?? 'all') === 'regular' ? 'selected' : '' }}>Umum</option>
                    <option value="langganan" {{ ($customerType ?? 'all') === 'langganan' ? 'selected' : '' }}>Langganan</option>
                </select>
            </div>
        </form>
    </div>

    <div class="card shadow-sm w-100">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle w-100 mb-0" style="font-size: 0.95rem; table-layout: fixed;">
                    <thead class="table-light">
                        <tr>
                            <th class="py-1 px-2" style="width: 5%;">#</th>
                            <th class="py-1 px-2" style="width: 12%;">Kode Pelanggan</th>
                            <th class="py-1 px-2" style="width: 18%;">Nama</th>
                            <th class="py-1 px-2" style="width: 24%;">Email</th>
                            <th class="py-1 px-2" style="width: 12%;">Jenis Pelanggan</th>
                            <th class="py-1 px-2" style="width: 12%;">No. Telepon</th>
                            <th class="py-1 px-2" style="width: 13%;">Alamat</th>
                            <th class="py-1 px-2" style="width: 9%;">Organisasi</th>
                            <th class="py-1 px-2" style="width: 8%;">Tgl Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $c)
                            <tr>
                                <td class="py-1 px-2 text-truncate">{{ $c->id }}</td>
                                <td class="py-1 px-2 text-truncate">{{ $c->kd_pelanggan ?? '-' }}</td>
                                <td class="py-1 px-2 text-truncate">{{ $c->name }}</td>
                                <td class="py-1 px-2 text-truncate">{{ $c->email }}</td>
                                <td class="py-1 px-2 text-truncate">{{ ($c->customer_type ?? 'regular') === 'langganan' ? 'Langganan' : 'Umum' }}</td>
                                <td class="py-1 px-2 text-truncate">{{ $c->phone_number ?? '-' }}</td>
                                <td class="py-1 px-2 text-truncate">{{ $c->home_address ?? '-' }}</td>
                                <td class="py-1 px-2 text-truncate">{{ $c->organization_name ?? '-' }}</td>
                                <td class="py-1 px-2">{{ $c->created_at ? $c->created_at->format('d M Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Tidak ada pelanggan untuk filter yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
