@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-sm transition py-2 px-3 disabled:bg-[#F7FAF8] disabled:text-[#8D9D95] shadow-2xs']) }}>
