@extends('layouts.app')

@section('title', 'Ubah BA Distribusi — ' . $distribusi->nomor)
@section('page-title', 'Ubah BA Distribusi')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('distribusi.index', session('distribusi_edit_return_query.'.$distribusi->id, [])) }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Distribusi</span>
        </a>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Ubah BA Distribusi</h1>
            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                {{ $distribusi->nomor }}
            </span>
        </div>
        <p class="text-xs text-[#62756A] mt-0.5">Perbarui rincian dokumen berita acara penyerahan barang.</p>
    </div>

    {{-- INFO BMN TERKAIT --}}
    @if($distribusi->bmn)
        <div class="p-4 rounded-2xl bg-[#EAF2ED] border border-[#CDE1D4] text-[#163828] flex items-start gap-3.5 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-[#163828] text-white flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div class="space-y-1">
                <div class="text-xs font-bold uppercase tracking-wider text-[#163828]">Aset BMN Terkait: {{ $distribusi->bmn->nama_barang }}</div>
                <div class="text-[11px] text-[#2C4A38] space-y-0.5">
                    <div>Kode Barang: <span class="font-mono font-semibold">{{ $distribusi->bmn->kode_barang }}</span> | NUP: <span class="font-mono font-semibold">{{ $distribusi->bmn->nup }}</span> | Jenis: <span class="font-semibold">{{ $distribusi->bmn->jenis }}</span></div>
                    <div class="text-xs text-[#163828] font-medium pt-1">
                        Menyimpan perubahan Berita Acara ini akan <strong>secara otomatis memperbarui lokasi ruangan dan penanggung jawab</strong> pada data induk BMN.
                    </div>
                </div>
            </div>
        </div>
    @endif

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
    <form method="POST" action="{{ route('distribusi.update', $distribusi) }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Informasi Berita Acara</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- NOMOR DOKUMEN --}}
            <div>
                <x-input-label for="nomor" value="Nomor Berita Acara *" />
                <x-text-input
                    id="nomor"
                    name="nomor"
                    type="text"
                    class="w-full font-mono text-xs"
                    :value="old('nomor', $distribusi->nomor)"
                    required
                />
            </div>

            {{-- TANGGAL --}}
            <div>
                <x-input-label for="tanggal" value="Tanggal Distribusi *" />
                <x-text-input
                    id="tanggal"
                    name="tanggal"
                    type="date"
                    class="w-full text-xs"
                    :value="old('tanggal', optional($distribusi->tanggal)->format('Y-m-d'))"
                    max="{{ date('Y-m-d') }}"
                    required
                />
                <x-input-error :messages="$errors->get('tanggal')" class="mt-1" />
            </div>
        </div>

        {{-- NAMA BARANG --}}
        <div>
            <x-input-label for="nama_barang" value="Nama Barang *" />
            <x-text-input
                id="nama_barang"
                name="nama_barang"
                type="text"
                class="w-full text-xs font-medium"
                :value="old('nama_barang', $distribusi->nama_barang)"
                required
            />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- JUMLAH --}}
            <div>
                <x-input-label for="jumlah" value="Jumlah Barang *" />
                <x-text-input
                    id="jumlah"
                    name="jumlah"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    data-numeric="true"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    class="w-full text-xs"
                    :value="old('jumlah', $distribusi->jumlah)"
                    required
                />
            </div>

            {{-- SATUAN --}}
            <div>
                <x-input-label for="satuan" value="Satuan *" />
                <x-text-input
                    id="satuan"
                    name="satuan"
                    type="text"
                    class="w-full text-xs"
                    :value="old('satuan', $distribusi->satuan)"
                    required
                />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- LOKASI ASAL --}}
            <div>
                <x-input-label for="lokasi_asal" value="Lokasi Asal *" />
                <input
                    type="text"
                    id="lokasi_asal"
                    name="lokasi_asal"
                    list="lokasi_asal_list_edit"
                    class="w-full text-xs font-medium px-3.5 py-2 rounded-lg border border-[#E3ECE6] text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                    value="{{ old('lokasi_asal', $distribusi->lokasi_asal ?: 'Gudang BMN') }}"
                    placeholder="mis. Gudang BMN"
                    required
                />
                <datalist id="lokasi_asal_list_edit">
                    @foreach($lokasis ?? [] as $lokasi)
                        <option value="{{ $lokasi->nama }}" label="{{ $lokasi->format_kombinasi }}">{{ $lokasi->format_kombinasi }}</option>
                    @endforeach
                </datalist>
            </div>

                        {{-- LOKASI TUJUAN --}}
            @php
                $initTujuanVal = old('lokasi_tujuan', $distribusi->lokasi_tujuan);
                $initTujuanKode = '';
                $initTujuanNama = '';
                if ($initTujuanVal && isset($lokasis)) {
                    $foundLokTujuan = $lokasis->first(fn($l) =>
                        (strtolower($l->format_kombinasi) === strtolower($initTujuanVal))
                        || (strtolower($l->nama) === strtolower($initTujuanVal))
                        || (strtolower($l->kode_ruangan ?? '') === strtolower($initTujuanVal))
                    );
                    if ($foundLokTujuan) {
                        $initTujuanKode = $foundLokTujuan->kode_ruangan ?? '';
                        $initTujuanNama = $foundLokTujuan->nama;
                    } else {
                        $initTujuanNama = $initTujuanVal;
                    }
                }
            @endphp
            <div>
                <x-input-label for="lokasi_tujuan" value="Lokasi Tujuan *" />
                <div
                    x-data="{
                        open: false,
                        search: '',
                        selected: @js($initTujuanVal),
                        selectedKode: @js($initTujuanKode),
                        selectedNama: @js($initTujuanNama ?: 'Pilih atau cari ruangan tujuan...'),
                        options: @js(($lokasis ?? collect())->map(fn($l) => [
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
                            this.selectedKode = opt.kode;
                            this.selectedNama = opt.nama;
                            this.open = false;
                            this.search = '';
                        },
                        reset() {
                            this.selected = '';
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
                    <input type="hidden" id="lokasi_tujuan" name="lokasi_tujuan" :value="selected" required>

                    {{-- Trigger Button --}}
                    <button
                        type="button"
                        @click="open = !open; if(open) { $nextTick(() => $refs.searchLokasiInput.focus()); }"
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
                                x-ref="searchLokasiInput"
                                x-model="search"
                                placeholder="Ketik kode (A1, B1..) atau nama ruangan..."
                                class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                @keydown.escape="open = false"
                                @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                            >
                            <button
                                type="button"
                                x-show="search"
                                @click="search = ''; $refs.searchLokasiInput.focus()"
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
                <x-input-error :messages="$errors->get('lokasi_tujuan')" class="mt-1" />
            </div>
        </div>

        {{-- PENERIMA --}}
        @php
            $initPenerimaVal = old('penerima', $distribusi->penerima);
            $initPenerimaNip = '';
            $initPenerimaNama = '';
            if ($initPenerimaVal && isset($pegawais)) {
                $foundPegawai = $pegawais->first(fn($p) =>
                    (strtolower("{$p->nama} ({$p->nip})") === strtolower($initPenerimaVal))
                    || (strtolower($p->nama) === strtolower($initPenerimaVal))
                    || ($p->nip && strtolower($p->nip) === strtolower($initPenerimaVal))
                );
                if ($foundPegawai) {
                    $initPenerimaNip = $foundPegawai->nip ?? '';
                    $initPenerimaNama = $foundPegawai->nama;
                } else {
                    $initPenerimaNama = $initPenerimaVal;
                }
            }
        @endphp
        <div>
            <x-input-label for="penerima" value="Nama Pegawai Penerima *" />
            <div
                x-data="{
                    open: false,
                    search: '',
                    selected: @js($initPenerimaVal),
                    selectedNip: @js($initPenerimaNip),
                    selectedNama: @js($initPenerimaNama ?: 'Pilih atau cari pegawai penerima...'),
                    options: @js(($pegawais ?? collect())->map(fn($p) => [
                        'nama' => $p->nama,
                        'nip' => $p->nip ?? '',
                        'jabatan' => $p->jabatan ?? '',
                        'label' => $p->nip ? "{$p->nama} ({$p->nip})" : $p->nama,
                    ])->values()),
                    get filtered() {
                        if (!this.search.trim()) return this.options;
                        const q = this.search.toLowerCase().trim();
                        return this.options.filter(o =>
                            (o.nip && o.nip.toLowerCase().includes(q)) ||
                            (o.nama && o.nama.toLowerCase().includes(q)) ||
                            (o.jabatan && o.jabatan.toLowerCase().includes(q)) ||
                            (o.label && o.label.toLowerCase().includes(q))
                        );
                    },
                    select(opt) {
                        this.selected = opt.label;
                        this.selectedNip = opt.nip;
                        this.selectedNama = opt.nama;
                        this.open = false;
                        this.search = '';
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
                <input type="hidden" id="penerima" name="penerima" :value="selected" required>

                {{-- Trigger Button --}}
                <button
                    type="button"
                    @click="open = !open; if(open) { $nextTick(() => $refs.searchPegawaiInput.focus()); }"
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
                            x-ref="searchPegawaiInput"
                            x-model="search"
                            placeholder="Ketik nama atau NIP pegawai..."
                            class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                            @keydown.escape="open = false"
                            @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                        >
                        <button
                            type="button"
                            x-show="search"
                            @click="search = ''; $refs.searchPegawaiInput.focus()"
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
                                :class="(selected === opt.label || selectedNama === opt.nama) ? 'bg-[#EAF2ED] text-[#163828] font-semibold' : 'text-[#132219]'"
                            >
                                <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                                    <template x-if="opt.nip">
                                        <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-white text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="opt.nip"></span>
                                    </template>
                                    <div class="truncate">
                                        <div class="truncate text-xs font-medium" x-text="opt.nama"></div>
                                        <template x-if="opt.jabatan">
                                            <div class="truncate text-[10px] text-[#62756A]" x-text="opt.jabatan"></div>
                                        </template>
                                    </div>
                                </div>
                                <span x-show="selected === opt.label || selectedNama === opt.nama" class="text-[#163828] font-bold shrink-0 ml-1">✓</span>
                            </button>
                        </template>
                        <div x-show="filtered.length === 0" class="py-4 text-center text-[#8D9D95]">
                            <p class="text-xs">Tidak ada pegawai cocok</p>
                        </div>
                    </div>
                </div>
            </div>
            <x-input-error :messages="$errors->get('penerima')" class="mt-1" />
        </div>

        {{-- KETERANGAN --}}
        <div>
            <x-input-label for="keterangan" value="Keterangan / Catatan Tambahan" />
            <textarea
                id="keterangan"
                name="keterangan"
                rows="3"
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
            >{{ old('keterangan', $distribusi->keterangan) }}</textarea>
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
            <a
                href="{{ route('distribusi.index', session('distribusi_edit_return_query.'.$distribusi->id, [])) }}"
                class="px-4 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-xl text-xs font-semibold transition"
            >
                Batalkan
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
    </form>

</div>
@endsection
