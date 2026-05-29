@if ($paginator->hasPages())
    <nav aria-label="Pagination Log Aktivitas">
        <ul class="pagination justify-content-center flex-wrap gap-2 mb-2">
            {{-- Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link rounded-pill px-3 py-2">Sebelumnya</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link rounded-pill px-3 py-2" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
                </li>
            @endif

            {{-- Nomor Halaman --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled d-none d-sm-block"><span class="page-link rounded-pill px-3 py-2">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link rounded-pill px-3 py-2">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item d-none d-sm-block">
                                <a class="page-link rounded-pill px-3 py-2" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Berikutnya --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link rounded-pill px-3 py-2" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link rounded-pill px-3 py-2">Berikutnya</span>
                </li>
            @endif
        </ul>

        <div class="text-center small text-muted">
            Menampilkan {{ $paginator->firstItem() ?? 0 }} sampai {{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} hasil
        </div>
    </nav>
@endif
