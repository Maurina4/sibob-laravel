@props(['paginator'])

<div class="mt-4 pt-4 border-t border-[#E3ECE6] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#62756A]">
    <div class="font-medium">
        @if($paginator->total() > 0)
            Menampilkan <span class="font-semibold text-[#132219]">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-[#132219]">{{ $paginator->lastItem() }}</span> dari <span class="font-semibold text-[#132219]">{{ $paginator->total() }}</span> data
        @else
            Menampilkan 0 data
        @endif
    </div>

    @if($paginator->hasPages())
        <div class="bmn-pagination">
            {{ $paginator->withQueryString()->links() }}
        </div>
    @endif
</div>
