@props(['type' => null])

@if(!empty($type))
    @php
        $badgeClass = match(true) {
            $type === 'BMN-NON TIK' || str_contains($type, 'NON TIK') || str_contains($type, 'NON-TIK') || str_contains($type, 'NONTIK') => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]',
            $type === 'BMN-TIK & LAB' || (str_contains($type, 'TIK') && str_contains($type, 'LAB')) => 'bg-[#EBF3FE] text-[#1A73E8] border-[#C2DBFE]',
            $type === 'BMN-TIK' || str_contains($type, 'TIK') => 'bg-[#EAF2ED] text-[#163828] border-[#CFE1D5]',
            $type === 'BMN-LAB' || str_contains($type, 'LAB') => 'bg-[#E6F4EA] text-[#137333] border-[#BBE2C6]',
            default => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]'
        };
    @endphp
    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $badgeClass }} tracking-tight">
        {{ $type }}
    </span>
@else
    <span class="text-[#8D9D95] text-xs font-mono">—</span>
@endif
