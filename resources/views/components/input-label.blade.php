@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-xs tracking-wider uppercase text-[#3C4F44] mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
