@if ($paginator->hasPages())
    <div class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-4 overflow-x-auto">
        <!-- Pagination Info -->
        <div class="text-sm text-gray-600 whitespace-nowrap">
            Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }} — 
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} items
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center space-x-2">
            {{-- Previous Arrow --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1 border rounded text-gray-400 select-none">&lt;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1 border rounded hover:bg-gray-100">&lt;</a>
            @endif

            {{-- Page Numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3 py-1 text-gray-500">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-1 border rounded bg-blue-500 text-white font-semibold">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1 border rounded hover:bg-gray-100">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Arrow --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1 border rounded hover:bg-gray-100">&gt;</a>
            @else
                <span class="px-3 py-1 border rounded text-gray-400 select-none">&gt;</span>
            @endif
        </div>
    </div>
@endif
