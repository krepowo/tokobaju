@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator */
    $paginator = $paginator ?? null;
@endphp

@if ($paginator && $paginator->hasPages())
    <nav aria-label="Navigasi halaman" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap">
        @if ($paginator->onFirstPage())
            <span class="btn btn--ghost btn--sm is-disabled" style="opacity:.5; pointer-events:none">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn--ghost btn--sm">Sebelumnya</a>
        @endif

        <span style="font-size:13px; color:#6b7280">
            Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
        </span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn--ghost btn--sm">Selanjutnya</a>
        @else
            <span class="btn btn--ghost btn--sm is-disabled" style="opacity:.5; pointer-events:none">Selanjutnya</span>
        @endif
    </nav>
@endif
