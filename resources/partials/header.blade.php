<header class="top">

    {{-- =====================================================
         BAGIAN KIRI
         ===================================================== --}}
    <div class="crumb">

        <button
            type="button"
            class="burger"
            onclick="sibobToggleSidebar()"
            aria-label="Buka atau tutup menu"
            title="Menu"
        >
            ☰
        </button>


        <span class="akhir">
            @yield('page-title', 'Beranda')
        </span>

    </div>


    {{-- =====================================================
         BAGIAN KANAN
         ===================================================== --}}
    <div class="header-right">

        <span class="header-office">
            BPOM Banjarbaru
        </span>


        <form
            method="POST"
            action="{{ route('logout') }}"
            style="display: inline;"
        >

            @csrf

            <button
                type="submit"
                class="logout-button"
            >
                Keluar
            </button>

        </form>

    </div>

</header>