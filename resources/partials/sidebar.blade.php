<aside class="side" aria-label="Navigasi utama">

    {{-- =====================================================
         LOGO
         ===================================================== --}}

    <div class="side-logo">

        <div class="kotak" style="background:#fff;">

            <svg viewBox="0 0 50 50" width="22" height="22">

                <path
                    d="M13 9
                       C18 12,20 16,20 21
                       C20 27,16 34,11 39
                       C19 36,27 30,31 23
                       C34 18,34 13,31 9
                       C27 16,23 20,19 22
                       C18 17,16 13,13 9Z"
                    fill="#164f9b"
                />

                <path
                    d="M28 8
                       C35 12,38 18,37 24
                       C36 31,31 37,23 41
                       C31 37,37 32,40 26
                       C43 19,39 12,33 8
                       C31 7,30 7,28 8Z"
                    fill="#168b70"
                />

            </svg>

        </div>


        <div class="teks">

            <p class="side-title">
                SIBOB
            </p>

            <p class="side-subtitle">
                Inventaris BMN BPOM
            </p>

        </div>

    </div>


    {{-- =====================================================
         NAVIGASI
         ===================================================== --}}

    <nav class="side-nav">

        {{-- =================================================
             BERANDA
             ================================================= --}}

        <a
            href="{{ route('dashboard') }}"
            class="nav-item {{ request()->routeIs('dashboard') ? 'aktif' : '' }}"
            title="Beranda"
        >

            <span class="nav-icon">

                <svg viewBox="0 0 24 24">

                    <path d="M3 10.5L12 3l9 7.5"></path>

                    <path d="M5 9.5V21h14V9.5"></path>

                    <path d="M9 21v-7h6v7"></path>

                </svg>

            </span>


            <span class="teks">
                Beranda
            </span>

        </a>


        {{-- =================================================
             MENU OPERATOR
             ================================================= --}}

        @if(auth()->user()->role === 'Operator')


            {{-- =================================================
                 BMN
                 ================================================= --}}

            <div
                class="nav-section"
                data-section="bmn"
            >

                <button
                href="{{ route('bmns.index') }}"
                    type="button"
                    class="nav-grup-link"
                    aria-label="Buka menu BMN"
                    aria-expanded="false"
                >

                    <span class="nav-group-icon">

                        <svg viewBox="0 0 24 24">

                            <rect
                                x="4"
                                y="3"
                                width="16"
                                height="18"
                                rx="2"
                            ></rect>

                            <path d="M8 7h8"></path>

                            <path d="M8 11h8"></path>

                            <path d="M8 15h5"></path>

                        </svg>

                    </span>


                    <span class="nav-group-title">
                        BMN
                    </span>


                    <span class="nav-arrow">
                        ›
                    </span>

                </button>


                <div class="nav-submenu">

                    <a
                        href="{{ route('bmns.index') }}"
                        class="nav-item sub {{ request()->routeIs('bmns.*') ? 'aktif' : '' }}"
                    >

                        <span class="nav-icon">

                            <svg viewBox="0 0 24 24">

                                <path d="M6 3h9l4 4v14H6z"></path>

                                <path d="M15 3v5h4"></path>

                                <path d="M9 13h6"></path>

                                <path d="M9 17h6"></path>

                            </svg>

                        </span>


                        <span class="teks">
                            Daftar BMN
                        </span>

                    </a>


                    <a
                        href="{{ route('pengembalians.index') }}"
                        class="nav-item sub {{ request()->routeIs('pengembalians.*') ? 'aktif' : '' }}"
                    >

                        <span class="nav-icon">

                            <svg viewBox="0 0 24 24">

                                <path d="M9 7H4v5"></path>

                                <path d="M4 12l5-5"></path>

                                <path d="M5 12a8 8 0 1 0 2.3-5.7"></path>

                            </svg>

                        </span>


                        <span class="teks">
                            Pengembalian
                        </span>

                    </a>


                    <a
                        href="{{ route('bmns-tik.index') }}"
                        class="nav-item sub {{ request()->routeIs('bmns-tik.*') ? 'aktif' : '' }}"
                    >

                        <span class="nav-icon">

                            <svg viewBox="0 0 24 24">

                                <rect
                                    x="3"
                                    y="4"
                                    width="18"
                                    height="13"
                                    rx="1"
                                ></rect>

                                <path d="M8 21h8"></path>

                                <path d="M12 17v4"></path>

                            </svg>

                        </span>


                        <span class="teks">
                            BMN TIK
                        </span>

                    </a>


                    <a
                        href="{{ route('bmns-lab.index') }}"
                        class="nav-item sub {{ request()->routeIs('bmns-lab.*') ? 'aktif' : '' }}"
                    >

                        <span class="nav-icon">

                            <svg viewBox="0 0 24 24">

                                <path d="M9 3h6"></path>

                                <path d="M10 3v7l-5 9h14l-5-9V3"></path>

                                <path d="M7 16h10"></path>

                            </svg>

                        </span>


                        <span class="teks">
                            BMN Lab
                        </span>

                    </a>

                </div>

            </div>


            {{-- =================================================
                 BA
                 ================================================= --}}

            <div
                class="nav-section"
                data-section="ba"
            >

                <button
                    type="button"
                    class="nav-grup-link"
                    aria-label="Buka menu BA"
                    aria-expanded="false"
                >

                    <span class="nav-group-icon">

                        <svg viewBox="0 0 24 24">

                            <path d="M4 12h15"></path>

                            <path d="M14 7l5 5-5 5"></path>

                        </svg>

                    </span>


                    <span class="nav-group-title">
                        BA
                    </span>


                    <span class="nav-arrow">
                        ›
                    </span>

                </button>


                <div class="nav-submenu">

                    <a
                        href="{{ route('ba-perpindahan.index') }}"
                        class="nav-item sub {{ request()->routeIs('ba-perpindahan.*') ? 'aktif' : '' }}"
                    >

                        <span class="nav-icon">

                            <svg viewBox="0 0 24 24">

                                <path d="M4 12h15"></path>

                                <path d="M14 7l5 5-5 5"></path>

                            </svg>

                        </span>


                        <span class="teks">
                            BA Perpindahan
                        </span>

                    </a>


                    <a
                        href="{{ route('distribusis.index') }}"
                        class="nav-item sub {{ request()->routeIs('distribusis.*') ? 'aktif' : '' }}"
                    >

                        <span class="nav-icon">

                            <svg viewBox="0 0 24 24">

                                <rect
                                    x="3"
                                    y="7"
                                    width="13"
                                    height="10"
                                    rx="1"
                                ></rect>

                                <path d="M16 10h3l3 3v4h-6"></path>

                                <circle
                                    cx="7"
                                    cy="19"
                                    r="2"
                                ></circle>

                                <circle
                                    cx="18"
                                    cy="19"
                                    r="2"
                                ></circle>

                            </svg>

                        </span>


                        <span class="teks">
                            Distribusi BMN
                        </span>

                    </a>

                </div>

            </div>


            {{-- =================================================
                 LAPORAN
                 ================================================= --}}

            <div
                class="nav-section"
                data-section="laporan"
            >

                <button
                    type="button"
                    class="nav-grup-link"
                    aria-label="Buka menu Laporan"
                    aria-expanded="false"
                >

                    <span class="nav-group-icon">

                        <svg viewBox="0 0 24 24">

                            <path d="M12 3l9 17H3z"></path>

                            <path d="M12 9v5"></path>

                            <path d="M12 17h.01"></path>

                        </svg>

                    </span>


                    <span class="nav-group-title">
                        Laporan
                    </span>


                    <span class="nav-arrow">
                        ›
                    </span>

                </button>


                <div class="nav-submenu">

                    <a
                        href="{{ route('laporan-kerusakan.index') }}"
                        class="nav-item sub {{ request()->routeIs('laporan-kerusakan.*') ? 'aktif' : '' }}"
                    >

                        <span class="nav-icon">

                            <svg viewBox="0 0 24 24">

                                <path d="M12 3l9 17H3z"></path>

                                <path d="M12 9v5"></path>

                                <path d="M12 17h.01"></path>

                            </svg>

                        </span>


                        <span class="teks">
                            Laporan Kerusakan
                        </span>

                    </a>

                </div>

            </div>


            {{-- =================================================
                 PENGATURAN
                 ================================================= --}}

            <div
                class="nav-section"
                data-section="pengaturan"
            >

                <button
                    type="button"
                    class="nav-grup-link"
                    aria-label="Buka menu Pengaturan"
                    aria-expanded="false"
                >

                    <span class="nav-group-icon">

                        <svg viewBox="0 0 24 24">

                            <circle
                                cx="12"
                                cy="12"
                                r="3"
                            ></circle>

                            <path
                                d="M19 12a7 7 0 0 0-.2-1.7l2-1.5-2-3.4-2.3.9a7 7 0 0 0-2.9-1.7L13.3 3h-2.6l-.3 2.6a7 7 0 0 0-2.9 1.7l-2.3-.9-2 3.4 2 1.5A7 7 0 0 0 5 12c0 .6.1 1.2.2 1.7l-2 1.5 2 3.4 2.3-.9a7 7 0 0 0 2.9 1.7l.3 2.6h2.6l.3-2.6a7 7 0 0 0 2.9-1.7l2.3.9 2-3.4-2-1.5A7 7 0 0 0 19 12z"
                            ></path>

                        </svg>

                    </span>


                    <span class="nav-group-title">
                        Pengaturan
                    </span>


                    <span class="nav-arrow">
                        ›
                    </span>

                </button>


                <div class="nav-submenu">

                    <a
                        href="{{ route('lokasis.index') }}"
                        class="nav-item sub {{ request()->routeIs('lokasis.*') ? 'aktif' : '' }}"
                    >

                        <span class="nav-icon">

                            <svg viewBox="0 0 24 24">

                                <path d="M12 21s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z"></path>

                                <circle
                                    cx="12"
                                    cy="9"
                                    r="2.5"
                                ></circle>

                            </svg>

                        </span>


                        <span class="teks">
                            Lokasi
                        </span>

                    </a>


                    <a
                        href="{{ route('data-terhapus.index') }}"
                        class="nav-item sub {{ request()->routeIs('data-terhapus.*') ? 'aktif' : '' }}"
                    >

                        <span class="nav-icon">

                            <svg viewBox="0 0 24 24">

                                <path d="M4 7h16"></path>

                                <path d="M10 11v6"></path>

                                <path d="M14 11v6"></path>

                                <path d="M6 7l1 14h10l1-14"></path>

                                <path d="M9 7V4h6v3"></path>

                            </svg>

                        </span>


                        <span class="teks">
                            Data Terhapus
                        </span>

                    </a>

                </div>

            </div>

        @endif


        {{-- =================================================
             MENU USER
             ================================================= --}}

        @if(auth()->user()->role !== 'Operator')

            <a
                href="{{ route('ba-perpindahan.index') }}"
                class="nav-item {{ request()->routeIs('ba-perpindahan.*') ? 'aktif' : '' }}"
                title="BA Perpindahan"
            >

                <span class="nav-icon">

                    <svg viewBox="0 0 24 24">

                        <path d="M4 12h15"></path>

                        <path d="M14 7l5 5-5 5"></path>

                    </svg>

                </span>


                <span class="teks">
                    BA Perpindahan
                </span>

            </a>


            <a
                href="{{ route('laporan-kerusakan.index') }}"
                class="nav-item {{ request()->routeIs('laporan-kerusakan.*') ? 'aktif' : '' }}"
                title="Laporan Kerusakan"
            >

                <span class="nav-icon">

                    <svg viewBox="0 0 24 24">

                        <path d="M12 3l9 17H3z"></path>

                        <path d="M12 9v5"></path>

                        <path d="M12 17h.01"></path>

                    </svg>

                </span>


                <span class="teks">
                    Laporan Kerusakan
                </span>

            </a>

        @endif

    </nav>


    {{-- =====================================================
         USER
         ===================================================== --}}

    <div class="side-user">

        <div class="avatar">

            {{
                collect(explode(' ', auth()->user()->name))
                    ->filter()
                    ->take(2)
                    ->map(
                        fn ($kata) =>
                            strtoupper(
                                substr($kata, 0, 1)
                            )
                    )
                    ->implode('')
            }}

        </div>


        <div class="teks side-user-text">

            <p class="side-user-name">
                {{ auth()->user()->name }}
            </p>

            <p class="side-user-role">
                {{ auth()->user()->role }}
            </p>

        </div>

    </div>

