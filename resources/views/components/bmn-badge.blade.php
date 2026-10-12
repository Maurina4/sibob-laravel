@props(['type' => null])

@if(!empty($type))
    @php
        $badgeClass = match($type) {
            'BMN-TIK' => 'bg-[#EAF2ED] text-[#163828] border-[#CFE1D5]',
            'BMN-LAB' => 'bg-[#E6F4EA] text-[#137333] border-[#BBE2C6]',
            'BMN-NON TIK' => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]',
            default => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]'
        };
    @endphp
    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $badgeClass }} tracking-tight">
        {{ $type }}
    </span>
@else
    <span class="text-[#8D9D95] text-xs font-mono">—</span>
@endif
