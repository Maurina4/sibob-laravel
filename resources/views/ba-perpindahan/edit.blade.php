@extends('layouts.app')

@section('title', 'Ubah BA Perpindahan — ' . ($baPerpindahan->nomor_ba ?: $baPerpindahan->id))
@section('page-title', 'Ubah BA Perpindahan')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('ba-perpindahan.index', session('ba_perpindahan_edit_return_query.'.$baPerpindahan->id, [])) }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar BA Perpindahan</span>
        </a>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Ubah BA Perpindahan</h1>
            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                {{ $baPerpindahan->nomor_ba ?: 'BA-MUT/' . $baPerpindahan->id }}
            </span>
        </div>
        <p class="text-xs text-[#62756A] mt-0.5">Perbarui rincian permohonan mutasi aset BMN.</p>
    </div>

    {{-- ERROR VALIDASI --}}
    @if ($errors->any())
        <div class="p-4 rounded-xl border border-[#F8D4D0] bg-[#FDF0EE] text-[#9C322C] text-xs">
            <strong class="font-semibold block mb-1">Periksa kembali data yang dimasukkan:</strong>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM CARD --}}
    <form method="POST" action="{{ route('ba-perpindahan.update', $baPerpindahan) }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Rincian Pemindahan Barang</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- NOMOR BA --}}
            <div>
                <x-input-label for="nomor_ba" value="Nomor BA Perpindahan" />
                <x-text-input
                    id="nomor_ba"
                    name="nomor_ba"
                    type="text"
                    class="w-full font-mono text-xs"
                    :value="old('nomor_ba', $baPerpindahan->nomor_ba)"
                />
            </div>

            {{-- TANGGAL --}}
            <div>
                <x-input-label for="tanggal" value="Tanggal Pemindahan *" />
                <x-text-input
                    id="tanggal"
                    name="tanggal"
                    type="date"
                    class="w-full text-xs"
                    :value="old('tanggal', optional($baPerpindahan->tanggal)->format('Y-m-d'))"
                    max="{{ date('Y-m-d') }}"
                    required
                />
                <x-input-error :messages="$errors->get('tanggal')" class="mt-1" />
            </div>
        </div>

        {{-- KODE & NUP BMN (RELASI MASTER BMN) --}}
        @php
            $bmnsJson = $bmns->map(function ($b) {
                $nupStr = $b->nup ? "-NUP.{$b->nup}" : '';
                $label = "{$b->kode_barang}{$nupStr} — {$b->nama_barang} (Lokasi: " . ($b->lokasi ?: '—') . " | Penanggung Jawab: " . ($b->penanggungjawab ?: '—') . ")";
                return [
                    'id' => (string)$b->id,
                    'kode_barang' => (string)($b->kode_barang ?? ''),
                    'nup' => (string)($b->nup ?? ''),
                    'nama_barang' => $b->nama_barang ?? '',
                    'lokasi' => $b->lokasi ?? '',
                    'penanggungjawab' => $b->penanggungjawab ?? '',
                    'penanggung_jawab_id' => (string)($b->penanggung_jawab_id ?? ''),
                    'label' => $label,
                ];
            })->values();

            $pegawaisJson = $pegawais->map(function ($p) {
                return [
                    'id' => (string)$p->id,
                    'nama' => (string)$p->nama,
                    'nip' => (string)($p->nip ?? ''),
                    'label' => $p->nip ? "{$p->nama} ({$p->nip})" : (string)$p->nama,
                ];
            })->values();

            $initialBmnId = (string)old('bmn_id', $baPerpindahan->bmn_id ?? '');
            $selectedBmn = $initialBmnId ? $bmns->firstWhere('id', $initialBmnId) : null;
            $initialBmnLabel = '';
            if ($selectedBmn) {
                $nupStr = $selectedBmn->nup ? "-NUP.{$selectedBmn->nup}" : '';
                $initialBmnLabel = "{$selectedBmn->kode_barang}{$nupStr} — {$selectedBmn->nama_barang} (Lokasi: " . ($selectedBmn->lokasi ?: '—') . " | Penanggung Jawab: " . ($selectedBmn->penanggungjawab ?: '—') . ")";
            }
        @endphp

        <div>
            <x-input-label for="bmn_id" value="Kode & NUP BMN (Pilih Barang Dari Daftar BMN)" />
            
            <div
                x-data="baBmnSearchableSelect({
                    initialId: '{{ $initialBmnId }}',
                    initialLabel: '{{ addslashes($initialBmnLabel) }}'
                })"
                class="relative w-full"
            >
                {{-- Hidden input for form submission --}}
                <input
                    type="hidden"
                    name="bmn_id"
                    id="bmn_id"
                    :value="selectedId"
                />

                {{-- Trigger Button --}}
                <button
                    type="button"
                    x-ref="triggerBtn"
                    @click="toggleOpen()"
                    class="w-full flex items-center justify-between text-left text-xs px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] bg-white hover:border-[#163828] focus:outline-none focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 transition cursor-pointer"
                    :class="open ? 'border-[#163828] ring-2 ring-[#163828]/20' : ''"
                >
                    <div class="flex items-center gap-1.5 truncate pr-2">
                        <span x-show="selectedId" x-text="selectedLabel" class="font-medium text-[#132219] truncate"></span>
                        <span x-show="!selectedId" class="text-[#8D9D95]">-- Pilih Kode & NUP BMN --</span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <span
                            x-show="selectedId"
                            @click.stop="clearSelection()"
                            class="p-0.5 text-[#8D9D95] hover:text-[#9C322C] rounded transition cursor-pointer"
                            title="Kosongkan pilihan"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                        <svg class="w-4 h-4 text-[#8D9D95] transition-transform duration-200" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>

                {{-- Floating Dropdown Panel with Search Input --}}
                <div
                    x-show="open"
                    @click.outside="closeDropdown()"
                    @scroll.window="if (open) updatePosition()"
                    @resize.window="if (open) updatePosition()"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    style="display: none;"
                    class="fixed z-[9999] bg-white border border-[#D5E5DA] rounded-xl shadow-2xl overflow-hidden flex flex-col"
                    :style="panelStyle"
                >
                    {{-- Search Input inside Dropdown --}}
                    <div class="p-2.5 border-b border-[#E3ECE6] bg-[#F7FAF8] shrink-0">
                        <div class="relative">
                            <input
                                x-ref="searchInput"
                                x-model="search"
                                type="text"
                                placeholder="Ketik Kode, NUP, Nama Barang, Lokasi, atau Penanggung Jawab..."
                                class="w-full text-xs pl-8 pr-7 py-2 rounded-lg border border-[#E3ECE6] text-[#132219] bg-white focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                @keydown.escape="closeDropdown()"
                                @keydown.enter.prevent="if (filteredItems.length > 0) selectItem(filteredItems[0])"
                            />
                            <svg class="w-3.5 h-3.5 text-[#8D9D95] absolute left-2.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <button
                                type="button"
                                x-show="search"
                                @click="search = ''; $refs.searchInput.focus()"
                                class="absolute right-2 top-2.5 text-[#8D9D95] hover:text-[#132219]"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Options List --}}
                    <div class="flex-1 max-h-64 overflow-y-auto divide-y divide-[#E3ECE6]/50 overscroll-contain">
                        <template x-for="item in filteredItems" :key="item.id">
                            <div
                                @click="selectItem(item)"
                                class="px-3.5 py-2.5 text-xs cursor-pointer hover:bg-[#EAF2ED] transition flex items-center justify-between gap-2"
                                :class="String(selectedId) === String(item.id) ? 'bg-[#EAF2ED]/70 font-semibold text-[#163828]' : 'text-[#132219]'"
                            >
                                <div class="min-w-0">
                                    <div class="font-medium text-[#132219] truncate">
                                        <span class="font-mono text-[#163828]" x-text="item.kode_barang + (item.nup ? '-NUP.' + item.nup : '')"></span>
                                        <span class="text-[#62756A] mx-1">—</span>
                                        <span x-text="item.nama_barang"></span>
                                    </div>
                                    <div class="text-[11px] text-[#62756A] flex items-center gap-2 flex-wrap mt-0.5">
                                        <span>Lokasi: <span class="text-[#132219]" x-text="item.lokasi || '—'"></span></span>
                                        <span>•</span>
                                        <span>Penanggung Jawab: <span class="text-[#132219]" x-text="item.penanggungjawab || '—'"></span></span>
                                    </div>
                                </div>
                                <svg x-show="String(selectedId) === String(item.id)" class="w-4 h-4 text-[#163828] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </template>
                        <div x-show="filteredItems.length === 0" class="p-4 text-center text-xs text-[#62756A]">
                            Tidak ditemukan barang dengan kata kunci "<span class="font-semibold" x-text="search"></span>".
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-[11px] text-[#62756A] mt-1">Pilih Kode & NUP dari daftar BMN untuk mengisi Nama Barang dan Lokasi Asal secara otomatis.</p>
        </div>

        {{-- NAMA BARANG --}}
        <div>
            <x-input-label for="nama_barang" value="Nama Barang BMN *" />
            <x-text-input
                id="nama_barang"
                name="nama_barang"
                type="text"
                class="w-full text-xs font-medium bg-[#F7FAF8] cursor-not-allowed text-[#132219] focus:ring-0 focus:border-[#E3ECE6]"
                :value="old('nama_barang', $baPerpindahan->nama_barang)"
                placeholder="Otomatis terisi saat memilih Kode & NUP BMN di atas"
                readonly
                required
            />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- DARI --}}
            <div>
                <x-input-label for="dari" value="Lokasi Asal (Dari) *" />
                <x-text-input
                    id="dari"
                    name="dari"
                    type="text"
                    class="w-full text-xs bg-[#F7FAF8] cursor-not-allowed text-[#132219] focus:ring-0 focus:border-[#E3ECE6]"
                    :value="old('dari', $baPerpindahan->dari)"
                    placeholder="Otomatis terisi saat memilih Kode & NUP BMN di atas"
                    readonly
                    required
                />
            </div>

                        {{-- KE (LOKASI TUJUAN) --}}
            @php
                $initKeVal = old('ke', $baPerpindahan->ke);
                $initKeId = old('lokasi_tujuan_id', $baPerpindahan->lokasi_tujuan_id);
                $initKeKode = '';
                $initKeNama = '';
                if ($initKeVal || $initKeId) {
                    $foundKe = $lokasis->first(fn($l) =>
                        ($initKeId && (string)$l->id === (string)$initKeId)
                        || ($initKeVal && strtolower($l->nama) === strtolower($initKeVal))
                        || ($initKeVal && strtolower($l->format_kombinasi) === strtolower($initKeVal))
                    );
                    if ($foundKe) {
                        $initKeId = (string)$foundKe->id;
                        $initKeKode = $foundKe->kode_ruangan ?? '';
                        $initKeNama = $foundKe->nama;
                        $initKeVal = $foundKe->nama;
                    } else {
                        $initKeNama = $initKeVal;
                    }
                }
            @endphp
            <div>
                <x-input-label for="ke" value="Lokasi Tujuan (Ke) *" />
                <div
                    x-data="{
                        open: false,
                        search: '',
                        selected: @js($initKeVal),
                        selectedId: @js($initKeId),
                        selectedKode: @js($initKeKode),
                        selectedNama: @js($initKeNama ?: 'Pilih atau cari ruangan tujuan...'),
                        options: @js(($lokasis ?? collect())->map(fn($l) => [
                            'id' => (string)$l->id,
                            'nama' => $l->nama,
                            'kode' => $l->kode_ruangan ?? '',
                            'label' => $l->format_kombinasi ?: $l->nama,
                        ])->values()),
                        get filtered() {
                            if (!this.search.trim()) return this.options;
                            const q = this.search.toLowerCase().trim();
                            return this.options.filter(o =>
                                (o.kode && o.kode.toLowerCase().includes(q)) ||
                                (o.nama && o.nama.toLowerCase().includes(q)) ||
                                (o.label && o.label.toLowerCase().includes(q))
                            );
                        },
                        select(opt) {
                            this.selected = opt.nama;
                            this.selectedId = opt.id;
                            this.selectedKode = opt.kode;
                            this.selectedNama = opt.nama;
                            this.open = false;
                            this.search = '';
                        },
                        reset() {
                            this.selected = '';
                            this.selectedId = '';
                            this.selectedKode = '';
                            this.selectedNama = 'Pilih atau cari ruangan tujuan...';
                            this.open = false;
                            this.search = '';
                        }
                    }"
                    @click.outside="open = false"
                    @keydown.escape="open = false"
                    class="relative mt-1"
                >
                    <input type="hidden" id="ke" name="ke" :value="selected" required>
                    <input type="hidden" id="lokasi_tujuan_id" name="lokasi_tujuan_id" :value="selectedId">

                    {{-- Trigger Button --}}
                    <button
                        type="button"
                        @click="open = !open; if(open) { $nextTick(() => $refs.searchKeInput.focus()); }"
                        class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg border border-[#E3ECE6] bg-white hover:border-[#163828] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 text-xs text-[#132219] transition cursor-pointer shadow-2xs font-medium text-left"
                        :class="open ? 'border-[#163828] ring-2 ring-[#163828]/20' : ''"
                    >
                        <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                            <svg class="w-3.5 h-3.5 text-[#8D9D95] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <template x-if="selectedKode">
                                <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#EAF2ED] text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="selectedKode"></span>
                            </template>
                            <span class="truncate" :class="selected ? 'text-[#132219]' : 'text-[#8D9D95]'" x-text="selectedNama"></span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <template x-if="selected">
                                <span
                                    @click.stop="reset()"
                                    class="p-0.5 text-[#8D9D95] hover:text-[#B22222] transition cursor-pointer"
                                    title="Hapus pilihan"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </span>
                            </template>
                            <svg class="w-3.5 h-3.5 text-[#8D9D95] transition-transform duration-150" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
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
                        class="absolute left-0 top-full mt-1 w-full bg-white rounded-xl shadow-xl border border-[#D5E5DA] p-2 z-50 text-xs"
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
                                x-ref="searchKeInput"
                                x-model="search"
                                placeholder="Ketik kode (A1, B1..) atau nama ruangan..."
                                class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                @keydown.escape="open = false"
                                @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                            >
                            <button
                                type="button"
                                x-show="search"
                                @click="search = ''; $refs.searchKeInput.focus()"
                                class="absolute inset-y-0 right-0 pr-2 flex items-center text-[#8D9D95] hover:text-[#132219] cursor-pointer"
                            >
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Option List --}}
                        <div class="max-h-56 overflow-y-auto space-y-0.5 divide-y divide-[#E3ECE6]/40" style="max-height: 220px; overflow-y: auto; overscroll-behavior: contain;">
                            <template x-for="opt in filtered" :key="opt.label">
                                <button
                                    type="button"
                                    @click="select(opt)"
                                    class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-left transition hover:bg-[#EAF2ED] cursor-pointer group"
                                    :class="(selected === opt.nama || selected === opt.label) ? 'bg-[#EAF2ED] text-[#163828] font-semibold' : 'text-[#132219]'"
                                >
                                    <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                                        <template x-if="opt.kode">
                                            <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-white text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="opt.kode"></span>
                                        </template>
                                        <span class="truncate text-xs" x-text="opt.nama"></span>
                                    </div>
                                    <span x-show="selected === opt.nama || selected === opt.label" class="text-[#163828] font-bold shrink-0 ml-1">✓</span>
                                </button>
                            </template>
                            <div x-show="filtered.length === 0" class="py-4 text-center text-[#8D9D95]">
                                <p class="text-xs">Tidak ada ruangan cocok</p>
                            </div>
                        </div>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('ke')" class="mt-1" />
            </div>
        </div>

        @php
            $userMatch = $pegawais->firstWhere('email', auth()->user()->email) ?? $pegawais->firstWhere('nama', auth()->user()->name);
            $selectedPengajuId = (string)old('pengaju_pegawai_id', old('pegawai_id', $baPerpindahan->pengaju_pegawai_id ?? $baPerpindahan->pegawai_id));
            $foundPengaju = $pegawais->firstWhere('id', $selectedPengajuId);
            $selectedPengajuNama = old('pengaju_nama', $baPerpindahan->pengaju_nama);
            $selectedPengajuNip = $foundPengaju?->nip ?? ($userMatch?->nip ?? '');
        @endphp

        {{-- PEGAWAI PENGAJU --}}
        <div>
            <x-input-label for="pengaju_pegawai_id" value="Pegawai Pengaju (Pemohon Perpindahan) *" />
            <div
                x-data="{
                    open: false,
                    search: '',
                    selectedId: @js($selectedPengajuId),
                    selectedNip: @js($selectedPengajuNip),
                    selectedNama: @js($selectedPengajuNama ?: 'Pilih atau cari pegawai pengaju...'),
                    options: @js(($pegawais ?? collect())->map(fn($p) => [
                        'id' => (string)$p->id,
                        'nama' => $p->nama,
                        'nip' => $p->nip ?? '',
                        'jabatan' => $p->jabatan ?? '',
                        'unit_kerja' => $p->unit_kerja ?? '',
                        'label' => $p->nip ? "{$p->nama} ({$p->nip})" : $p->nama,
                    ])->values()),
                    get filtered() {
                        if (!this.search.trim()) return this.options;
                        const q = this.search.toLowerCase().trim();
                        return this.options.filter(o =>
                            (o.nip && o.nip.toLowerCase().includes(q)) ||
                            (o.nama && o.nama.toLowerCase().includes(q)) ||
                            (o.jabatan && o.jabatan.toLowerCase().includes(q)) ||
                            (o.unit_kerja && o.unit_kerja.toLowerCase().includes(q)) ||
                            (o.label && o.label.toLowerCase().includes(q))
                        );
                    },
                    select(opt) {
                        this.selectedId = opt.id;
                        this.selectedNip = opt.nip;
                        this.selectedNama = opt.nama;
                        this.open = false;
                        this.search = '';
                        $nextTick(() => {
                            if (typeof syncPihakKeduaList === 'function') {
                                syncPihakKeduaList();
                            }
                        });
                    },
                    reset() {
                        this.selectedId = '';
                        this.selectedNip = '';
                        this.selectedNama = 'Pilih atau cari pegawai pengaju...';
                        this.open = false;
                        this.search = '';
                    }
                }"
                @click.outside="open = false"
                @keydown.escape="open = false"
                class="relative mt-1"
            >
                <input type="hidden" id="pengaju_pegawai_id" name="pengaju_pegawai_id" :value="selectedId" required>
                <input type="hidden" id="pengaju_nama" name="pengaju_nama" :value="selectedNama">

                {{-- Trigger Button --}}
                <button
                    type="button"
                    @click="open = !open; if(open) { $nextTick(() => $refs.searchPengajuInput.focus()); }"
                    class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg border border-[#E3ECE6] bg-white hover:border-[#163828] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 text-xs text-[#132219] transition cursor-pointer shadow-2xs font-medium text-left"
                    :class="open ? 'border-[#163828] ring-2 ring-[#163828]/20' : ''"
                >
                    <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                        <svg class="w-3.5 h-3.5 text-[#8D9D95] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <template x-if="selectedNip">
                            <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#EAF2ED] text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="selectedNip"></span>
                        </template>
                        <span class="truncate" :class="selectedId ? 'text-[#132219]' : 'text-[#8D9D95]'" x-text="selectedNama"></span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <template x-if="selectedId">
                            <span
                                @click.stop="reset()"
                                class="p-0.5 text-[#8D9D95] hover:text-[#B22222] transition cursor-pointer"
                                title="Hapus pilihan"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </span>
                        </template>
                        <svg class="w-3.5 h-3.5 text-[#8D9D95] transition-transform duration-150" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
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
                    class="absolute left-0 top-full mt-1 w-full bg-white rounded-xl shadow-xl border border-[#D5E5DA] p-2 z-50 text-xs"
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
                            x-ref="searchPengajuInput"
                            x-model="search"
                            placeholder="Ketik nama atau NIP pegawai pengaju..."
                            class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                            @keydown.escape="open = false"
                            @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                        >
                        <button
                            type="button"
                            x-show="search"
                            @click="search = ''; $refs.searchPengajuInput.focus()"
                            class="absolute inset-y-0 right-0 pr-2 flex items-center text-[#8D9D95] hover:text-[#132219] cursor-pointer"
                        >
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Option List --}}
                    <div class="max-h-56 overflow-y-auto space-y-0.5 divide-y divide-[#E3ECE6]/40" style="max-height: 220px; overflow-y: auto; overscroll-behavior: contain;">
                        <template x-for="opt in filtered" :key="opt.id">
                            <button
                                type="button"
                                @click="select(opt)"
                                class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-left transition hover:bg-[#EAF2ED] cursor-pointer group"
                                :class="selectedId === opt.id ? 'bg-[#EAF2ED] text-[#163828] font-semibold' : 'text-[#132219]'"
                            >
                                <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                                    <template x-if="opt.nip">
                                        <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-white text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="opt.nip"></span>
                                    </template>
                                    <div class="truncate">
                                        <div class="truncate text-xs font-medium" x-text="opt.nama"></div>
                                        <template x-if="opt.unit_kerja || opt.jabatan">
                                            <div class="truncate text-[10px] text-[#62756A]" x-text="opt.unit_kerja || opt.jabatan"></div>
                                        </template>
                                    </div>
                                </div>
                                <span x-show="selectedId === opt.id" class="text-[#163828] font-bold shrink-0 ml-1">✓</span>
                            </button>
                        </template>
                        <div x-show="filtered.length === 0" class="py-4 text-center text-[#8D9D95]">
                            <p class="text-xs">Tidak ada pegawai cocok</p>
                        </div>
                    </div>
                </div>
            </div>
            <x-input-error :messages="$errors->get('pengaju_pegawai_id')" />
        </div>

        {{-- PIHAK PERTAMA & PIHAK KEDUA --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- PIHAK PERTAMA (PENYERAH / PENANGGUNG JAWAB SAAT INI) --}}
            <div>
                <x-input-label for="pihak_pertama" value="Pihak Pertama (Penanggung Jawab BMN Saat Ini / Yang Menyerahkan) *" />
                <input
                    id="pihak_pertama"
                    name="pihak_pertama"
                    type="text"
                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] bg-[#F7FAF8] cursor-not-allowed focus:outline-none focus:ring-0 focus:border-[#E3ECE6] transition"
                    value="{{ old('pihak_pertama', $baPerpindahan->pihak_pertama ?: ($baPerpindahan->bmn?->penanggungjawab ?: $baPerpindahan->pengaju_nama)) }}"
                    placeholder="Otomatis terisi saat memilih Kode & NUP BMN di atas"
                    readonly
                    required
                />
                <input type="hidden" name="pihak_pertama_id" id="pihak_pertama_id" value="{{ old('pihak_pertama_id', $baPerpindahan->pihak_pertama_id ?: $baPerpindahan->bmn?->penanggung_jawab_id) }}" />
                <input type="hidden" name="pegawai_id" id="pegawai_id" value="{{ old('pegawai_id', $baPerpindahan->pegawai_id) }}" />
                <p class="text-[11px] text-[#62756A] mt-1">
                    <span class="inline-flex items-center gap-1 font-semibold text-[#163828]">
                        <svg class="w-3.5 h-3.5 text-[#163828]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pihak Pertama:
                    </span> Sesuai penanggung jawab barang yang terdaftar pada BMN saat ini.
                </p>
                <x-input-error :messages="$errors->get('pihak_pertama')" />
            </div>

            {{-- PIHAK KEDUA (PEGAWAI PENERIMA BARU) --}}
            <div>
                @if(auth()->user()->role === 'Operator')
                    @php
                        $initP2Val = old('pihak_kedua', $baPerpindahan->pihak_kedua);
                        $initP2Nip = '';
                        $initP2Nama = '';
                        if ($initP2Val && isset($pegawais)) {
                            $foundP2 = $pegawais->first(fn($p) =>
                                (strtolower("{$p->nama} ({$p->nip})") === strtolower($initP2Val))
                                || (strtolower($p->nama) === strtolower($initP2Val))
                                || ($p->nip && strtolower($p->nip) === strtolower($initP2Val))
                            );
                            if ($foundP2) {
                                $initP2Nip = $foundP2->nip ?? '';
                                $initP2Nama = $foundP2->nama;
                            } else {
                                $initP2Nama = $initP2Val;
                            }
                        }
                    @endphp
                    <x-input-label for="pihak_kedua" value="Pihak Kedua (Pegawai Penerima Baru) *" />
                    <div
                        x-data="{
                            open: false,
                            search: '',
                            selected: @js($initP2Val),
                            selectedNip: @js($initP2Nip),
                            selectedNama: @js($initP2Nama ?: 'Pilih atau cari pegawai penerima...'),
                            options: @js(($pegawais ?? collect())->map(fn($p) => [
                                'id' => (string)$p->id,
                                'nama' => $p->nama,
                                'nip' => $p->nip ?? '',
                                'jabatan' => $p->jabatan ?? '',
                                'unit_kerja' => $p->unit_kerja ?? '',
                                'label' => $p->nip ? "{$p->nama} ({$p->nip})" : $p->nama,
                            ])->values()),
                            get filtered() {
                                const penyerahStr = (document.getElementById('pihak_pertama')?.value || '').trim().toLowerCase();
                                const penyerahId = (document.getElementById('pihak_pertama_id')?.value || '').trim();
                                const pengajuId = (document.getElementById('pengaju_pegawai_id')?.value || '').trim();
                                const pengajuStr = (document.getElementById('pengaju_nama')?.value || '').trim().toLowerCase();

                                let list = this.options.filter(o => {
                                    if (penyerahId && o.id === penyerahId) return false;
                                    if (pengajuId && o.id === pengajuId) return false;
                                    if (penyerahStr) {
                                        const pNama = o.nama.toLowerCase();
                                        const pLabel = o.label.toLowerCase();
                                        if (pNama === penyerahStr || pLabel === penyerahStr || penyerahStr.includes(pNama)) return false;
                                    }
                                    if (pengajuStr) {
                                        const pNama = o.nama.toLowerCase();
                                        const pLabel = o.label.toLowerCase();
                                        if (pNama === pengajuStr || pLabel === pengajuStr || pengajuStr.includes(pNama)) return false;
                                    }
                                    return true;
                                });

                                if (!this.search.trim()) return list;
                                const q = this.search.toLowerCase().trim();
                                return list.filter(o =>
                                    (o.nip && o.nip.toLowerCase().includes(q)) ||
                                    (o.nama && o.nama.toLowerCase().includes(q)) ||
                                    (o.jabatan && o.jabatan.toLowerCase().includes(q)) ||
                                    (o.unit_kerja && o.unit_kerja.toLowerCase().includes(q)) ||
                                    (o.label && o.label.toLowerCase().includes(q))
                                );
                            },
                            select(opt) {
                                this.selected = opt.label;
                                this.selectedNip = opt.nip;
                                this.selectedNama = opt.nama;
                                this.open = false;
                                this.search = '';
                                const warningEl = document.getElementById('pihak_kedua_warning');
                                if (warningEl) warningEl.classList.add('hidden');
                            },
                            reset() {
                                this.selected = '';
                                this.selectedNip = '';
                                this.selectedNama = 'Pilih atau cari pegawai penerima...';
                                this.open = false;
                                this.search = '';
                            }
                        }"
                        @click.outside="open = false"
                        @keydown.escape="open = false"
                        class="relative mt-1"
                    >
                        <input type="hidden" id="pihak_kedua" name="pihak_kedua" :value="selected" required>
                        <datalist id="pegawai_pihak_kedua_list_edit">
                            @foreach(($pegawais ?? collect()) as $p)
                                @php
                                    $isPengaju = ($p->id == $baPerpindahan->pengaju_pegawai_id) || ($p->nama === $baPerpindahan->pengaju_nama);
                                    $isPenyerah = ($p->id == $baPerpindahan->pihak_pertama_id) || ($p->nama === $baPerpindahan->pihak_pertama);
                                @endphp
                                @if(! $isPengaju && ! $isPenyerah)
                                    <option value="{{ $p->nip ? $p->nama . ' (' . $p->nip . ')' : $p->nama }}">{{ $p->nip ? $p->nama . ' (' . $p->nip . ')' : $p->nama }}</option>
                                @endif
                            @endforeach
                        </datalist>

                        {{-- Trigger Button --}}
                        <button
                            type="button"
                            @click="open = !open; if(open) { $nextTick(() => $refs.searchP2Input.focus()); }"
                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg border border-[#E3ECE6] bg-white hover:border-[#163828] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 text-xs text-[#132219] transition cursor-pointer shadow-2xs font-medium text-left"
                            :class="open ? 'border-[#163828] ring-2 ring-[#163828]/20' : ''"
                        >
                            <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                                <svg class="w-3.5 h-3.5 text-[#8D9D95] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <template x-if="selectedNip">
                                    <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#EAF2ED] text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="selectedNip"></span>
                                </template>
                                <span class="truncate" :class="selected ? 'text-[#132219]' : 'text-[#8D9D95]'" x-text="selectedNama"></span>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <template x-if="selected">
                                    <span
                                        @click.stop="reset()"
                                        class="p-0.5 text-[#8D9D95] hover:text-[#B22222] transition cursor-pointer"
                                        title="Hapus pilihan"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </span>
                                </template>
                                <svg class="w-3.5 h-3.5 text-[#8D9D95] transition-transform duration-150" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
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
                            class="absolute left-0 top-full mt-1 w-full bg-white rounded-xl shadow-xl border border-[#D5E5DA] p-2 z-50 text-xs"
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
                                    x-ref="searchP2Input"
                                    x-model="search"
                                    placeholder="Ketik nama atau NIP pegawai..."
                                    class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                    @keydown.escape="open = false"
                                    @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                                >
                                <button
                                    type="button"
                                    x-show="search"
                                    @click="search = ''; $refs.searchP2Input.focus()"
                                    class="absolute inset-y-0 right-0 pr-2 flex items-center text-[#8D9D95] hover:text-[#132219] cursor-pointer"
                                >
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Option List --}}
                            <div class="max-h-56 overflow-y-auto space-y-0.5 divide-y divide-[#E3ECE6]/40" style="max-height: 220px; overflow-y: auto; overscroll-behavior: contain;">
                                <template x-for="opt in filtered" :key="opt.id">
                                    <button
                                        type="button"
                                        @click="select(opt)"
                                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-left transition hover:bg-[#EAF2ED] cursor-pointer group"
                                        :class="selected === opt.label ? 'bg-[#EAF2ED] text-[#163828] font-semibold' : 'text-[#132219]'"
                                    >
                                        <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                                            <template x-if="opt.nip">
                                                <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-white text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="opt.nip"></span>
                                            </template>
                                            <div class="truncate">
                                                <div class="truncate text-xs font-medium" x-text="opt.nama"></div>
                                                <template x-if="opt.unit_kerja || opt.jabatan">
                                                    <div class="truncate text-[10px] text-[#62756A]" x-text="opt.unit_kerja || opt.jabatan"></div>
                                                </template>
                                            </div>
                                        </div>
                                        <span x-show="selected === opt.label" class="text-[#163828] font-bold shrink-0 ml-1">✓</span>
                                    </button>
                                </template>
                                <div x-show="filtered.length === 0" class="py-4 text-center text-[#8D9D95]">
                                    <p class="text-xs">Tidak ada pegawai penerima cocok</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-[11px] text-[#62756A] mt-1">Ditentukan oleh Operator: Pegawai yang akan menerima aset.</p>
                    <p id="pihak_kedua_warning" class="text-[11px] text-[#9C322C] font-semibold mt-1 hidden items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Pegawai penyerah atau pengaju tidak dapat dipilih sebagai penerima barang.</span>
                    </p>
                @else
                    <x-input-label for="pihak_kedua_disabled" value="Pihak Kedua (Pegawai Penerima Baru)" />
                    <input
                        id="pihak_kedua_disabled"
                        type="text"
                        disabled
                        class="w-full text-xs font-medium px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#62756A] bg-[#F7FAF8] cursor-not-allowed italic"
                        value="Ditentukan oleh Operator (saat verifikasi)"
                    />
                    <p class="text-[11px] text-[#62756A] mt-1 italic">Hanya Operator yang dapat menentukan pegawai penerima baru.</p>
                @endif
                <x-input-error :messages="$errors->get('pihak_kedua')" />
            </div>
        </div>

        {{-- ALASAN --}}
        <div>
            <x-input-label for="alasan" value="Alasan Perpindahan *" />
            <textarea
                id="alasan"
                name="alasan"
                rows="3"
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
                required
            >{{ old('alasan', $baPerpindahan->alasan) }}</textarea>
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-between gap-3">
            @if($baPerpindahan->status !== 'Disetujui')
                <button
                    type="button"
                    onclick="if(confirm('Apakah Anda yakin ingin menghapus data BA perpindahan ini?')) { document.getElementById('delete-ba-form').submit(); }"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#FDF0EE] hover:bg-[#FCE3DF] border border-[#F8D4D0] text-[#9C322C] rounded-xl text-xs font-semibold transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Hapus BA</span>
                </button>
            @else
                <div></div>
            @endif

            <div class="flex items-center gap-3">
                <a
                    href="{{ route('ba-perpindahan.index', session('ba_perpindahan_edit_return_query.'.$baPerpindahan->id, [])) }}"
                    class="px-4 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-xl text-xs font-semibold transition"
                >
                    Kembali
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </div>
    </form>

    @if($baPerpindahan->status !== 'Disetujui')
        <form id="delete-ba-form" method="POST" action="{{ route('ba-perpindahan.destroy', $baPerpindahan) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

</div>
@endsection

@push('scripts')
<script>
window.baBmnList = @json($bmnsJson);
window.allPegawaiPihakKedua = @json($pegawaisJson);

function isPersonMatch(pegawai, searchStr, searchId) {
    if (!searchStr && !searchId) return false;
    if (searchId && String(pegawai.id) === String(searchId)) return true;
    if (!searchStr) return false;

    const s = searchStr.trim().toLowerCase();
    const pLabel = (pegawai.label || '').trim().toLowerCase();
    const pNama = (pegawai.nama || '').trim().toLowerCase();

    if (s === pLabel || s === pNama) return true;

    if (pegawai.nip) {
        const pNip = pegawai.nip.trim().toLowerCase();
        if (s.includes(pNip)) return true;
    }

    const strippedS = s.replace(/\s*\(.*?\)\s*/g, '').trim();
    if (strippedS && strippedS === pNama) return true;

    return false;
}

function syncPihakKeduaList() {
    const pihakPertamaInput = document.getElementById('pihak_pertama');
    const pihakPertamaIdInput = document.getElementById('pihak_pertama_id');
    const pengajuSelect = document.getElementById('pengaju_pegawai_id');
    const pengajuNamaInput = document.getElementById('pengaju_nama');
    const pihakKeduaInput = document.getElementById('pihak_kedua');
    const datalist = document.getElementById('pegawai_pihak_kedua_list_edit');
    const warningEl = document.getElementById('pihak_kedua_warning');
    const warningTextEl = warningEl ? warningEl.querySelector('span') : null;

    if (!datalist) return;

    const penyerahStr = pihakPertamaInput ? pihakPertamaInput.value.trim() : '';
    const penyerahId = pihakPertamaIdInput ? pihakPertamaIdInput.value.trim() : '';
    const pengajuId = pengajuSelect ? pengajuSelect.value.trim() : '';
    const pengajuOpt = pengajuSelect && pengajuSelect.selectedIndex >= 0 ? pengajuSelect.options[pengajuSelect.selectedIndex] : null;
    const pengajuStr = (pengajuOpt ? (optText => optText.getAttribute('data-nama') || optText.textContent)(pengajuOpt) : '') || (pengajuNamaInput ? pengajuNamaInput.value.trim() : '');

    // Rebuild options excluding BOTH the penyerah and pengaju
    datalist.innerHTML = '';
    window.allPegawaiPihakKedua.forEach(p => {
        const isPenyerah = isPersonMatch(p, penyerahStr, penyerahId);
        const isPengaju = isPersonMatch(p, pengajuStr, pengajuId);
        if (!isPenyerah && !isPengaju) {
            const opt = document.createElement('option');
            opt.value = p.label;
            datalist.appendChild(opt);
        }
    });

    // Check if current pihak_kedua matches penyerah or pengaju
    if (pihakKeduaInput && pihakKeduaInput.value.trim()) {
        const currentVal = pihakKeduaInput.value.trim();
        const matchesPenyerah = (penyerahStr || penyerahId) && (window.allPegawaiPihakKedua.some(p => {
            return isPersonMatch(p, penyerahStr, penyerahId) && isPersonMatch(p, currentVal, null);
        }) || (penyerahStr && currentVal.toLowerCase() === penyerahStr.toLowerCase()));

        const matchesPengaju = (pengajuStr || pengajuId) && (window.allPegawaiPihakKedua.some(p => {
            return isPersonMatch(p, pengajuStr, pengajuId) && isPersonMatch(p, currentVal, null);
        }) || (pengajuStr && currentVal.toLowerCase() === pengajuStr.toLowerCase()));

        if (matchesPenyerah || matchesPengaju) {
            pihakKeduaInput.value = '';
            const msg = matchesPengaju
                ? 'Pegawai pengaju tidak dapat dipilih sebagai penerima barang.'
                : 'Pegawai penyerah tidak dapat dipilih sebagai penerima barang.';
            if (warningEl) {
                if (warningTextEl) warningTextEl.textContent = msg;
                warningEl.classList.remove('hidden');
                warningEl.classList.add('flex');
            }
            pihakKeduaInput.setCustomValidity(msg);
        } else {
            if (warningEl) {
                warningEl.classList.add('hidden');
                warningEl.classList.remove('flex');
            }
            pihakKeduaInput.setCustomValidity('');
        }
    }
}

function baBmnSearchableSelect(config) {
    return {
        open: false,
        search: '',
        selectedId: config.initialId || '',
        selectedLabel: config.initialLabel || '',
        items: window.baBmnList || [],
        panelStyle: '',

        init() {
            if (this.selectedId) {
                const found = this.items.find(i => String(i.id) === String(this.selectedId));
                if (found) {
                    if (!this.selectedLabel) {
                        this.selectedLabel = found.label;
                    }
                    const namaBarangInput = document.getElementById('nama_barang');
                    const dariInput = document.getElementById('dari');
                    const pihakPertamaInput = document.getElementById('pihak_pertama');
                    const pihakPertamaIdInput = document.getElementById('pihak_pertama_id');
                    const pegawaiIdInput = document.getElementById('pegawai_id');

                    if (namaBarangInput && !namaBarangInput.value) {
                        namaBarangInput.value = found.nama_barang || '';
                    }
                    if (dariInput && !dariInput.value) {
                        dariInput.value = found.lokasi || '';
                    }
                    if (pihakPertamaInput && !pihakPertamaInput.value) {
                        pihakPertamaInput.value = (found.penanggungjawab || '').trim();
                    }
                    if (pihakPertamaIdInput && !pihakPertamaIdInput.value) {
                        pihakPertamaIdInput.value = found.penanggung_jawab_id || '';
                    }
                    if (pegawaiIdInput && !pegawaiIdInput.value) {
                        pegawaiIdInput.value = found.penanggung_jawab_id || '';
                    }
                }
            }
            syncPihakKeduaList();
        },

        get filteredItems() {
            if (!this.search || !this.search.trim()) {
                return this.items;
            }
            const q = this.search.toLowerCase().trim();
            return this.items.filter(item => {
                const kode = (item.kode_barang || '').toLowerCase();
                const nup = (item.nup || '').toLowerCase();
                const nama = (item.nama_barang || '').toLowerCase();
                const lokasi = (item.lokasi || '').toLowerCase();
                const pj = (item.penanggungjawab || '').toLowerCase();
                return kode.includes(q) || nup.includes(q) || nama.includes(q) || lokasi.includes(q) || pj.includes(q);
            });
        },

        toggleOpen() {
            this.open = !this.open;
            if (this.open) {
                this.updatePosition();
                this.$nextTick(() => {
                    this.updatePosition();
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            } else {
                this.search = '';
            }
        },

        closeDropdown() {
            this.open = false;
            this.search = '';
        },

        updatePosition() {
            const trigger = this.$refs.triggerBtn;
            if (!trigger) return;
            const rect = trigger.getBoundingClientRect();
            const panelHeight = 320;
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            let top;
            if (spaceBelow < panelHeight && spaceAbove > spaceBelow) {
                top = Math.max(10, rect.top - panelHeight - 6);
            } else {
                top = rect.bottom + 6;
            }

            const width = rect.width;
            let left = rect.left;
            if (left + width > window.innerWidth - 16) {
                left = window.innerWidth - width - 16;
            }
            if (left < 16) left = 16;

            this.panelStyle = `top: ${top}px; left: ${left}px; width: ${width}px; max-height: ${panelHeight}px;`;
        },

        selectItem(item) {
            this.selectedId = item.id;
            this.selectedLabel = item.label;
            this.open = false;
            this.search = '';

            const namaBarangInput = document.getElementById('nama_barang');
            const dariInput = document.getElementById('dari');
            const pihakPertamaInput = document.getElementById('pihak_pertama');
            const pihakPertamaIdInput = document.getElementById('pihak_pertama_id');
            const pegawaiIdInput = document.getElementById('pegawai_id');

            if (namaBarangInput) namaBarangInput.value = item.nama_barang || '';
            if (dariInput) dariInput.value = item.lokasi || '';
            if (pihakPertamaInput) {
                pihakPertamaInput.value = (item.penanggungjawab || '').trim();
                pihakPertamaInput.readOnly = true;
            }
            if (pihakPertamaIdInput) pihakPertamaIdInput.value = item.penanggung_jawab_id || '';
            if (pegawaiIdInput) pegawaiIdInput.value = item.penanggung_jawab_id || '';

            syncPihakKeduaList();
        },

        clearSelection() {
            this.selectedId = '';
            this.selectedLabel = '';
            this.open = false;
            this.search = '';

            const namaBarangInput = document.getElementById('nama_barang');
            const dariInput = document.getElementById('dari');
            const pihakPertamaInput = document.getElementById('pihak_pertama');
            const pihakPertamaIdInput = document.getElementById('pihak_pertama_id');
            const pegawaiIdInput = document.getElementById('pegawai_id');

            if (namaBarangInput) namaBarangInput.value = '';
            if (dariInput) dariInput.value = '';
            if (pihakPertamaInput) {
                pihakPertamaInput.value = '';
                pihakPertamaInput.readOnly = true;
            }
            if (pihakPertamaIdInput) pihakPertamaIdInput.value = '';
            if (pegawaiIdInput) pegawaiIdInput.value = '';

            syncPihakKeduaList();
        }
    };
}

document.addEventListener('DOMContentLoaded', function () {
    syncPihakKeduaList();

    const keSelect = document.getElementById('ke');
    const lokasiTujuanIdInput = document.getElementById('lokasi_tujuan_id');
    if (keSelect && lokasiTujuanIdInput) {
        keSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            lokasiTujuanIdInput.value = opt ? (opt.getAttribute('data-id') || '') : '';
        });
    }

    const pengajuSelect = document.getElementById('pengaju_pegawai_id');
    const pengajuNamaInput = document.getElementById('pengaju_nama');
    if (pengajuSelect && pengajuNamaInput) {
        pengajuSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            pengajuNamaInput.value = opt ? (opt.getAttribute('data-nama') || '') : '';
            syncPihakKeduaList();
        });
    }

    const pihakKeduaInput = document.getElementById('pihak_kedua');
    if (pihakKeduaInput) {
        function validatePihakKedua(clearOnMatch = false) {
            const pihakPertamaInput = document.getElementById('pihak_pertama');
            const pihakPertamaIdInput = document.getElementById('pihak_pertama_id');
            const penyerahStr = pihakPertamaInput ? pihakPertamaInput.value.trim() : '';
            const penyerahId = pihakPertamaIdInput ? pihakPertamaIdInput.value.trim() : '';

            const pengajuId = pengajuSelect ? pengajuSelect.value.trim() : '';
            const pengajuOpt = pengajuSelect && pengajuSelect.selectedIndex >= 0 ? pengajuSelect.options[pengajuSelect.selectedIndex] : null;
            const pengajuStr = (pengajuOpt ? (optText => optText.getAttribute('data-nama') || optText.textContent)(pengajuOpt) : '') || (pengajuNamaInput ? pengajuNamaInput.value.trim() : '');

            const warningEl = document.getElementById('pihak_kedua_warning');
            const warningTextEl = warningEl ? warningEl.querySelector('span') : null;

            if (!pihakKeduaInput.value.trim()) {
                if (warningEl) {
                    warningEl.classList.add('hidden');
                    warningEl.classList.remove('flex');
                }
                pihakKeduaInput.setCustomValidity('');
                return false;
            }

            const currentVal = pihakKeduaInput.value.trim();
            const matchesPenyerah = (penyerahStr || penyerahId) && (window.allPegawaiPihakKedua.some(p => {
                return isPersonMatch(p, penyerahStr, penyerahId) && isPersonMatch(p, currentVal, null);
            }) || (penyerahStr && currentVal.toLowerCase() === penyerahStr.toLowerCase()));

            const matchesPengaju = (pengajuStr || pengajuId) && (window.allPegawaiPihakKedua.some(p => {
                return isPersonMatch(p, pengajuStr, pengajuId) && isPersonMatch(p, currentVal, null);
            }) || (pengajuStr && currentVal.toLowerCase() === pengajuStr.toLowerCase()));

            if (matchesPenyerah || matchesPengaju) {
                if (clearOnMatch) {
                    pihakKeduaInput.value = '';
                }
                const msg = matchesPengaju
                    ? 'Pegawai pengaju tidak dapat dipilih sebagai penerima barang.'
                    : 'Pegawai penyerah tidak dapat dipilih sebagai penerima barang.';
                if (warningEl) {
                    if (warningTextEl) warningTextEl.textContent = msg;
                    warningEl.classList.remove('hidden');
                    warningEl.classList.add('flex');
                }
                pihakKeduaInput.setCustomValidity(msg);
                return true;
            } else {
                if (warningEl) {
                    warningEl.classList.add('hidden');
                    warningEl.classList.remove('flex');
                }
                pihakKeduaInput.setCustomValidity('');
                return false;
            }
        }

        pihakKeduaInput.addEventListener('input', function () {
            validatePihakKedua(false);
        });

        pihakKeduaInput.addEventListener('change', function () {
            validatePihakKedua(true);
        });
    }

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const pihakPertamaInput = document.getElementById('pihak_pertama');
            const pihakPertamaIdInput = document.getElementById('pihak_pertama_id');
            const pihakKeduaInput = document.getElementById('pihak_kedua');
            if (pihakKeduaInput && pihakKeduaInput.value.trim()) {
                const penyerahStr = pihakPertamaInput ? pihakPertamaInput.value.trim() : '';
                const penyerahId = pihakPertamaIdInput ? pihakPertamaIdInput.value.trim() : '';

                const pengajuId = pengajuSelect ? pengajuSelect.value.trim() : '';
                const pengajuOpt = pengajuSelect && pengajuSelect.selectedIndex >= 0 ? pengajuSelect.options[pengajuSelect.selectedIndex] : null;
                const pengajuStr = (pengajuOpt ? (optText => optText.getAttribute('data-nama') || optText.textContent)(pengajuOpt) : '') || (pengajuNamaInput ? pengajuNamaInput.value.trim() : '');

                const currentVal = pihakKeduaInput.value.trim();

                const matchesPenyerah = (penyerahStr || penyerahId) && (window.allPegawaiPihakKedua.some(p => {
                    return isPersonMatch(p, penyerahStr, penyerahId) && isPersonMatch(p, currentVal, null);
                }) || (penyerahStr && currentVal.toLowerCase() === penyerahStr.toLowerCase()));

                const matchesPengaju = (pengajuStr || pengajuId) && (window.allPegawaiPihakKedua.some(p => {
                    return isPersonMatch(p, pengajuStr, pengajuId) && isPersonMatch(p, currentVal, null);
                }) || (pengajuStr && currentVal.toLowerCase() === pengajuStr.toLowerCase()));

                if (matchesPenyerah || matchesPengaju) {
                    e.preventDefault();
                    const warningEl = document.getElementById('pihak_kedua_warning');
                    const warningTextEl = warningEl ? warningEl.querySelector('span') : null;
                    const alertMsg = matchesPengaju
                        ? 'Penerima (Pihak Kedua) tidak boleh sama dengan Pegawai Pengaju.'
                        : 'Penerima (Pihak Kedua) tidak boleh sama dengan Penyerah (Pihak Pertama).';
                    if (warningEl) {
                        if (warningTextEl) warningTextEl.textContent = alertMsg;
                        warningEl.classList.remove('hidden');
                        warningEl.classList.add('flex');
                    }
                    pihakKeduaInput.focus();
                    alert(alertMsg);
                    return false;
                }
            }
        });
    }
});
</script>
@endpush
