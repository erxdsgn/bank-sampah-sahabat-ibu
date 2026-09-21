{{-- Simpan sebagai: resources/views/admin/partials/pagination.blade.php --}}
{{-- Pakai: {{ $paginator->links('admin.partials.pagination') }} --}}

@if ($paginator->hasPages())
    <nav class="pager" role="navigation" aria-label="Navigasi halaman">

        {{-- Sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span class="pager-btn is-disabled" aria-disabled="true" aria-label="Sebelumnya">
                <svg viewBox="0 0 24 24">
                    <path d="m15 18-6-6 6-6"></path>
                </svg>
            </span>
        @else
            <a class="pager-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">
                <svg viewBox="0 0 24 24">
                    <path d="m15 18-6-6 6-6"></path>
                </svg>
            </a>
        @endif

        {{-- Nomor halaman --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pager-dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pager-btn is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="pager-btn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Berikutnya --}}
        @if ($paginator->hasMorePages())
            <a class="pager-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">
                <svg viewBox="0 0 24 24">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </a>
        @else
            <span class="pager-btn is-disabled" aria-disabled="true" aria-label="Berikutnya">
                <svg viewBox="0 0 24 24">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </span>
        @endif

    </nav>
@endif
