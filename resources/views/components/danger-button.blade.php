<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-[#B9382E] hover:bg-[#9E2F26] active:bg-[#83261F] text-white text-xs font-semibold rounded-lg tracking-wide shadow-2xs transition duration-150 focus:outline-none focus:ring-2 focus:ring-[#B9382E] focus:ring-offset-2 disabled:opacity-50 cursor-pointer']) }}>
    {{ $slot }}
</button>
