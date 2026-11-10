@extends('layouts.app')

@section('title', 'Ubah BMN — ' . $bmn->nama_barang)
@section('page-title', 'Ubah Data BMN')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('bmns.index', session('bmn_edit_return_query.'.$bmn->id, [])) }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar BMN</span>
        </a>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Ubah Data BMN</h1>
            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                {{ $bmn->kode_barang }}@if($bmn->nup)-NUP.{{ $bmn->nup }}@endif
            </span>
        </div>
        <p class="text-xs text-[#62756A] mt-0.5">Perbarui informasi barang inventaris: <span class="font-medium text-[#132219]">{{ $bmn->nama_barang }}</span></p>
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

    {{-- FORM EDIT --}}
    <form method="POST" action="{{ route('bmns.update', $bmn) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- KOLOM UTAMA (2/3) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- CARD INFORMASI DASAR --}}
                <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-5">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                        <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Informasi Identitas Barang</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- KODE BARANG --}}
                        <div>
                            <x-input-label for="kode_barang" value="Kode Barang *" />
                            <x-text-input
                                id="kode_barang"
                                name="kode_barang"
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                    maxlength="10"
                                data-numeric="true"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                class="w-full font-mono text-xs"
                                :value="old('kode_barang', $bmn->kode_barang)"
                                placeholder="mis. 3080305002 (maksimal 10 angka)"
                                required
                            />
                            <x-input-error :messages="$errors->get('kode_barang')" />
                        </div>

                        {{-- NUP --}}
                        <div>
                            <x-input-label for="nup" value="NUP *" />
                            <x-text-input
                                id="nup"
                                name="nup"
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                    maxlength="10"
                                data-numeric="true"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full font-mono text-xs"
                                :value="old('nup', $bmn->nup)"
                                required
                            />
                            <x-input-error :messages="$errors->get('nup')" />
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
                            :value="old('nama_barang', $bmn->nama_barang)"
                            required
                        />
                        <x-input-error :messages="$errors->get('nama_barang')" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- MEREK / TIPE --}}
                        <div>
                            <x-input-label for="merek" value="Merek / Tipe" />
                            <x-text-input
                                id="merek"
                                name="merek"
                                type="text"
                                class="w-full text-xs"
                                :value="old('merek', $bmn->merek)"
                            />
                        </div>

                        {{-- TANGGAL TERIMA --}}
                        <div>
                            <x-input-label for="tanggal_terima" value="Tanggal Terima" />
                            <x-text-input
                                id="tanggal_terima"
                                name="tanggal_terima"
                                type="date"
                                class="w-full text-xs"
                                :value="old('tanggal_terima', optional($bmn->tanggal_terima)->format('Y-m-d'))"
                                max="{{ date('Y-m-d') }}"
                            />
                            <x-input-error :messages="$errors->get('tanggal_terima')" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- TAHUN PEROLEHAN --}}
                        <div>
                            <x-input-label for="tahun_perolehan" value="Tahun Perolehan" />
                            <x-text-input
                                id="tahun_perolehan"
                                name="tahun_perolehan"
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                    maxlength="10"
                                data-numeric="true"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full text-xs font-mono"
                                :value="old('tahun_perolehan', $bmn->tahun_perolehan)"
                                placeholder="mis. {{ date('Y') }}"
                            />
                            <x-input-error :messages="$errors->get('tahun_perolehan')" />
                        </div>

                        {{-- NILAI PEROLEHAN --}}
                        <div>
                            <x-input-label for="nilai_perolehan" value="Nilai Perolehan (Rp)" />
                            <x-text-input
                                id="nilai_perolehan"
                                name="nilai_perolehan"
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                    maxlength="10"
                                data-numeric="true"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full text-xs font-mono"
                                :value="old('nilai_perolehan', $bmn->nilai_perolehan)"
                                placeholder="mis. 15000000"
                            />
                            <x-input-error :messages="$errors->get('nilai_perolehan')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- JENIS BARANG --}}
                        <div>
                            <x-input-label for="jenis" value="Jenis Barang *" />
                            <select
                                id="jenis"
                                name="jenis"
                                required
                                class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
                            >
                                <option value="">Pilih Jenis</option>
                                <option value="BMN-NON TIK" {{ old('jenis', $bmn->jenis) == 'BMN-NON TIK' ? 'selected' : '' }}>BMN-NON TIK (Umum)</option>
                                <option value="BMN-LAB" {{ old('jenis', $bmn->jenis) == 'BMN-LAB' ? 'selected' : '' }}>BMN-LAB</option>
                                <option value="BMN-TIK" {{ old('jenis', $bmn->jenis) == 'BMN-TIK' ? 'selected' : '' }}>BMN-TIK</option>
                                <option value="BMN-TIK & LAB" {{ old('jenis', $bmn->jenis) == 'BMN-TIK & LAB' ? 'selected' : '' }}>BMN-TIK & LAB</option>
                            </select>
                            <x-input-error :messages="$errors->get('jenis')" />
                        </div>

                        {{-- SATUAN --}}
                        <div>
                            <x-input-label for="satuan" value="Satuan" />
                            <select
                                id="satuan"
                                name="satuan"
                                class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
                            >
                                <option value="unit" {{ old('satuan', $bmn->satuan) == 'unit' ? 'selected' : '' }}>unit</option>
                                <option value="buah" {{ old('satuan', $bmn->satuan) == 'buah' ? 'selected' : '' }}>buah</option>
                                <option value="set" {{ old('satuan', $bmn->satuan) == 'set' ? 'selected' : '' }}>set</option>
                                <option value="paket" {{ old('satuan', $bmn->satuan) == 'paket' ? 'selected' : '' }}>paket</option>
                            </select>
                        </div>
                    </div>

                    {{-- SPESIFIKASI --}}
                    <div>
                        <x-input-label for="spesifikasi" value="Spesifikasi / Catatan Teknis" />
                        <textarea
                            id="spesifikasi"
                            name="spesifikasi"
                            rows="4"
                            class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
                        >{{ old('spesifikasi', $bmn->spesifikasi) }}</textarea>
                    </div>
                </div>

            </div>

            {{-- KOLOM SAMPING (1/3) --}}
            <div class="space-y-6">

                {{-- CARD PENEMPATAN & TANGGUNG JAWAB --}}
                <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-5">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                        <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Penempatan & Penanggung Jawab</h2>
                    </div>

                    {{-- LOKASI (SEARCHABLE DROPDOWN BERDASARKAN KODE RUANGAN & NAMA) --}}
                    @php
                        $initLokasi = old('lokasi', $bmn->lokasi);
                        $initKode = '';
                        $initNama = '';
                        if ($initLokasi) {
                            $foundLok = ($lokasis ?? collect())->first(fn($l) => $l->nama === $initLokasi || $l->format_kombinasi === $initLokasi || ($l->kode_ruangan && strcasecmp("{$l->kode_ruangan} {$l->nama}", $initLokasi) === 0) || ($bmn->lokasi_id && $l->id === $bmn->lokasi_id));
                            if ($foundLok) {
                                $initKode = $foundLok->kode_ruangan ?? '';
                                $initNama = $foundLok->nama;
                            } else {
                                $initNama = $initLokasi;
                            }
                        }
                    @endphp
                    <div
                        x-data="{
                            open: false,
                            search: '',
                            selected: @js($initLokasi ?: ''),
                            selectedKode: @js($initKode),
                            selectedNama: @js($initNama ?: 'Pilih Lokasi Ruangan...'),
                            options: @js(($lokasis ?? collect())->map(fn($l) => [
                                'nama' => $l->nama,
                                'kode' => $l->kode_ruangan ?? '',
                                'label' => $l->format_kombinasi,
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
                                this.selected = opt.label;
                                this.selectedKode = opt.kode;
                                this.selectedNama = opt.nama;
                                this.open = false;
                                this.search = '';
                            },
                            reset() {
                                this.selected = '';
                                selectedKode = '';
                                this.selectedNama = 'Pilih Lokasi Ruangan...';
                                this.open = false;
                                this.search = '';
                            }
                        }"
                        @click.outside="open = false"
                        @keydown.escape="open = false"
                        class="relative"
                    >
                        <x-input-label for="lokasi" value="Lokasi Ruangan *" />
                        <input type="hidden" id="lokasi" name="lokasi" :value="selected" required>

                        {{-- Trigger Button --}}
                        <div class="relative mt-1">
                            <button
                                type="button"
                                @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()); }"
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
                                        x-ref="searchInput"
                                        x-model="search"
                                        placeholder="Ketik kode (A1, B1..) atau nama ruangan..."
                                        class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                        @keydown.escape="open = false"
                                        @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                                    >
                                    <button
                                        type="button"
                                        x-show="search"
                                        @click="search = ''; $refs.searchInput.focus()"
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
                        <x-input-error :messages="$errors->get('lokasi')" class="mt-1" />
                    </div>

                    {{-- PENANGGUNG JAWAB --}}
                    <div>
                        <x-input-label for="penanggungjawab" value="Penanggung Jawab *" />
                        <select
                            id="penanggungjawab"
                            name="penanggungjawab"
                            required
                            onchange="autoFillBmnNip(this)"
                            class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
                        >
                            <option value="">Pilih Penanggung Jawab</option>
                            @foreach($pegawais ?? [] as $pegawai)
                                @php
                                    $pjFormatted = $pegawai->nip ? "{$pegawai->nama} ({$pegawai->nip})" : $pegawai->nama;
                                @endphp
                                <option
                                    value="{{ $pjFormatted }}"
                                    data-nip="{{ $pegawai->nip }}"
                                    data-nama="{{ $pegawai->nama }}"
                                    {{ old('penanggungjawab', $bmn->penanggungjawab) == $pjFormatted || old('penanggungjawab', $bmn->penanggungjawab) == $pegawai->nama ? 'selected' : '' }}
                                >
                                    {{ $pjFormatted }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('penanggungjawab')" />
                    </div>

                    {{-- NIP PJ --}}
                    <div>
                        <x-input-label for="penanggungjawab_nip" value="NIP Penanggung Jawab" />
                        <x-text-input
                            id="penanggungjawab_nip"
                            name="penanggungjawab_nip"
                            type="text"
                            class="w-full font-mono text-xs bg-[#F7FAF8] text-[#4D6357] cursor-not-allowed border-[#E3ECE6] focus:ring-0 focus:border-[#E3ECE6]"
                            :value="old('penanggungjawab_nip', $bmn->penanggungjawab_nip ?: '-')"
                            placeholder="Terisi otomatis dari penanggung jawab"
                            readonly
                            tabindex="-1"
                        />
                    </div>

                    {{-- KONDISI --}}
                    <div>
                        <x-input-label value="Kondisi Fisik *" />
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#E3ECE6] cursor-pointer hover:bg-[#F7FAF8] transition">
                                <input
                                    type="radio"
                                    name="kondisi"
                                    value="Baik"
                                    class="text-[#163828] focus:ring-[#163828]"
                                    {{ old('kondisi', $bmn->kondisi) == 'Baik' ? 'checked' : '' }}
                                >
                                <span class="text-xs font-semibold text-[#137333]">Baik</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#E3ECE6] cursor-pointer hover:bg-[#F7FAF8] transition">
                                <input
                                    type="radio"
                                    name="kondisi"
                                    value="Rusak Berat"
                                    class="text-[#163828] focus:ring-[#163828]"
                                    {{ in_array(old('kondisi', $bmn->kondisi), ['Rusak', 'Rusak Berat', 'Rusak Ringan']) ? 'checked' : '' }}
                                >
                                <span class="text-xs font-semibold text-[#9C322C]">Rusak Berat</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- TOMBOL AKSI --}}
                <div class="bg-white rounded-2xl border border-[#E3ECE6] p-5 shadow-2xs space-y-3">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Perubahan</span>
                    </button>

                    <a
                        href="{{ route('bmns.index', session('bmn_edit_return_query.'.$bmn->id, [])) }}"
                        class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-xl text-xs font-semibold transition"
                    >
                        Batal
                    </a>
                </div>

            </div>

        </div>
    </form>

</div>

<script>
    function autoFillBmnNip(sel) {
        const opt = sel.options[sel.selectedIndex];
        const nip = opt ? (opt.getAttribute('data-nip') || '') : '';
        const nipInput = document.getElementById('penanggungjawab_nip');
        if (nipInput) {
            nipInput.value = nip || '-';
        }
    }
</script>
@endsection