</aside>


<style>

/* =========================================================
   GROUP MENU
   ========================================================= */

.nav-grup-link {
    appearance: none;
    -webkit-appearance: none;

    display: flex !important;
    align-items: center;

    width: 100% !important;
    min-height: 48px;

    padding: 8px 20px;
    margin: 0;

    box-sizing: border-box;

    border: 0;
    border-left: 3px solid transparent;

    outline: none;

    font: inherit;
    text-align: left;

    color: rgba(255,255,255,.65);
    background: transparent;

    cursor: pointer;

    position: relative;
    z-index: 999;

    pointer-events: auto !important;

    user-select: none;
}

.nav-grup-link:hover {
    color: #fff !important;
    background: rgba(255,255,255,.08) !important;
}

.nav-grup-link.buka {
    color: #fff !important;
    background: rgba(255,255,255,.08) !important;
}


/* =========================================================
   IKON GROUP
   ========================================================= */

.nav-group-icon {
    width: 20px;
    height: 20px;
    min-width: 20px;

    display: flex;
    align-items: center;
    justify-content: center;

    pointer-events: none;
}

.nav-group-icon svg {
    width: 18px;
    height: 18px;

    fill: none;
    stroke: currentColor;

    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;

    pointer-events: none;
}

.nav-group-title {
    margin-left: 10px;
    flex: 1;

    pointer-events: none;
}

