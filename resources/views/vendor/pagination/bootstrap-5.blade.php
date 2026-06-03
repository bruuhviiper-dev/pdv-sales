@if ($paginator->hasPages())
    <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <p class="small text-muted mb-0">
            Mostrando
            <span class="fw-semibold">{{ $paginator->firstItem() ?? 0 }}</span>
            a
            <span class="fw-semibold">{{ $paginator->lastItem() ?? 0 }}</span>
            de
            <span class="fw-semibold">{{ $paginator->total() }}</span>
            resultado(s)
        </p>

        <ul class="pagination pagination-sm mb-0">
            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link"><i class="ti ti-chevron-left"></i></span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="ti ti-chevron-left"></i></a>
                </li>
            @endif

            {{-- Números --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Próximo --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="ti ti-chevron-right"></i></a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link"><i class="ti ti-chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif
