@extends('layouts.app')

@section('content')
<style>
    .log-page-title {
        color: #660000;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .btn-rounded-soft {
        border-radius: 999px !important;
        padding: 0.55rem 1rem;
        font-weight: 600;
    }

    .log-table thead th {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .log-pagination .page-link {
        border-radius: 999px !important;
        margin: 0 4px;
        border: 1px solid #dee2e6;
        color: #660000;
        box-shadow: none !important;
    }

    .log-pagination .page-item.active .page-link {
        background: #660000;
        border-color: #660000;
        color: #fff;
    }

    .log-pagination .page-item.disabled .page-link {
        color: #adb5bd;
    }
</style>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-3 flex-wrap">
        <h1 class="mb-0 log-page-title">Log Aktivitas Internal</h1>
        <a href="{{ route('beranda') }}" class="btn btn-outline-secondary btn-rounded-soft">
            Kembali ke Beranda
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 70vh; overflow: auto;">
            <table class="table mb-0 align-middle log-table" style="min-width: 980px;">
                <thead style="position: sticky; top: 0; z-index: 2;">
                    <tr>
                        <th>#</th>
                        <th>Waktu</th>
                        <th>Pelaku</th>
                        <th>Aksi</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->created_at->copy()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $log->actor ? $log->actor->name . ' (' . $log->actor->email . ')' : 'System' }}</td>
                        <td>{{ $log->action }}</td>
                        <td style="max-width:420px;overflow-wrap:break-word">{{ $log->details }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada log</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <div class="log-pagination">
            {{ $logs->links('vendor.pagination.rounded-indonesia') }}
        </div>
    </div>
</div>
@endsection
