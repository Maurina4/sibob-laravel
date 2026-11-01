<header class="h-16 px-6 lg:px-8 bg-white border-b border-[#E3ECE6] flex items-center justify-between sticky top-0 z-40">

    {{-- BAGIAN KIRI: BREADCRUMB & BURGER --}}
    <div class="flex items-center gap-3">
        {{-- Tombol Buka/Tutup Sidebar (Clean SVG Icon) --}}
        <button
            type="button"
            class="p-2 rounded-lg text-[#62756A] hover:text-[#132219] hover:bg-[#F7FAF8] transition border border-transparent hover:border-[#E3ECE6]"
            onclick="toggleSidebar()"
            aria-label="Toggle Sidebar"
            title="Menu Navigasi"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        {{-- Judul Halaman --}}
        <div class="flex items-center gap-2">
            <span class="text-xs uppercase tracking-wider text-[#62756A] font-medium hidden sm:inline">SIBOB /</span>
            <h1 class="text-base font-semibold text-[#132219] tracking-tight">
                @yield('page-title', 'Beranda')
            </h1>
        </div>
    </div>

    {{-- BAGIAN KANAN: BADGE INSTANSI & USER PROFILE --}}
    <div class="flex items-center gap-3 sm:gap-4">

        {{-- Badge Instansi Resmi --}}
        <div class="hidden md:flex items-center gap-2 px-3 py-1 rounded-full bg-[#EAF2ED] border border-[#D5E5DA]">
            <span class="w-2 h-2 rounded-full bg-[#1E5E38]"></span>
            <span class="text-xs font-medium text-[#1E5E38] tracking-tight">
                BPOM Banjarbaru
            </span>
        </div>

        {{-- User Profile Pill & Logout --}}
        @auth
            <div class="flex items-center gap-3 pl-2 sm:border-l sm:border-[#E3ECE6]">
                {{-- Info Pengguna --}}
                <a href="{{ route('profile.edit') }}" class="hidden sm:flex flex-col text-right group hover:opacity-85 transition" title="Edit Profil">
                    <span class="text-xs font-semibold text-[#132219] group-hover:text-[#163828] leading-tight">
                        {{ auth()->user()->name }}
                    </span>
                    <span class="text-[10px] font-medium text-[#62756A]">
                        {{ auth()->user()->role ?? 'Petugas' }}
                    </span>
                </a>

                {{-- Avatar Initial --}}
                <a href="{{ route('profile.edit') }}" class="w-8 h-8 rounded-lg bg-[#163828] hover:bg-[#1E4834] text-white flex items-center justify-center text-xs font-bold uppercase shadow-2xs transition" title="Profil Pengguna">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </a>

                {{-- Tombol Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="inline-block">
                    @csrf
                    <button
                        type="submit"
                        class="p-2 rounded-lg text-[#62756A] hover:text-[#9C322C] hover:bg-[#FDF0EE] transition border border-transparent hover:border-[#F8D4D0]"
                        title="Keluar dari Sistem"
                        aria-label="Logout"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        @endauth

    </div>

</header>

{{-- JAVASCRIPT TOGGLE SIDEBAR --}}
<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.side');
        if (!sidebar) return;

        if (window.innerWidth > 900) {
            sidebar.classList.toggle('mini');
        } else {
            sidebar.classList.toggle('mobile');
            sidebar.classList.toggle('buka');
        }
    }

    window.addEventListener('resize', function () {
        const sidebar = document.querySelector('.side');
        if (!sidebar) return;
        if (window.innerWidth > 900) {
            sidebar.classList.remove('mobile', 'buka');
        }
    });
</script>