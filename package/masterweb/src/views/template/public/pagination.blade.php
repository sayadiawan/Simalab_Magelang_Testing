@if ($paginator->hasPages())
<nav class="blog-pag">    
    <ul class="pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li><a href="#"><i class="fa fa-angle-left"></i><span class="sr-only">Previous</span></a></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl()  }}"><i class="fa fa-angle-left"></i><span class="sr-only">Previous</span></a></li>
            {{-- <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a></li> --}}
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                {{-- <li class="disabled"><span>{{ $element }}</span></li> --}}
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active" ><a href=""> {{ $page }} </a></li>
                        {{-- <li class="active"><span>{{ $page }}</span></li> --}}
                    @else
                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                        {{-- <li><a href="{{ $url }}">{{ $page }}</a></li> --}}
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}"><i class="fa fa-angle-right"></i><span class="sr-only">Next</span></a></li>
            {{-- <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a></li> --}}
        @else
            <li><a href="#"><i class="fa fa-angle-right"></i><span class="sr-only">Next</span></a></li>
            {{-- <li class="disabled"><span>&raquo;</span></li> --}}
        @endif
    </ul>
</nav>
@endif