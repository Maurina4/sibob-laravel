<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-[#F2F6F3] active:bg-[#EAF2ED] text-[#132219] border border-[#E3ECE6] text-xs font-semibold rounded-lg tracking-wide transition duration-150 focus:outline-none focus:ring-2 focus:ring-[#163828] focus:ring-offset-2 disabled:opacity-50 cursor-pointer']) }}>
    {{ $slot }}
</button>