.nav-arrow {
    margin-left: auto;

    font-size: 20px;
    line-height: 1;

    transition: transform .2s ease;

    pointer-events: none;
}

.nav-grup-link.buka .nav-arrow {
    transform: rotate(90deg);
}


/* =========================================================
   SUBMENU
   ========================================================= */

.nav-submenu {
    display: none !important;
}

.nav-submenu.submenu-terbuka {
    display: block !important;
}


/* =========================================================
   DESKTOP MINI
   ========================================================= */

@media (min-width: 701px) {

    .side.mini {
        width: 72px !important;
    }

    .side.mini .nav-grup-link {
        width: 72px !important;

        height: 48px !important;
        min-height: 48px !important;

        padding: 0 !important;
        margin: 0 !important;

        justify-content: center !important;
        align-items: center !important;

        border-left: 0 !important;

        pointer-events: auto !important;
        cursor: pointer !important;
    }

    .side.mini .nav-group-icon {
        width: 20px !important;
        height: 20px !important;
        margin: 0 !important;
    }

    .side.mini .nav-group-title {
        display: none !important;
    }

    .side.mini .nav-arrow {
        display: none !important;
    }

    .side.mini .nav-submenu {
        display: none !important;
    }

    .side:not(.mini) .nav-submenu.submenu-terbuka {
        display: block !important;
    }

}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 700px) {

    .side {
        position: fixed !important;

        left: 0;
        top: 0;

        width: 240px !important;
        height: 100vh;

        transform: translateX(-100%);

        transition: transform .2s ease;

        z-index: 10000;
    }

    .side.show {
        transform: translateX(0) !important;
    }

    .side .nav-submenu.submenu-terbuka {
        display: block !important;
    }

}

