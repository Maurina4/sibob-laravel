<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Sistem — SIBOB</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="bg-[#F7FAF8] text-[#132219] antialiased selection:bg-[#EAF2ED] selection:text-[#163828] min-h-screen flex">

    <div class="w-full min-h-screen flex flex-col lg:flex-row">

        {{-- =====================================================
             BAGIAN KIRI: BRANDING HERO (DEEP FOREST PINE)
             ===================================================== --}}
        <section class="lg:w-5/12 bg-[#123324] text-white p-8 lg:p-14 flex flex-col justify-between relative overflow-hidden shrink-0 border-r border-[#1B4332]">

            {{-- BACKGROUND SUBTLE DECORATION (NO SLOP GRADIENTS) --}}
            <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 24px 24px;"></div>

            {{-- HEADER BRAND --}}
            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#1B4332] border border-white/20 flex items-center justify-center shrink-0 shadow-sm">
                        <svg viewBox="0 0 50 50" class="w-6 h-6">
                            <path d="M13 9 C18 12,20 16,20 21 C20 27,16 34,11 39 C19 36,27 30,31 23 C34 18,34 13,31 9 C27 16,23 20,19 22 C18 17,16 13,13 9Z" fill="#38BDF8" />
                            <path d="M28 8 C35 12,38 18,37 24 C36 31,31 37,23 41 C31 37,37 32,40 26 C43 19,39 12,33 8 C31 7,30 7,28 8Z" fill="#4ADE80" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-base tracking-wider text-white">SIBOB</div>
                        <div class="text-[10px] text-emerald-200/70 font-medium tracking-tight">BBPOM di Banjarmasin</div>
                    </div>
                </div>

                <div class="mt-14">
                    <span class="inline-block px-2.5 py-1 rounded-md bg-white/10 text-emerald-200 text-xs font-mono tracking-tight mb-4 border border-white/10">
                        Portal Aparatur Sipil Negara
                    </span>
                    <h1 class="text-3xl lg:text-4xl font-bold tracking-tight text-white leading-tight font-serif italic">
                        Pengelolaan Aset BMN yang Akuntabel & Presisi
                    </h1>
                    <p class="mt-4 text-emerald-100/70 text-sm leading-relaxed max-w-md">
                        Sistem terintegrasi untuk pencatatan, mutasi, kalibrasi instrumen laboratorium, dan berita acara Barang Milik Negara.
                    </p>
                </div>
            </div>

            {{-- FITUR UNGGULAN (MINIMALIST CHECKLIST) --}}
            <div class="my-8 relative z-10 space-y-3">
                <div class="flex items-center gap-3 text-xs text-emerald-100/80">
                    <span class="w-5 h-5 rounded-full bg-white/10 text-emerald-300 flex items-center justify-center shrink-0 font-bold text-[10px]">✓</span>
                    <span>Pelacakan status inventaris laboratorium & perangkat TIK</span>
                </div>
                <div class="flex items-center gap-3 text-xs text-emerald-100/80">
                    <span class="w-5 h-5 rounded-full bg-white/10 text-emerald-300 flex items-center justify-center shrink-0 font-bold text-[10px]">✓</span>
                    <span>Berita acara perpindahan & mutasi barang digital</span>
                </div>
                <div class="flex items-center gap-3 text-xs text-emerald-100/80">
                    <span class="w-5 h-5 rounded-full bg-white/10 text-emerald-300 flex items-center justify-center shrink-0 font-bold text-[10px]">✓</span>
                    <span>Monitoring kepatuhan jadwal kalibrasi alat uji BPOM</span>
                </div>
            </div>

            {{-- FOOTER INSTITUSI --}}
            <div class="pt-6 border-t border-white/10 text-xs text-emerald-200/60 relative z-10">
                Balai Besar Pengawas Obat dan Makanan di Banjarmasin &copy; {{ date('Y') }}
            </div>

        </section>

        {{-- =====================================================
             BAGIAN KANAN: FORM LOGIN (CRISP WHITE SURFACES)
             ===================================================== --}}
        <section class="flex-1 bg-[#F7FAF8] p-6 lg:p-14 flex items-center justify-center">

            <div class="w-full max-w-md bg-white border border-[#E3ECE6] rounded-2xl p-8 lg:p-10 shadow-sm">

                {{-- HEADER FORM --}}
                <div class="mb-8">
                    <h2 class="text-2xl font-bold tracking-tight text-[#132219]">Masuk ke Akun</h2>
                    <p class="mt-1.5 text-xs text-[#62756A]">
                        Gunakan email kedinasan ASN yang telah terdaftar pada sistem SIBOB.
                    </p>
                </div>

                {{-- PESAN ERROR --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl border border-[#F8D4D0] bg-[#FDF0EE] text-[#9C322C] text-xs space-y-1">
                        <strong class="font-semibold block">Autentikasi Gagal:</strong>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- FORM AUTH --}}
                <form method="POST" action="{{ route('login') }}" id="loginForm" class="space-y-5">
                    @csrf

                    {{-- EMAIL FIELD --}}
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-[#132219] mb-1.5">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@pom.go.id"
                                required
                                autofocus
                                class="w-full px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] text-sm placeholder:text-[#62756A]/50 focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                            >
                        </div>
                    </div>

                    {{-- PASSWORD FIELD --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-[#132219]">
                                Kata Sandi
                            </label>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Masukkan password"
                                required
                                class="w-full px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] text-sm placeholder:text-[#62756A]/50 focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white pr-16"
                            >
                            <button
                                type="button"
                                onclick="togglePassword()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-[#62756A] hover:text-[#132219] transition"
                            >
                                <span id="pwdToggleText">Lihat</span>
                            </button>
                        </div>
                    </div>

                    {{-- CAPTCHA FIELD --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-[#132219] mb-1.5">
                            Verifikasi Keamanan
                        </label>
                        <div class="flex items-center gap-3 mb-2">
                            <div
                                id="captchaCode"
                                class="flex-1 py-2 px-3 text-center rounded-lg bg-[#EAF2ED] border border-[#D5E5DA] font-mono text-base font-bold tracking-widest text-[#163828] select-none"
                            >
                                000000
                            </div>
                            <button
                                type="button"
                                onclick="generateCaptcha()"
                                class="px-3 py-2 rounded-lg border border-[#E3ECE6] hover:bg-[#F7FAF8] text-xs font-medium text-[#132219] transition shrink-0"
                            >
                                Perbarui
                            </button>
                        </div>
                        <input
                            id="captchaInput"
                            type="text"
                            name="captcha"
                            placeholder="Ketik 6 digit kode di atas"
                            maxlength="6"
                            required
                            autocomplete="off"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] text-sm placeholder:text-[#62756A]/50 focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white font-mono"
                        >
                        <div id="captchaError" class="text-xs text-[#9C322C] mt-1 hidden font-medium"></div>
                    </div>

                    {{-- TOMBOL SUBMIT --}}
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full py-3 px-4 rounded-xl bg-[#163828] hover:bg-[#1E4834] text-white text-sm font-semibold tracking-wide transition shadow-2xs flex items-center justify-center gap-2"
                        >
                            <span>Masuk ke Sistem</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>

                </form>

                {{-- INFO DEMO LOGIN --}}
                <div class="mt-8 pt-5 border-t border-[#E3ECE6] text-center">
                    <p class="text-[11px] text-[#62756A]">
                        Akun standar: <code class="px-1.5 py-0.5 rounded bg-[#F7FAF8] text-[#132219] font-mono">admin@pom.go.id</code> (Password: <code class="px-1.5 py-0.5 rounded bg-[#F7FAF8] text-[#132219] font-mono">password</code>)
                    </p>
                </div>

            </div>

        </section>

    </div>

    {{-- LOGIKA JAVASCRIPT CAPTCHA & TOGGLE --}}
    <script>
        let currentCaptcha = '';

        function generateCaptcha() {
            const chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
            let res = '';
            for (let i = 0; i < 6; i++) {
                res += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            currentCaptcha = res;
            const el = document.getElementById('captchaCode');
            if (el) el.textContent = res;
            const inp = document.getElementById('captchaInput');
            if (inp) inp.value = '';
            const err = document.getElementById('captchaError');
            if (err) err.classList.add('hidden');
        }

        function togglePassword() {
            const input = document.getElementById('password');
            const toggle = document.getElementById('pwdToggleText');
            if (!input || !toggle) return;
            if (input.type === 'password') {
                input.type = 'text';
                toggle.textContent = 'Tutup';
            } else {
                input.type = 'password';
                toggle.textContent = 'Lihat';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            generateCaptcha();

            const form = document.getElementById('loginForm');
            if (form) {
                form.addEventListener('submit', function (e) {
                    const inputVal = document.getElementById('captchaInput').value.trim().toUpperCase();
                    if (inputVal !== currentCaptcha) {
                        e.preventDefault();
                        const err = document.getElementById('captchaError');
                        if (err) {
                            err.textContent = 'Kode verifikasi tidak cocok. Silakan coba kembali.';
                            err.classList.remove('hidden');
                        }
                        generateCaptcha();
                    }
                });
            }
        });
    </script>

</body>
</html>