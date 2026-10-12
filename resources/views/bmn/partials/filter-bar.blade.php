<div class="bg-white p-3.5 rounded-xl border border-[#E3ECE6] mb-5 shadow-2xs">
    <form method="GET" action="{{ route('bmns.index') }}" class="flex flex-wrap items-center gap-2.5">
        {{-- SEARCH --}}
        <div class="relative flex-1 min-w-[240px]">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#8D9D95]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari kode atau nama barang..."
                class="w-full pl-9 pr-3 py-2 bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] placeholder-[#8D9D95] transition"
            >
        </div>

        {{-- FILTER JENIS BARANG --}}
        <div>
            <select
                name="jenis"
                class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 px-3 transition cursor-pointer"
            >
                <option value="">Semua Jenis</option>
                <option value="BMN-TIK" {{ request('jenis') === 'BMN-TIK' ? 'selected' : '' }}>BMN-TIK</option>
                <option value="BMN-LAB" {{ request('jenis') === 'BMN-LAB' ? 'selected' : '' }}>BMN-LAB</option>
                <option value="BMN-NON TIK" {{ request('jenis') === 'BMN-NON TIK' ? 'selected' : '' }}>BMN-NON TIK</option>
            </select>
        </div>

        {{-- FILTER LOKASI --}}
        <div>
            <select
                name="lokasi"
                class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 px-3 transition cursor-pointer"
            >
                <option value="">Semua Lokasi</option>
                @foreach($lokasiFilters ?? [] as $lok)
                    <option value="{{ $lok }}" {{ request('lokasi') == $lok ? 'selected' : '' }}>
                        {{ $lok }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- FILTER PENANGGUNG JAWAB --}}
        <div>
            <select
                name="penanggungjawab"
                class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 px-3 transition cursor-pointer"
            >
                <option value="">Semua PJ</option>
                @foreach($penanggungjawabs ?? [] as $pj)
                    @php
                        $pjFormatted = is_object($pj) ? ($pj->nip ? "{$pj->nama} ({$pj->nip})" : $pj->nama) : $pj;
                        $pjVal = $pjFormatted;
                    @endphp
                    <option value="{{ $pjVal }}" {{ request('penanggungjawab') == $pjVal || (is_object($pj) && request('penanggungjawab') == $pj->nama) ? 'selected' : '' }}>
                        {{ $pjFormatted }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- FILTER KONDISI --}}
        <div>
            <select
                name="kondisi"
                class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 px-3 transition cursor-pointer"
            >
                <option value="">Semua Kondisi</option>
                @foreach($kondisis ?? [] as $kondisi)
                    <option value="{{ $kondisi }}" {{ request('kondisi') == $kondisi ? 'selected' : '' }}>
                        {{ $kondisi }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- TOMBOL CARI --}}
        <button
            type="submit"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-lg text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
        >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span>Cari</span>
        </button>

        {{-- RESET --}}
        @if(request()->hasAny(['search', 'jenis', 'lokasi', 'penanggungjawab', 'kondisi']))
            <a
                href="{{ route('bmns.index') }}"
                class="inline-flex items-center px-3 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-lg text-xs font-medium transition"
            >
                Reset
            </a>
        @endif
    </form>
</div>
