@props([
    'paginator' => null,
])

@if ($paginator && $paginator->hasPages())
    @php
        // linkCollection(): phần tử đầu là "Previous", phần tử cuối là "Next",
        // ở giữa là các số trang và dấu "..." (url = null)
        $links = $paginator->linkCollection();
        $pageLinks = $links->slice(1, $links->count() - 2);
    @endphp

    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center gap-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
        @endif

        {{-- Page numbers and "..." separators --}}
        @foreach ($pageLinks as $link)
            @if ($link['url'] === null)
                <span class="w-9 h-9 flex items-center justify-center text-gray-500 font-medium">
                    {{ $link['label'] }}
                </span>
            @elseif ($link['active'])
                <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-primary text-white font-semibold shadow-sm">
                    {{ $link['label'] }}
                </span>
            @else
                <a href="{{ $link['url'] }}" class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium transition">
                    {{ $link['label'] }}
                </a>
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @else
            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </span>
        @endif
    </nav>
@elseif (!$paginator)
    {{-- Mock display when no paginator passed (matches Figma 1 2 3 4 5) --}}
    <nav class="flex items-center justify-center gap-2">
        <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-primary text-white font-semibold shadow-sm">1</span>
        <button type="button" class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium transition">2</button>
        <button type="button" class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium transition">3</button>
        <button type="button" class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium transition">4</button>
        <button type="button" class="w-9 h-9 flex items-center justify-center rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium transition">5</button>
    </nav>
@endif