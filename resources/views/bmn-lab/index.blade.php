@extends('layouts.app')

@section('title', 'Daftar BMN Laboratorium — SIBOB')
@section('page-title', 'Daftar BMN Lab')

@section('content')
<div class="space-y-6">

    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">Sub-Kategori Pengujian</div>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Daftar BMN Laboratorium</h1>
            <p class="text-xs text-[#62756A] mt-0.5">Pemantauan instrumen pengujian dan inventaris peralatan laboratorium BBPOM di Banjarbaru.</p>
        </div>

        <a
            href="{{ route('bmns-lab.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition shrink-0"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah BMN Lab</span>
        </a>
    </div>

    {{-- MASTER LOKASI MEMORY CACHE UNTUK SEARCHABLE DROPDOWNS --}}
    @php
        $masterLokasiOptions = ($lokasis ?? collect())->map(function($l) {
            return [
                'id' => $l->id,
                'kode' => $l->kode_ruangan ?? '',
                'nama' => $l->nama,
                'label' => $l->format_kombinasi,
            ];
        })->values();

        if (!empty($lokasiFilters)) {
            $existingLabels = $masterLokasiOptions->pluck('label')->map(fn($n) => strtolower(trim($n)))->toArray();
            $existingNames = $masterLokasiOptions->pluck('nama')->map(fn($n) => strtolower(trim($n)))->toArray();
            $existingCodes = $masterLokasiOptions->pluck('kode')->filter()->map(fn($n) => strtolower(trim($n)))->toArray();

            foreach ($lokasiFilters as $extraLok) {
                $extraTrim = strtolower(trim((string)$extraLok));
                if ($extraTrim !== '' && !in_array($extraTrim, $existingLabels) && !in_array($extraTrim, $existingNames) && !in_array($extraTrim, $existingCodes)) {
                    $masterLokasiOptions->push([
                        'id' => null,
                        'kode' => '',
                        'nama' => (string)$extraLok,
                        'label' => (string)$extraLok,
                    ]);
                }
            }
        }
    @endphp
    <script>
        window.__MASTER_LOKASIS__ = @json($masterLokasiOptions);

        window.bmnLocationCell = function(config) {
            return {
                open: false,
                openUpward: false,
                search: '',
                selected: config.selected || '',
                selectedLabel: config.selectedLabel || '',
                selectedKode: config.selectedKode || '',
                selectedNama: config.selectedNama || 'Pilih Lokasi...',
                initialSelected: config.selected || '',
                initialLabel: config.selectedLabel || '',
                initialKode: config.selectedKode || '',
                initialNama: config.selectedNama || 'Pilih Lokasi...',
                itemName: config.itemName || '',
                get isChanged() {
                    return this.selected !== this.initialSelected;
                },
                toggle(triggerEl) {
                    this.open = !this.open;
                    if (this.open) {
                        if (triggerEl) {
                            const rect = triggerEl.getBoundingClientRect();
                            const spaceBelow = window.innerHeight - rect.bottom;
                            this.openUpward = (spaceBelow < 250 && rect.top > spaceBelow);
                        }
                        this.$nextTick(() => {
                            if (this.$refs.rowSearchInput) {
                                this.$refs.rowSearchInput.focus();
                            }
                        });
                    }
                },
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
                    if (!opt) return;
                    this.selected = opt.label;
                    this.selectedLabel = opt.label;
                    this.selectedKode = opt.kode;
                    this.selectedNama = opt.nama;
                    this.open = false;
                    if (this.$refs.hiddenInput) {
                        this.$refs.hiddenInput.value = opt.label;
                    }
                },
                cancel() {
                    this.selected = this.initialSelected;
                    this.selectedLabel = this.initialLabel;
                    this.selectedKode = this.initialKode;
                    this.selectedNama = this.initialNama;
                    this.open = false;
                    this.search = '';
                    if (this.$refs.hiddenInput) {
                        this.$refs.hiddenInput.value = this.initialSelected;
                    }
                },
                confirmSave() {
                    if (!this.isChanged) return;
                    const msg = 'Apakah Anda yakin ingin mengubah lokasi barang "' + this.itemName + '" ke "' + this.selectedLabel + '"?';
                    if (!confirm(msg)) {
                        return;
                    }
                    if (this.$refs.hiddenInput) {
                        this.$refs.hiddenInput.value = this.selected;
                    }
                    if (this.$refs.form) {
                        this.$refs.form.submit();
                    }
                }
            };
        };
    </script>

    {{-- FILTER TOOLBAR --}}
    <div class="bg-white p-3.5 rounded-xl border border-[#E3ECE6] shadow-2xs">
        <form method="GET" action="{{ route('bmns-lab.index') }}" class="flex flex-wrap items-center gap-2.5">
            {{-- SEARCH --}}
            <div class="relative flex-1 min-w-[220px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#8D9D95]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari kode atau nama instrumen lab..."
                    class="w-full pl-9 pr-3 py-2 bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] placeholder-[#8D9D95] transition"
                >
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

            {{-- CARI BUTTON --}}
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-lg text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
            >
                <span>Cari</span>
            </button>

            {{-- RESET --}}
            <a
                href="{{ route('bmns-lab.index') }}"
                class="inline-flex items-center px-3 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-lg text-xs font-medium transition"
            >
                Reset
            </a>
        </form>
    </div>

    @php
        $bmnsLab = $bmnsLab ?? $items ?? collect();
    @endphp

    {{-- TABEL DATA LAB --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Kode Barang</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">NUP</th>
                        <th class="py-3.5 px-4">Nama Alat Lab</th>
                        <th class="py-3.5 px-4">Merek</th>
                        <th class="py-3.5 px-4 text-center">Tahun</th>
                        <th class="py-3.5 px-4 text-right">Nilai Perolehan</th>
                        <th class="py-3.5 px-4">Lokasi</th>
                        <th class="py-3.5 px-4 text-center w-14">QR</th>
                        <th class="py-3.5 px-4">Penanggung Jawab</th>
                        <th class="py-3.5 px-4">Kondisi</th>
                        <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6]/70 text-xs">
                    @forelse($bmnsLab as $index => $item)
                        @php
                            $kondisi = $item->kondisi ?: 'Baik';
                            $kondisiLower = strtolower($kondisi);
                            $kondisiStyle = match($kondisiLower) {
                                'baik' => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                                'rusak sedang' => 'bg-[#FFF4E5] text-[#B25E00] border-[#FFE2BF]',
                                'rusak ringan' => 'bg-[#FEF7E0] text-[#B06000] border-[#FEEFC3]',
                                'rusak', 'rusak berat' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                                default => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]',
                            };
                            $dotColor = match($kondisiLower) {
                                'baik' => 'bg-[#137333]',
                                'rusak sedang', 'rusak ringan' => 'bg-[#B06000]',
                                'rusak', 'rusak berat' => 'bg-[#9C322C]',
                                default => 'bg-[#4A5D52]',
                            };
                        @endphp
                        <tr class="hover:bg-[#F9FCFA] transition">
                            {{-- NO --}}
                            <td class="py-3.5 px-4 text-center text-[#62756A] font-mono text-[11px]">
                                {{ $bmnsLab->firstItem() + $index }}
                            </td>

                            {{-- KODE BARANG --}}
                            <td class="py-3.5 px-4 font-mono text-[11px] whitespace-nowrap">
                                <span class="font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                    {{ $item->kode_barang ?: '—' }}
                                </span>
                            </td>

                            {{-- NUP --}}
                            <td class="py-3.5 px-4 text-center font-mono text-[11px] whitespace-nowrap">
                                <span class="font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                    {{ $item->nup ?? '—' }}
                                </span>
                            </td>

                            {{-- NAMA BARANG --}}
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-[#132219]">
                                    {{ $item->nama_barang }}
                                </div>
                                @if($item->is_dikembalikan)
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#FFF4E5] text-[#B25E00] border border-[#FFE2BF]" title="Aset telah dikembalikan ke Pengelola BMN">
                                            <svg class="w-3 h-3 text-[#B25E00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                            <span>Barang Pengembalian</span>
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- MEREK --}}
                            <td class="py-3.5 px-4 text-[#4D6357]">
                                {{ $item->merek ?: '—' }}
                            </td>

                            {{-- TAHUN PEROLEHAN --}}
                            <td class="py-3.5 px-4 text-center font-mono text-[11px] text-[#4D6357]">
                                {{ $item->tahun_perolehan ?: '—' }}
                            </td>

                            {{-- NILAI PEROLEHAN --}}
                            <td class="py-3.5 px-4 text-right font-mono text-[11px] font-medium text-[#132219]">
                                {{ $item->nilai_perolehan !== null ? 'Rp ' . number_format($item->nilai_perolehan, 0, ',', '.') : '—' }}
                            </td>

                            {{-- LOKASI (SEARCHABLE DROPDOWN LANGSUNG DI TABEL) --}}
                            @php
                                $rowKode = '';
                                $rowNama = $item->lokasi;
                                if ($item->lokasi) {
                                    $matchedLok = ($lokasis ?? collect())->first(fn ($l) => $l->nama === $item->lokasi || $l->format_kombinasi === $item->lokasi || ($l->kode_ruangan && strcasecmp("{$l->kode_ruangan} {$l->nama}", $item->lokasi) === 0));
                                    if ($matchedLok) {
                                        $rowKode = $matchedLok->kode_ruangan ?? '';
                                        $rowNama = $matchedLok->nama;
                                    }
                                }
                                $rowLabel = $rowKode ? "{$rowKode} - {$rowNama}" : ($rowNama ?: 'Pilih Lokasi...');
                            @endphp
                            <td class="py-2.5 px-3">
                                <form
                                    x-ref="form"
                                    method="POST"
                                    action="{{ route('bmns-lab.update-lokasi', $item) }}"
                                    class="inline-block w-full min-w-[210px] max-w-[320px]"
                                    x-data="bmnLocationCell({
                                        selected: @js($item->lokasi ?: ''),
                                        selectedLabel: @js($rowLabel),
                                        selectedKode: @js($rowKode),
                                        selectedNama: @js($rowNama ?: 'Pilih Lokasi...'),
                                        itemName: @js($item->nama_barang)
                                    })"
                                    onclick="event.stopPropagation()"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="lokasi" x-ref="hiddenInput" :value="selected">

                                    <div class="flex items-center gap-1.5 w-full">
                                        <div class="relative flex-1 min-w-0" @click.outside="open = false" @keydown.escape="open = false">
                                            {{-- Trigger Button --}}
                                            <button
                                                type="button"
                                                @click="toggle($el)"
                                                class="w-full flex items-center justify-between gap-1.5 px-2.5 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] hover:bg-white hover:border-[#163828] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/15 text-xs text-[#132219] transition cursor-pointer shadow-2xs font-medium text-left"
                                                :class="[
                                                    open ? 'border-[#163828] ring-2 ring-[#163828]/15 bg-white' : '',
                                                    isChanged ? 'border-amber-400 bg-amber-50/40 text-amber-950 ring-1 ring-amber-400/50' : ''
                                                ]"
                                                title="Ubah Lokasi Ruangan (Klik untuk cari & pilih)"
                                            >
                                            <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                                                <svg class="w-3.5 h-3.5 text-[#8D9D95] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                </svg>
                                                <template x-if="selectedKode">
                                                    <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#EAF2ED] text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="selectedKode"></span>
                                                </template>
                                                <span class="truncate text-xs text-[#132219]" x-text="selectedNama"></span>
                                            </div>
                                            <svg class="w-3.5 h-3.5 text-[#8D9D95] shrink-0 transition-transform duration-150" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        {{-- Floating Searchable Panel --}}
                                        <div
                                            x-show="open"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute left-0 w-72 sm:w-80 bg-white rounded-xl shadow-xl border border-[#D5E5DA] p-2 z-50 text-xs"
                                            :class="openUpward ? 'bottom-full mb-1' : 'top-full mt-1'"
                                            style="display: none;"
                                        >
                                            {{-- Search Input --}}
                                            <div class="relative mb-1.5">
                                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#8D9D95]">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                </span>
                                                <input
                                                    type="text"
                                                    x-ref="rowSearchInput"
                                                    x-model="search"
                                                    placeholder="Ketik kode (A1, B1..) atau nama ruangan..."
                                                    class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                                    @keydown.escape="open = false"
                                                    @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                                                >
                                                <button
                                                    type="button"
                                                    x-show="search"
                                                    @click="search = ''; $refs.rowSearchInput.focus()"
                                                    class="absolute inset-y-0 right-0 pr-2 flex items-center text-[#8D9D95] hover:text-[#132219] cursor-pointer"
                                                >
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            {{-- Option List --}}
                                            <div class="max-h-36 overflow-y-auto space-y-0.5 divide-y divide-[#E3ECE6]/40" style="max-height: 180px; overflow-y: auto; overscroll-behavior: contain;">
                                                <template x-for="opt in filtered" :key="opt.label">
                                                    <button
                                                        type="button"
                                                        @click="select(opt)"
                                                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition hover:bg-[#EAF2ED] cursor-pointer group"
                                                        :class="(selectedNama === opt.nama || selected === opt.label) ? 'bg-[#EAF2ED] text-[#163828] font-semibold' : 'text-[#132219]'"
                                                    >
                                                        <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                                                            <template x-if="opt.kode">
                                                                <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-white text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="opt.kode"></span>
                                                            </template>
                                                            <span class="truncate text-xs" x-text="opt.nama"></span>
                                                        </div>
                                                        <span x-show="selectedNama === opt.nama || selected === opt.label" class="text-[#163828] font-bold shrink-0 ml-1">✓</span>
                                                    </button>
                                                </template>
                                                <div x-show="filtered.length === 0" class="py-4 text-center text-[#8D9D95]">
                                                    <p class="text-xs">Tidak ada ruangan cocok</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tombol Konfirmasi Centang & Batal (Hanya muncul jika lokasi diubah) --}}
                                    <template x-if="isChanged">
                                        <div class="flex items-center gap-1 shrink-0">
                                            <button
                                                type="button"
                                                @click="confirmSave()"
                                                class="w-7 h-7 rounded-lg bg-[#0E5954] hover:bg-[#163828] text-white flex items-center justify-center shadow-xs transition cursor-pointer"
                                                title="Konfirmasi & Simpan Perubahan Lokasi"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                            <button
                                                type="button"
                                                @click="cancel()"
                                                class="w-7 h-7 rounded-lg bg-[#F7FAF8] hover:bg-[#EAF2ED] text-[#62756A] hover:text-[#132219] border border-[#D5E5DA] flex items-center justify-center shadow-2xs transition cursor-pointer"
                                                title="Batal Ubah Lokasi"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </form>
                            </td>

                            {{-- QR --}}
                            <td class="py-3.5 px-4 text-center">
                                <button
                                    type="button"
                                    onclick="openQrModal({
                                        title: 'QR Code BMN Laboratorium',
                                        code: '{{ $item->kode_barang }}@if($item->nup)-NUP.{{ $item->nup }}@endif',
                                        name: '{{ addslashes($item->nama_barang) }}',
                                        lokasi: '{{ addslashes($item->lokasi ?: '—') }}',
                                        pj: '{{ addslashes($item->penanggungjawab ?: '—') }}'
                                    })"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#EAF2ED] text-[#163828] hover:bg-[#163828] hover:text-white transition shadow-2xs cursor-pointer group"
                                    title="Lihat & Cetak QR Code"
                                >
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                                        <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                                        <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                                        <path d="M7 7h.01M17 7h.01M7 17h.01M14 14h3v3h-3zM20 14v3M14 20h3M20 20h.01"></path>
                                    </svg>
                                </button>
                            </td>

                            {{-- PENANGGUNG JAWAB --}}
                            <td class="py-3.5 px-4 font-medium text-[#132219]">
                                <div>{{ $item->penanggungjawab ?: '—' }}</div>
                                @if($item->is_dikembalikan)
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#B25E00] bg-[#FFF4E5] px-1.5 py-0.5 rounded border border-[#FFE2BF]">
                                            <svg class="w-2.5 h-2.5 text-[#B25E00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                            <span>Barang Pengembalian</span>
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- KONDISI --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium border {{ $kondisiStyle }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                    <span>{{ $kondisi }}</span>
                                </span>
                            </td>

                            {{-- AKSI --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('bmns-lab.edit', array_merge(['bmnLab' => $item->id], request()->query())) }}"
                                        class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                        title="Edit Data"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    {{-- HAPUS --}}
                                    <form
                                        method="POST"
                                        action="{{ route('bmns-lab.destroy', $item) }}"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data BMN Lab ini?')"
                                        class="inline"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 text-[#9C322C]/70 hover:text-[#9C322C] hover:bg-[#FDF0EE] rounded-lg transition cursor-pointer"
                                            title="Hapus Data"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="py-12 text-center">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-[#EAF2ED] text-[#163828] flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-[#132219]">Belum Ada Data BMN Lab</div>
                                    <p class="text-xs text-[#62756A]">Belum ada alat laboratorium yang terdaftar.</p>
                                    <a
                                        href="{{ route('bmns-lab.create') }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] text-white rounded-lg text-xs font-semibold hover:bg-[#1E4834] transition shadow-2xs"
                                    >
                                        + Tambah BMN Lab Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- FOOTER / PAGINATION --}}
        <div class="px-5 pb-5">
            <x-table-footer :paginator="$bmnsLab" />
        </div>
    </div>

</div>

<x-qr-modal />

@endsection