</style>


<script>

(function () {

    function initSibobSidebar() {

        const sidebar = document.querySelector('.side');

        if (!sidebar) {
            return;
        }


        const nav = sidebar.querySelector('.side-nav');

        if (!nav) {
            return;
        }


        if (nav.dataset.sibobInitialized === '1') {
            return;
        }

        nav.dataset.sibobInitialized = '1';


        /*
        |--------------------------------------------------------------------------
        | GROUP MENU
        |--------------------------------------------------------------------------
        |
        | Klik BMN / BA / Laporan / Pengaturan hanya mengatur submenu.
        |
        | PENTING:
        | Klik group menu TIDAK membuka sidebar yang sedang mini.
        |
        */

        nav.addEventListener('click', function (event) {

            const button = event.target.closest('.nav-grup-link');

            if (!button) {
                return;
            }


            event.preventDefault();
            event.stopPropagation();


            /*
            |--------------------------------------------------------------------------
            | Jika sidebar sedang mini
            |--------------------------------------------------------------------------
            |
            | Jangan membuka sidebar.
            |
            | Sidebar hanya dibuka melalui:
            | 1. Burger
            | 2. Area sidebar
            |
            */

            if (sidebar.classList.contains('mini')) {
                return;
            }


            const section = button.closest('.nav-section');

            if (!section) {
                return;
            }


            const submenu =
                section.querySelector('.nav-submenu');


            /*
            |--------------------------------------------------------------------------
            | Tutup group lain
            |--------------------------------------------------------------------------
            */

            sidebar
                .querySelectorAll('.nav-section')
                .forEach(function (otherSection) {

                    if (otherSection === section) {
                        return;
                    }


                    const otherButton =
                        otherSection.querySelector(
                            '.nav-grup-link'
                        );


                    const otherSubmenu =
                        otherSection.querySelector(
                            '.nav-submenu'
                        );


                    if (otherButton) {

                        otherButton.classList.remove('buka');

                        otherButton.setAttribute(
                            'aria-expanded',
                            'false'
                        );

                    }


                    if (otherSubmenu) {

                        otherSubmenu.classList.remove(
                            'submenu-terbuka'
                        );

                    }

                });


            /*
            |--------------------------------------------------------------------------
            | Buka / tutup group
            |--------------------------------------------------------------------------
            */

            const sudahTerbuka =
                button.classList.contains('buka');


            if (sudahTerbuka) {

                button.classList.remove('buka');

                button.setAttribute(
                    'aria-expanded',
                    'false'
                );


                if (submenu) {

                    submenu.classList.remove(
                        'submenu-terbuka'
                    );

                }

            } else {

                button.classList.add('buka');

                button.setAttribute(
                    'aria-expanded',
                    'true'
                );


                if (submenu) {

                    submenu.classList.add(
                        'submenu-terbuka'
                    );

                }

            }

        });


        /*
        |--------------------------------------------------------------------------
        | KLIK AREA SIDEBAR
        |--------------------------------------------------------------------------
        |
        | Jika desktop dan sidebar mini:
        |
        | klik area kosong sidebar
        |        ↓
        | sidebar terbuka
        |
        | Tetapi klik menu/link tidak dianggap sebagai
        | klik area kosong.
        |
        */

        sidebar.addEventListener('click', function (event) {

            if (window.innerWidth <= 700) {
                return;
            }


            if (!sidebar.classList.contains('mini')) {
                return;
            }


            /*
            | Jangan anggap klik menu sebagai area kosong.
            */

            // if (
            //     event.target.closest('a') ||
            //     event.target.closest('button') ||
            //     event.target.closest('.nav-section')
            // ) {
            //     return;
            // }


            /*
            | Klik area kosong sidebar.
            */

            sidebar.classList.remove('mini');

        });


        /*
        |--------------------------------------------------------------------------
        | BURGER
        |--------------------------------------------------------------------------
        */

        window.sibobToggleSidebar = function () {

            const sidebarElement =
                document.querySelector('.side');

            if (!sidebarElement) {
                return;
            }


            if (window.innerWidth <= 700) {

                sidebarElement.classList.toggle('show');

            } else {

                sidebarElement.classList.toggle('mini');

            }

        };

    }


    /*
    |--------------------------------------------------------------------------
    | INIT
    |--------------------------------------------------------------------------
    */

    if (document.readyState === 'loading') {

        document.addEventListener(
            'DOMContentLoaded',
            initSibobSidebar
        );

    } else {

        initSibobSidebar();

    }

})();

</script>