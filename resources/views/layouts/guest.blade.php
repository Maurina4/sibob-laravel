<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIBOB — Sistem Inventaris BMN') }}</title>

        {{-- Fonts: Plus Jakarta Sans & Instrument Serif --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F7FAF8] text-[#132219] antialiased selection:bg-[#EAF2ED] selection:text-[#163828]">
        <div class="min-h-screen flex flex-col sm:justify-center items-center py-10 px-4 sm:px-6">
            {{-- BRAND HEADER --}}
            <div class="flex flex-col items-center mb-6">
                <a href="/" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#163828] border border-[#163828]/20 flex items-center justify-center shadow-xs">
                        <svg viewBox="0 0 50 50" class="w-6 h-6">
                            <path d="M13 9 C18 12,20 16,20 21 C20 27,16 34,11 39 C19 36,27 30,31 23 C34 18,34 13,31 9 C27 16,23 20,19 22 C18 17,16 13,13 9Z" fill="#38BDF8" />
                            <path d="M28 8 C35 12,38 18,37 24 C36 31,31 37,23 41 C31 37,37 32,40 26 C43 19,39 12,33 8 C31 7,30 7,28 8Z" fill="#4ADE80" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-tight text-[#132219]">SIBOB</span>
                        <div class="text-[10px] uppercase font-semibold tracking-wider text-[#62756A]">BBPOM di Banjarmasin</div>
                    </div>
                </a>
            </div>

            {{-- MAIN CARD --}}
            <div class="w-full sm:max-w-md bg-white border border-[#E3ECE6] rounded-2xl p-6 sm:p-8 shadow-2xs">
                {{ $slot }}
            </div>

            {{-- FOOTER KEDINASAN --}}
            <div class="mt-8 text-center text-xs text-[#62756A]">
                &copy; {{ date('Y') }} Balai Besar Pengawas Obat dan Makanan di Banjarmasin
            </div>
        </div>
    </body>
</html>
