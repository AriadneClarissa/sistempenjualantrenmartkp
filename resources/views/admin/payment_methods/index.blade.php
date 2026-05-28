@extends('layouts.app')

@section('content')
@include('admin._header', ['activePage' => 'payment'])

<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold ms-0">Manajemen Metode Pembayaran</h4>
    </div>

    <div class="mb-4">
        <form action="{{ route('admin.payment_methods.store') }}" method="POST">
            @csrf
            <div class="row g-2">
                <div class="col-md-3">
                    <input name="name" class="form-control" placeholder="Nama metode (Misal: BCA)" required>
                </div>
                <div class="col-md-3">
                    <input name="account_name" class="form-control" placeholder="Pemilik rekening">
                </div>
                <div class="col-md-3">
                    <input name="account_number" class="form-control" placeholder="No. rekening">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary px-4">Tambah</button>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white mb-0">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">Nama Metode</th>
                    <th width="25%">Pemilik Rekening</th>
                    <th width="25%">No. Rekening</th>
                    <th width="20%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($methods as $index => $m)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $m->name }}</td>
                    <td>{{ $m->account_name ?: '-' }}</td>
                    <td>{{ $m->account_number ?: '-' }}</td>
                    <td>
                        <form action="{{ route('admin.payment_methods.destroy', $m->id) }}" method="POST" class="m-0">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger px-3">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">Belum ada metode pembayaran yang ditambahkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection