@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $items */
    $items = $items ?? null;
@endphp
@if ($items && $items->hasPages())
    <div class="ad-pager">
        <span>Menampilkan {{ $items->firstItem() }}–{{ $items->lastItem() }} dari {{ $items->total() }} data</span>
        <div class="ad-pager__pages">
            @if ($items->onFirstPage())
                <span class="ad-pager__link is-disabled">Sebelumnya</span>
            @else
                <a href="{{ $items->previousPageUrl() }}" class="ad-pager__link">Sebelumnya</a>
            @endif

            @foreach ($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) as $nomor => $url)
                <a href="{{ $url }}" class="ad-pager__link {{ $nomor === $items->currentPage() ? 'is-active' : '' }}">{{ $nomor }}</a>
            @endforeach

            @if ($items->hasMorePages())
                <a href="{{ $items->nextPageUrl() }}" class="ad-pager__link">Selanjutnya</a>
            @else
                <span class="ad-pager__link is-disabled">Selanjutnya</span>
            @endif
        </div>
    </div>
@elseif ($items)
    <div class="ad-pager">
        <span>Menampilkan {{ $items->count() }} data</span>
    </div>
@endif