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
                onchange="this.form.submit()"
                class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 pl-3 pr-9 transition cursor-pointer"
            >
                <option value="">Jenis</option>
                <option value="BMN-TIK" {{ request('jenis') === 'BMN-TIK' ? 'selected' : '' }}>BMN-TIK</option>
                <option value="BMN-LAB" {{ request('jenis') === 'BMN-LAB' ? 'selected' : '' }}>BMN-LAB</option>
                <option value="BMN-NON TIK" {{ request('jenis') === 'BMN-NON TIK' ? 'selected' : '' }}>BMN-NON TIK</option>
            </select>
        </div>

        {{-- FILTER TAHUN PEROLEHAN (MONEV PENGADAAN) --}}
        <div>
            <select
                name="tahun"
                onchange="this.form.submit()"
                class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 pl-3 pr-9 transition cursor-pointer font-medium"
                title="Filter Pengadaan Berdasarkan Tahun untuk Monev"
            >
                <option value="">Semua Tahun</option>
                <option value="current" {{ request('tahun') === 'current' ? 'selected' : '' }}>
                    Tahun Berjalan ({{ date('Y') }})
                </option>
                <option value="previous" {{ request('tahun') === 'previous' ? 'selected' : '' }}>
                    Tahun Sebelumnya ({{ date('Y') - 1 }})
                </option>
                <option value="prior_all" {{ request('tahun') === 'prior_all' ? 'selected' : '' }}>
                    Semua Thn Lalu (&le; {{ date('Y') - 1 }})
                </option>
                @foreach($tahunFilters ?? [] as $thn)
                    @if($thn != date('Y') && $thn != (date('Y') - 1))
                        <option value="{{ $thn }}" {{ request('tahun') == (string) $thn ? 'selected' : '' }}>
                            Tahun {{ $thn }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>

        {{-- FILTER LOKASI (SEARCHABLE DROPDOWN BERDASARKAN KODE RUANGAN) --}}
        @php
            $currentLokasiFilter = (string) request('lokasi', '');
            $selectedFilterLabel = 'Semua Lokasi';
            $selectedFilterKode = '';
            if ($currentLokasiFilter !== '') {
                $matchedFilter = ($lokasis ?? collect())->first(function($l) use ($currentLokasiFilter) {
                    return strcasecmp($l->format_kombinasi, $currentLokasiFilter) === 0
                        || strcasecmp($l->nama, $currentLokasiFilter) === 0
                        || ($l->kode_ruangan && strcasecmp($l->kode_ruangan, $currentLokasiFilter) === 0)
                        || ($l->kode_ruangan && strcasecmp("{$l->kode_ruangan} {$l->nama}", $currentLokasiFilter) === 0);
                });
                if ($matchedFilter) {
                    $selectedFilterLabel = $matchedFilter->nama;
                    $selectedFilterKode = $matchedFilter->kode_ruangan ?? '';
                } else {
                    $selectedFilterLabel = $currentLokasiFilter;
                }
            }
        @endphp
        <div
            class="relative"
            x-data="{
                open: false,
                search: '',
                selected: @js($currentLokasiFilter),
                selectedLabel: @js($selectedFilterLabel),
                selectedKode: @js($selectedFilterKode),
                get filtered() {
                    if (!this.search.trim()) return window.__MASTER_LOKASIS__ || [];
                    const q = this.search.toLowerCase().trim();
                    return (window.__MASTER_LOKASIS__ || []).filter(l =>
                        (l.kode && l.kode.toLowerCase().includes(q)) ||
                        (l.nama && l.nama.toLowerCase().includes(q)) ||
                        (l.label && l.label.toLowerCase().includes(q))
                    );
                },
                select(opt) {
                    this.selected = opt.label;
                    this.selectedLabel = opt.nama;
                    this.selectedKode = opt.kode;
                    this.open = false;
                    this.$nextTick(() => {
                        this.$refs.hiddenLokasiFilter.closest('form').submit();
                    });
                },
                clear() {
                    this.selected = '';
                    this.selectedLabel = 'Semua Lokasi';
                    this.selectedKode = '';
                    this.open = false;
                    this.$nextTick(() => {
                        this.$refs.hiddenLokasiFilter.closest('form').submit();
                    });
                }
            }"
            @click.outside="open = false"
            @keydown.escape="open = false"
        >
            <input type="hidden" name="lokasi" x-ref="hiddenLokasiFilter" :value="selected">

            {{-- Filter Trigger Button --}}
            <button
                type="button"
                @click="open = !open; if(open) { $nextTick(() => $refs.filterSearchInput.focus()); }"
                class="inline-flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs transition cursor-pointer border shadow-2xs font-medium"
                :class="selected
                    ? 'bg-[#EAF2ED] border-[#163828] text-[#163828] ring-1 ring-[#163828]/10'
                    : 'bg-[#F7FAF8] hover:bg-white text-[#132219] border-[#E3ECE6] hover:border-[#163828]'"
                title="Filter Lokasi berdasarkan Kode / Nama Ruangan"
            >
                <div class="flex items-center gap-1.5 min-w-0 max-w-[210px] truncate">
                    <svg class="w-3.5 h-3.5 shrink-0" :class="selected ? 'text-[#163828]' : 'text-[#8D9D95]'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <template x-if="selected && selectedKode">
                        <span class="font-mono text-[10px] font-bold px-1.5 py-0.2 rounded bg-white text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="selectedKode"></span>
                    </template>
                    <span class="truncate" x-text="selectedLabel"></span>
                </div>

                <div class="flex items-center gap-1 shrink-0">
                    <template x-if="selected">
                        <span
                            @click.stop="clear()"
                            class="p-0.5 rounded text-[#163828] hover:text-[#9C322C] hover:bg-[#FDF0EE] transition cursor-pointer"
                            title="Hapus filter lokasi"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                    </template>
                    <svg class="w-3.5 h-3.5 text-[#8D9D95] transition-transform duration-200" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </button>

            {{-- Floating Dropdown Panel --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute left-0 top-full mt-1.5 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-[#D5E5DA] p-2.5 z-50 text-xs"
                style="display: none;"
            >
                {{-- Search Box --}}
                <div class="relative mb-2">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#8D9D95]">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        type="text"
                        x-ref="filterSearchInput"
                        x-model="search"
                        placeholder="Cari kode (A1, B1..) atau nama ruangan..."
                        class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                        @keydown.escape="open = false"
                        @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                    >
                    <button
                        type="button"
                        x-show="search"
                        @click="search = ''; $refs.filterSearchInput.focus()"
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-[#8D9D95] hover:text-[#132219] cursor-pointer"
                    >
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Option List --}}
                <div class="max-h-60 overflow-y-auto space-y-0.5 divide-y divide-[#E3ECE6]/40" style="max-height: 220px; overflow-y: auto; overscroll-behavior: contain;">
                    {{-- Opsi Semua Lokasi (Reset) --}}
                    <button
                        type="button"
                        @click="clear()"
                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-left transition hover:bg-[#F2F6F3] cursor-pointer"
                        :class="!selected ? 'bg-[#EAF2ED] font-semibold text-[#163828]' : 'text-[#62756A]'"
                    >
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full border border-current"></span>
                            <span>Semua Lokasi (Reset)</span>
                        </span>
                        <span x-show="!selected" class="text-[#163828] font-bold">✓</span>
                    </button>

                    <template x-for="opt in filtered" :key="opt.label">
                        <button
                            type="button"
                            @click="select(opt)"
                            class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition hover:bg-[#F2F6F3] cursor-pointer group"
                            :class="(selected === opt.label || selected === opt.nama || selected === opt.kode) ? 'bg-[#EAF2ED] text-[#163828] font-semibold' : 'text-[#132219]'"
                        >
                            <div class="flex items-center gap-2 min-w-0 pr-2">
                                <template x-if="opt.kode">
                                    <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-white text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="opt.kode"></span>
                                </template>
                                <span class="truncate text-xs" x-text="opt.nama"></span>
                            </div>
                            <span x-show="selected === opt.label || selected === opt.nama || selected === opt.kode" class="text-[#163828] font-bold shrink-0 ml-1">✓</span>
                        </button>
                    </template>

                    <div x-show="filtered.length === 0" class="py-6 text-center text-[#8D9D95]">
                        <p class="text-xs">Tidak ada lokasi yang cocok</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER PENANGGUNG JAWAB --}}
        <div>
            <select
                name="penanggungjawab"
                onchange="this.form.submit()"
                class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 pl-3 pr-9 transition cursor-pointer"
            >
                <option value="">Penanggung Jawab</option>
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
                onchange="this.form.submit()"
                class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 pl-3 pr-9 transition cursor-pointer"
            >
                <option value="">Kondisi</option>
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
        <a
            href="{{ route('bmns.index') }}"
            class="inline-flex items-center px-3 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-lg text-xs font-medium transition"
        >
            Reset
        </a>
    </form>
</div>
