<aside class="side bg-[#123324] text-white w-64 min-h-screen flex flex-col shrink-0 border-r border-[#1B4332] z-50 transition-all duration-200" aria-label="Navigasi Utama SIBOB">

    {{-- LOGO & HEADER BRAND --}}
    <div class="h-16 px-5 border-b border-white/10 flex items-center gap-3 bg-[#0E281C]">
        <div class="w-9 h-9 rounded-lg bg-[#1B4332] border border-white/15 flex items-center justify-center shrink-0 shadow-2xs">
            <svg viewBox="0 0 50 50" class="w-5 h-5">
                <path d="M13 9 C18 12,20 16,20 21 C20 27,16 34,11 39 C19 36,27 30,31 23 C34 18,34 13,31 9 C27 16,23 20,19 22 C18 17,16 13,13 9Z" fill="#38BDF8" />
                <path d="M28 8 C35 12,38 18,37 24 C36 31,31 37,23 41 C31 37,37 32,40 26 C43 19,39 12,33 8 C31 7,30 7,28 8Z" fill="#4ADE80" />
            </svg>
        </div>
        <div class="flex flex-col min-w-0">
            <span class="font-bold text-sm tracking-wide text-white leading-tight">SIBOB</span>
            <span class="text-[10px] text-emerald-200/60 font-medium tracking-tight truncate">Inventaris BMN BPOM</span>
        </div>
    </div>

    {{-- NAVIGASI UTAMA --}}
    <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">

        {{-- SECTION LABEL --}}
        <div class="px-3 pt-2 pb-1 text-[10px] font-semibold tracking-wider uppercase text-emerald-300/40">
            Menu Utama
        </div>

        {{-- BERANDA --}}
        <a
            href="{{ route('dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('dashboard') ? 'bg-white/12 text-white font-semibold shadow-xs' : 'text-emerald-100/70 hover:bg-white/6 hover:text-white' }}"
        >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Beranda</span>
        </a>

        {{-- PENGAJUAN PENGEMBALIAN (USER & OPERATOR) --}}
        @if(config('fitur.pengajuan_pengembalian', true))
            @php
                $pendingPengembalianCount = (auth()->check() && auth()->user()->role === 'Operator')
                    ? \App\Models\PengajuanPengembalian::where('status', 'diajukan')->count()
                    : 0;
            @endphp
            <a
                href="{{ route('pengajuan-pengembalian.index') }}"
                class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('pengajuan-pengembalian.*') || request()->routeIs('admin.pengajuan-pengembalian.*') ? 'bg-white/12 text-white font-semibold shadow-xs' : 'text-emerald-100/70 hover:bg-white/6 hover:text-white' }}"
            >
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Pengajuan Pengembalian</span>
                </div>
                @if($pendingPengembalianCount > 0)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white leading-none">
                        {{ $pendingPengembalianCount }}
                    </span>
                @endif
            </a>
        @endif

        {{-- MODUL BMN (OPERATOR) --}}
        @if(auth()->user()->role === 'Operator')
            <div class="pt-2">
                <button
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-emerald-100/70 hover:bg-white/6 hover:text-white transition"
                    onclick="toggleSubmenu('submenu-bmn', this)"
                >
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Inventaris BMN</span>
                    </div>
                    <svg class="w-3.5 h-3.5 transition-transform {{ request()->routeIs('bmns.*', 'bmns-tik.*', 'bmns-lab.*') ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div id="submenu-bmn" class="pl-7 pr-1 pt-1 space-y-1 {{ request()->routeIs('bmns.*', 'bmns-tik.*', 'bmns-lab.*') ? 'block' : 'hidden' }}">
                    <a href="{{ session('nav_last_url.bmns', route('bmns.index')) }}" data-nav-key="bmns" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs transition {{ request()->routeIs('bmns.*') ? 'bg-white/12 text-white font-medium' : 'text-emerald-100/60 hover:text-white hover:bg-white/5' }}">
                        <span>Daftar BMN</span>
                    </a>

                    <a href="{{ session('nav_last_url.bmns-tik', route('bmns-tik.index')) }}" data-nav-key="bmns-tik" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs transition {{ request()->routeIs('bmns-tik.*') ? 'bg-white/12 text-white font-medium' : 'text-emerald-100/60 hover:text-white hover:bg-white/5' }}">
                        <span>BMN TIK</span>
                    </a>
                    <a href="{{ session('nav_last_url.bmns-lab', route('bmns-lab.index')) }}" data-nav-key="bmns-lab" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs transition {{ request()->routeIs('bmns-lab.*') ? 'bg-white/12 text-white font-medium' : 'text-emerald-100/60 hover:text-white hover:bg-white/5' }}">
                        <span>BMN Laboratorium</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- MODUL BERITA ACARA --}}
        <div class="pt-2">
            <button
                type="button"
                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium text-emerald-100/70 hover:bg-white/6 hover:text-white transition"
                onclick="toggleSubmenu('submenu-ba', this)"
            >
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Berita Acara</span>
                </div>
                <svg class="w-3.5 h-3.5 transition-transform {{ request()->routeIs('ba-perpindahan.*', 'distribusi.*', 'pengembalians.*') ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <div id="submenu-ba" class="pl-7 pr-1 pt-1 space-y-1 {{ request()->routeIs('ba-perpindahan.*', 'distribusi.*', 'pengembalians.*') ? 'block' : 'hidden' }}">
                <a href="{{ session('nav_last_url.ba-perpindahan', route('ba-perpindahan.index')) }}" data-nav-key="ba-perpindahan" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs transition {{ request()->routeIs('ba-perpindahan.*') ? 'bg-white/12 text-white font-medium' : 'text-emerald-100/60 hover:text-white hover:bg-white/5' }}">
                    <span>BA Perpindahan</span>
                </a>
                @if(auth()->user()->role === 'Operator')
                    <a href="{{ session('nav_last_url.distribusi', route('distribusi.index')) }}" data-nav-key="distribusi" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs transition {{ request()->routeIs('distribusi.*') ? 'bg-white/12 text-white font-medium' : 'text-emerald-100/60 hover:text-white hover:bg-white/5' }}">
                        <span>BA Distribusi</span>
                    </a>
                    <a href="{{ session('nav_last_url.pengembalians', route('pengembalians.index')) }}" data-nav-key="pengembalians" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs transition {{ request()->routeIs('pengembalians.*') ? 'bg-white/12 text-white font-medium' : 'text-emerald-100/60 hover:text-white hover:bg-white/5' }}">
                        <span>BA Pengembalian</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- DAFTAR RUSAK BERAT --}}
        <div class="pt-2">
            <a
                href="{{ session('nav_last_url.laporan-kerusakan', route('laporan-kerusakan.index')) }}"
                data-nav-key="laporan-kerusakan"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('laporan-kerusakan.*') ? 'bg-white/12 text-white font-semibold' : 'text-emerald-100/70 hover:bg-white/6 hover:text-white' }}"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Daftar Rusak Berat</span>
            </a>
        </div>

        {{-- PENGATURAN (OPERATOR) --}}
        @if(auth()->user()->role === 'Operator')
            <div class="pt-4">
                <div class="px-3 pb-1 text-[10px] font-semibold tracking-wider uppercase text-emerald-300/40">
                    Sistem & Master
                </div>
                <a
                    href="{{ session('nav_last_url.lokasis', route('lokasis.index')) }}"
                    data-nav-key="lokasis"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('lokasis.*') ? 'bg-white/12 text-white font-semibold' : 'text-emerald-100/70 hover:bg-white/6 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Master Lokasi</span>
                </a>
                <a
                    href="{{ session('nav_last_url.data-terhapus', route('data-terhapus.index')) }}"
                    data-nav-key="data-terhapus"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('data-terhapus.*') ? 'bg-white/12 text-white font-semibold' : 'text-emerald-100/70 hover:bg-white/6 hover:text-white' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Data Terhapus</span>
                </a>
            </div>
        @endif

    </nav>

    {{-- FOOTER SIDEBAR --}}
    <div class="p-3 border-t border-white/10 bg-[#0E281C]">
        <div class="px-3 py-2 rounded-lg bg-white/5 border border-white/5 text-[11px] text-emerald-200/70 flex items-center justify-between">
            <span>Balai POM BJM</span>
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
        </div>
    </div>

