@if ($paginator->hasPages())
<nav role="navigation" aria-label="Navegación de páginas" class="flex items-center justify-between gap-4">

    <p class="text-xs tabular-nums" style="color: #a09080;">
        {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
        de {{ $paginator->total() }} diagnósticos
    </p>

    <div class="flex items-center gap-1">

        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <span class="flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg opacity-30"
                  style="border: 1px solid #e4ddd1; background: white;">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#7a7264" stroke-width="1.5">
                    <path d="M10 4L6 8l4 4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="flex h-8 w-8 items-center justify-center rounded-lg transition-all hover:brightness-95"
               style="border: 1px solid #e4ddd1; background: white; color: #3d3a30;">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M10 4L6 8l4 4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        @endif

        {{-- Números --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="flex h-8 items-center px-2 text-sm" style="color: #a09080;">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2.5 text-sm font-bold text-white"
                              style="background: #2a5c0f;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                           class="flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2.5 text-sm font-medium transition-all hover:brightness-95"
                           style="border: 1px solid #e4ddd1; background: white; color: #3d3a30;">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Siguiente --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="flex h-8 w-8 items-center justify-center rounded-lg transition-all hover:brightness-95"
               style="border: 1px solid #e4ddd1; background: white; color: #3d3a30;">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M6 4l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        @else
            <span class="flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg opacity-30"
                  style="border: 1px solid #e4ddd1; background: white;">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="#7a7264" stroke-width="1.5">
                    <path d="M6 4l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        @endif

    </div>
</nav>
@endif
