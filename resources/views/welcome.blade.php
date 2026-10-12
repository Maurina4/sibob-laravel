<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBOB — Sistem Inventaris BMN BPOM Banjarmasin</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="bg-[#F7FAF8] text-[#132219] antialiased selection:bg-[#EAF2ED] selection:text-[#163828] min-h-screen flex flex-col justify-between">

    {{-- =====================================================
         NAVBAR
         ===================================================== --}}
    <header class="h-20 px-6 lg:px-12 border-b border-[#E3ECE6] bg-white/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto h-full flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#163828] flex items-center justify-center shrink-0 shadow-2xs">
                    <svg viewBox="0 0 50 50" class="w-6 h-6">
                        <path d="M13 9 C18 12,20 16,20 21 C20 27,16 34,11 39 C19 36,27 30,31 23 C34 18,34 13,31 9 C27 16,23 20,19 22 C18 17,16 13,13 9Z" fill="#38BDF8" />
                        <path d="M28 8 C35 12,38 18,37 24 C36 31,31 37,23 41 C31 37,37 32,40 26 C43 19,39 12,33 8 C31 7,30 7,28 8Z" fill="#4ADE80" />
                    </svg>
                </div>
                <div>
                    <span class="font-bold text-base tracking-tight text-[#132219] block leading-none">SIBOB</span>
                    <span class="text-[10px] text-[#62756A] font-medium tracking-tight">BBPOM di Banjarmasin</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="px-5 py-2.5 rounded-xl bg-[#163828] hover:bg-[#1E4834] text-white text-xs font-semibold tracking-wide transition shadow-2xs"
                    >
                        Buka Dashboard &rarr;
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="px-5 py-2.5 rounded-xl bg-[#163828] hover:bg-[#1E4834] text-white text-xs font-semibold tracking-wide transition shadow-2xs"
                    >
                        Masuk ke Sistem &rarr;
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- =====================================================
         HERO SECTION (EDITORIAL MINIMALIST)
         ===================================================== --}}
    <main class="flex-1 py-16 lg:py-24 px-6 lg:px-12 max-w-7xl mx-auto w-full">

        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EAF2ED] border border-[#D5E5DA] mb-6">
                <span class="w-2 h-2 rounded-full bg-[#1E5E38]"></span>
                <span class="text-xs font-medium text-[#1E5E38]">Sistem Informasi Barang Milik Negara</span>
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-[#132219] leading-[1.12]">
                Tata Kelola Aset & Mutasi BMN yang <span class="font-serif italic font-normal text-[#163828]">Akuntabel.</span>
            </h1>

            <p class="mt-6 text-base sm:text-lg text-[#62756A] leading-relaxed max-w-2xl">
                Platform digital resmi Balai Besar Pengawas Obat dan Makanan di Banjarmasin untuk inventarisasi terpadu, pemantauan kalibrasi instrumen pengujian, dan tertib administrasi berita acara.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-4">
                <a
                    href="{{ route('login') }}"
                    class="px-6 py-3.5 rounded-xl bg-[#163828] hover:bg-[#1E4834] text-white text-sm font-semibold tracking-wide transition shadow-xs flex items-center gap-2"
                >
                    <span>Mulai Akses Inventaris</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
                <div class="text-xs text-[#62756A] flex items-center gap-2 pl-2">
                    <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                    <span>Khusus Pegawai & Aparatur Sipil Negara BBPOM</span>
                </div>
            </div>
        </div>

        {{-- =====================================================
             BENTO FEATURE GRID
             ===================================================== --}}
        <div class="mt-20 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- BENTO 1: INVENTARISASI TERPADU --}}
            <div class="bg-white border border-[#E3ECE6] rounded-2xl p-8 hover:border-[#163828]/30 transition shadow-2xs">
                <div class="w-10 h-10 rounded-xl bg-[#EAF2ED] text-[#163828] flex items-center justify-center mb-6">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[#132219]">Sentralisasi Data Aset</h3>
                <p class="mt-2 text-xs text-[#62756A] leading-relaxed">
                    Pengelompokan otomatis BMN Umum, Alat Laboratorium, dan Perangkat TIK dalam satu basis data relasional berstandar NUP.
                </p>
            </div>

            {{-- BENTO 2: MONITORING KALIBRASI LAB --}}
            <div class="bg-white border border-[#E3ECE6] rounded-2xl p-8 hover:border-[#163828]/30 transition shadow-2xs">
                <div class="w-10 h-10 rounded-xl bg-[#FCF5E8] text-[#8E5F13] flex items-center justify-center mb-6">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[#132219]">Pengawasan Kalibrasi</h3>
                <p class="mt-2 text-xs text-[#62756A] leading-relaxed">
                    Pelacakan masa berlaku kalibrasi instrumen pengujian mutu obat dan makanan demi menjaga standar validitas hasil uji laboratorium.
                </p>
            </div>

            {{-- BENTO 3: BERITA ACARA DIGITAL --}}
            <div class="bg-white border border-[#E3ECE6] rounded-2xl p-8 hover:border-[#163828]/30 transition shadow-2xs">
                <div class="w-10 h-10 rounded-xl bg-[#EAF5EE] text-[#1E5E38] flex items-center justify-center mb-6">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[#132219]">Berita Acara & Mutasi</h3>
                <p class="mt-2 text-xs text-[#62756A] leading-relaxed">
                    Proses perpindahan tangan, peminjaman, dan serah terima aset terdokumentasi rapi tanpa risiko data tercecer.
                </p>
            </div>

        </div>

    </main>

    {{-- =====================================================
         FOOTER RESMI
         ===================================================== --}}
    <footer class="py-8 px-6 lg:px-12 border-t border-[#E3ECE6] bg-white">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#62756A]">
            <div>
                &copy; {{ date('Y') }} <strong>Balai Besar Pengawas Obat dan Makanan di Banjarmasin</strong>. Hak Cipta Dilindungi.
            </div>
            <div class="flex items-center gap-4 text-xs font-mono">
                <span>SIBOB v2.0</span>
                <span>•</span>
                <span>Sistem BMN Terintegrasi</span>
            </div>
        </div>
    </footer>

</body>
</html>
