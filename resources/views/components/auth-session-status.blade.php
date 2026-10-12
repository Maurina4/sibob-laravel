@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'p-3 rounded-lg bg-[#EAF5EE] border border-[#D5E5DA] text-xs font-medium text-[#1E5E38]']) }}>
        {{ $status }}
    </div>
@endif
