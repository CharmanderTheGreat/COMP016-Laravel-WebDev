@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex justify-center">
        <div class="join">
            @if ($paginator->onFirstPage())
                <button class="join-item btn btn-sm" disabled aria-disabled="true">&laquo;</button>
            @else
                <a class="join-item btn btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <button class="join-item btn btn-sm" disabled>{{ $element }}</button>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button class="join-item btn btn-sm btn-primary" aria-current="page">{{ $page }}</button>
                        @else
                            <a class="join-item btn btn-sm" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="join-item btn btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
            @else
                <button class="join-item btn btn-sm" disabled aria-disabled="true">&raquo;</button>
            @endif
        </div>
    </nav>
@endif
