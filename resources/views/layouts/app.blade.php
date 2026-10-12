<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIBOB — Sistem Inventaris BMN BPOM')</title>

    {{-- Google Fonts: Plus Jakarta Sans & Instrument Serif --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- CSS + JS melalui Vite --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="bg-[#F7FAF8] text-[#132219] antialiased selection:bg-[#EAF2ED] selection:text-[#163828]">
    <div class="app min-h-screen flex">

        {{-- SIDEBAR --}}
        @include('partials.sidebar')

        {{-- AREA UTAMA --}}
        <main class="main flex-1 flex flex-col min-w-0 bg-[#F7FAF8] overflow-y-auto">

            {{-- HEADER --}}
            @include('partials.header')

            {{-- KONTEN HALAMAN --}}
            <div class="konten p-6 lg:p-8 max-w-7xl w-full mx-auto flex-1">

                {{-- PESAN BERHASIL --}}
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl border border-[#D5E5DA] bg-[#EAF5EE] text-[#1E5E38] text-sm flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-[#1E5E38]/10 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span class="font-medium">{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-[#1E5E38]/60 hover:text-[#1E5E38] text-lg leading-none">&times;</button>
                    </div>
                @endif

                {{-- PESAN ERROR --}}
                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl border border-[#F8D4D0] bg-[#FDF0EE] text-[#9C322C] text-sm flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-[#9C322C]/10 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span class="font-medium">{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-[#9C322C]/60 hover:text-[#9C322C] text-lg leading-none">&times;</button>
                    </div>
                @endif

                {{-- ISI HALAMAN --}}
                @yield('content')
                {{ $slot ?? '' }}

            </div>

            {{-- FOOTER KEDINASAN MINIMALIS --}}
            <footer class="py-4 px-6 border-t border-[#E3ECE6] text-xs text-[#62756A] flex flex-col sm:flex-row items-center justify-between gap-2 bg-white/50">
                <span>&copy; {{ date('Y') }} <strong>SIBOB</strong> — Balai Besar Pengawas Obat dan Makanan di Banjarmasin.</span>
                <span class="text-[11px] font-mono tracking-tight text-[#62756A]/80">BMN Management System v2.0</span>
            </footer>

        </main>

    </div>

    {{-- JAVASCRIPT TAMBAHAN --}}
    @stack('scripts')

</body>
</html>