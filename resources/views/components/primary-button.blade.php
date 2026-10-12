<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white text-xs font-semibold rounded-lg tracking-wide shadow-2xs transition duration-150 focus:outline-none focus:ring-2 focus:ring-[#163828] focus:ring-offset-2 disabled:opacity-50 cursor-pointer']) }}>
    {{ $slot }}
</button>