</aside>

{{-- SCRIPT TOGGLE SUBMENU & SYNC STATE --}}
<script>
    function toggleSubmenu(id, button) {
        const submenu = document.getElementById(id);
        if (!submenu) return;
        const isHidden = submenu.classList.contains('hidden');
        submenu.classList.toggle('hidden');
        const arrow = button.querySelector('svg:last-child');
        if (arrow) {
            arrow.classList.toggle('rotate-90', isHidden);
        }
    }

    (function() {
        const modulePrefixes = {
            '/bmns': 'bmns',
            '/pengembalians': 'pengembalians',
            '/bmns-tik': 'bmns-tik',
            '/bmns-lab': 'bmns-lab',
            '/ba-perpindahan': 'ba-perpindahan',
            '/distribusi': 'distribusi',
            '/laporan-kerusakan': 'laporan-kerusakan',
            '/lokasis': 'lokasis',
            '/data-terhapus': 'data-terhapus'
        };

        const path = window.location.pathname;

        // Simpan URL index aktif ke sessionStorage
        for (const [prefix, key] of Object.entries(modulePrefixes)) {
            if (path === prefix || path === prefix + '/') {
                sessionStorage.setItem('sibob_nav_' + key, window.location.href);
                break;
            }
        }

        // Terapkan URL tersimpan ke link sidebar jika ada
        document.querySelectorAll('a[data-nav-key]').forEach(function(link) {
            const key = link.getAttribute('data-nav-key');
            const saved = sessionStorage.getItem('sibob_nav_' + key);
            if (saved) {
                link.href = saved;
            }
        });

        // Tangani tombol Reset untuk membersihkan state tersimpan
        document.querySelectorAll('a').forEach(function(el) {
            const txt = (el.textContent || '').trim().toLowerCase();
            if (txt === 'reset') {
                el.addEventListener('click', function() {
                    for (const [prefix, key] of Object.entries(modulePrefixes)) {
                        if (path === prefix || path === prefix + '/') {
                            sessionStorage.removeItem('sibob_nav_' + key);
                        }
                    }
                });
            }
        });
    })();
</script>