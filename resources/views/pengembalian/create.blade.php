@extends('layouts.app')

@section('title', 'Tambah Pengembalian — SIBOB')
@section('page-title', 'Tambah Pengembalian')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('pengembalians.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Pengembalian BMN</span>
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Pengajuan Pengembalian BMN</h1>
        <p class="text-xs text-[#62756A] mt-0.5">Catat penyerahan kembali barang milik negara yang selesai digunakan.</p>
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
    <form method="POST" action="{{ route('pengembalians.store') }}" class="space-y-6">
        @csrf

        {{-- CARD 1: INFORMASI UTAMA --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Informasi Pengembalian</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- KOLOM 1: NOMOR (SIPANDA) & TANGGAL --}}
                <div class="space-y-4">
                    <div>
                        <x-input-label for="nomor_pengajuan" value="Nomor (SIPANDA)" />
                        <x-text-input
                            id="nomor_pengajuan"
                            name="nomor_pengajuan"
                            type="text"
                            class="w-full font-mono text-xs"
                            :value="old('nomor_pengajuan')"
                            placeholder="mis. PL.03.01.22A.{{ date('Ymd') }}.01"
                        />
                    </div>

                    <div>
                        <x-input-label for="tanggal" value="Tanggal *" />
                        <x-text-input
                            id="tanggal"
                            name="tanggal"
                            type="date"
                            class="w-full text-xs"
                            :value="old('tanggal', date('Y-m-d'))"
                            max="{{ date('Y-m-d') }}"
                            required
                        />
                        <x-input-error :messages="$errors->get('tanggal')" class="mt-1" />
                    </div>
                </div>

                {{-- KOLOM 2: BIDANG & PEGAWAI --}}
                <div class="space-y-4">
                    <div>
                        <x-input-label for="bidang" value="Bidang *" />
                        <select
                            id="bidang"
                            name="bidang"
                            required
                            class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
                        >
                            <option value="">pilih Bagian</option>
                            @foreach ($bidangs as $bidang)
                                <option value="{{ $bidang }}" @selected(old('bidang') === $bidang)>
                                    {{ $bidang }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="nama_pengaju" value="pegawai *" />
                        <select
                            id="nama_pengaju"
                            name="nama_pengaju"
                            required
                            class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
                        >
                            <option value="">pilih nama pegawai</option>
                            @foreach ($pegawais as $pegawai)
                                @php
                                    $pjFormatted = $pegawai->nip ? "{$pegawai->nama} ({$pegawai->nip})" : $pegawai->nama;
                                @endphp
                                <option value="{{ $pjFormatted }}" @selected(old('nama_pengaju') === $pjFormatted || old('nama_pengaju') === $pegawai->nama)>
                                    {{ $pjFormatted }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: DAFTAR BARANG --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-[#E3ECE6]">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Daftar barang</h2>
                </div>
                <span class="text-[11px] text-[#62756A]">Pilih item BMN yang dikembalikan</span>
            </div>

            @php
                $initialItems = old('items', [
                    ['bmn_id' => '', 'kondisi_awal' => '', 'kondisi' => 'Baik', 'keterangan' => '', 'merek' => '', 'nup' => '']
                ]);
                $bmnsJson = $bmns->map(function ($b) {
                    return [
                        'id' => (string)$b->id,
                        'nama_barang' => $b->nama_barang,
                        'merek' => $b->merek ?? '',
                        'nup' => (string)($b->nup ?? ''),
                        'kode_barang' => (string)($b->kode_barang ?? ''),
                        'kondisi' => (string)($b->kondisi ?? 'Baik'),
                        'label' => "{$b->nama_barang} (Kode: " . ($b->kode_barang ?? '-') . " | NUP: " . ($b->nup ?? '-') . ")",
                    ];
                })->values();
            @endphp

            <div class="overflow-x-auto border border-[#E3ECE6] rounded-xl min-h-[320px]">
                <table class="w-full text-left border-collapse" id="items-table">
                    <thead>
                        <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                            <th class="py-3 px-3 text-center w-12 border-r border-[#E3ECE6]">No</th>
                            <th class="py-3 px-4 min-w-[240px] border-r border-[#E3ECE6]">Nama Barang</th>
                            <th class="py-3 px-3 min-w-[120px] border-r border-[#E3ECE6]">Merk</th>
                            <th class="py-3 px-3 min-w-[90px] border-r border-[#E3ECE6]">NUP</th>
                            <th class="py-3 px-3 min-w-[130px] border-r border-[#E3ECE6]">Kondisi Awal</th>
                            <th class="py-3 px-3 min-w-[150px] border-r border-[#E3ECE6]">Kondisi Pengembalian <span class="text-[#9C322C] font-bold">*</span></th>
                            <th class="py-3 px-3 min-w-[160px] border-r border-[#E3ECE6]">Ket</th>
                            <th class="py-3 px-3 text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="items-tbody" class="divide-y divide-[#E3ECE6] text-xs">
                        @foreach ($initialItems as $index => $item)
                            @php
                                $selectedBmn = !empty($item['bmn_id']) ? $bmns->firstWhere('id', $item['bmn_id']) : null;
                                $selectedLabel = $selectedBmn ? "{$selectedBmn->nama_barang} (Kode: " . ($selectedBmn->kode_barang ?? '-') . " | NUP: " . ($selectedBmn->nup ?? '-') . ")" : '';
                                $merekVal = $item['merek'] ?? ($selectedBmn?->merek ?? '');
                                $nupVal = $item['nup'] ?? ($selectedBmn?->nup ?? '');
                                $kondisiAwalVal = $item['kondisi_awal'] ?? ($selectedBmn?->kondisi ?? 'Baik');
                                $kondisiVal = $item['kondisi'] ?? 'Baik';
                                $keteranganVal = $item['keterangan'] ?? '';
                            @endphp
                            <tr class="item-row hover:bg-[#FDFEFE] transition">
                                <td class="py-2.5 px-3 text-center text-[#62756A] font-mono text-[11px] font-semibold row-number border-r border-[#E3ECE6]">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="py-2 px-3 border-r border-[#E3ECE6]">
                                    <div
                                        x-data="bmnSearchableSelect({
                                            initialId: '{{ $item['bmn_id'] ?? '' }}',
                                            initialLabel: '{{ addslashes($selectedLabel) }}'
                                        })"
                                        class="relative w-full"
                                    >
                                        {{-- Hidden input for form submission --}}
                                        <input
                                            type="hidden"
                                            name="items[{{ $index }}][bmn_id]"
                                            :value="selectedId"
                                            class="bmn-id-input"
                                        />

                                        {{-- Trigger Button --}}
                                        <button
                                            type="button"
                                            x-ref="triggerBtn"
                                            @click="toggleOpen()"
                                            class="w-full flex items-center justify-between text-left text-xs px-3 py-2 rounded-lg border border-[#E3ECE6] bg-white hover:border-[#163828] focus:outline-none focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 transition cursor-pointer"
                                            :class="open ? 'border-[#163828] ring-2 ring-[#163828]/20' : ''"
                                        >
                                            <div class="flex items-center gap-1.5 truncate pr-2">
                                                <span x-show="selectedId" x-text="selectedLabel" class="font-medium text-[#132219] truncate"></span>
                                                <span x-show="!selectedId" class="text-[#8D9D95]">Pilih Barang</span>
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
                                            <div class="p-2 border-b border-[#E3ECE6] bg-[#F7FAF8] shrink-0">
                                                <div class="relative">
                                                    <input
                                                        x-ref="searchInput"
                                                        x-model="search"
                                                        type="text"
                                                        placeholder="Ketik nama, kode, atau NUP barang..."
                                                        class="w-full text-xs pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] text-[#132219] bg-white focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                                        @keydown.escape="closeDropdown()"
                                                        @keydown.enter.prevent="if (filteredItems.length > 0) selectItem(filteredItems[0])"
                                                    />
                                                    <svg class="w-3.5 h-3.5 text-[#8D9D95] absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                    <button
                                                        type="button"
                                                        x-show="search"
                                                        @click="search = ''; $refs.searchInput.focus()"
                                                        class="absolute right-2 top-2 text-[#8D9D95] hover:text-[#132219]"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Options List --}}
                                            <div class="flex-1 max-h-56 overflow-y-auto divide-y divide-[#E3ECE6]/50 overscroll-contain">
                                                <template x-for="bmn in filteredItems" :key="bmn.id">
                                                    <div
                                                        @click="selectItem(bmn)"
                                                        class="px-3.5 py-2.5 text-xs cursor-pointer hover:bg-[#EAF2ED] transition flex items-center justify-between gap-2"
                                                        :class="String(selectedId) === String(bmn.id) ? 'bg-[#EAF2ED]/70 font-semibold text-[#163828]' : 'text-[#132219]'"
                                                    >
                                                        <div class="min-w-0">
                                                            <div class="font-medium text-[#132219] truncate" x-text="bmn.nama_barang"></div>
                                                            <div class="text-[11px] text-[#62756A] flex items-center gap-1.5 flex-wrap mt-0.5">
                                                                <span class="font-mono text-[#163828]" x-text="'Kode: ' + (bmn.kode_barang || '-')"></span>
                                                                <span>•</span>
                                                                <span class="font-mono text-[#163828]" x-text="'NUP: ' + (bmn.nup || '-')"></span>
                                                                <template x-if="bmn.merek">
                                                                    <span class="text-[#62756A]" x-text="'• ' + bmn.merek"></span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <svg x-show="String(selectedId) === String(bmn.id)" class="w-4 h-4 text-[#163828] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
                                </td>
                                <td class="py-2 px-3 border-r border-[#E3ECE6]">
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][merek]"
                                        value="{{ $merekVal }}"
                                        readonly
                                        class="item-merek w-full bg-[#F7FAF8] border border-[#E3ECE6] text-[#4D6357] rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                                        placeholder=""
                                    />
                                </td>
                                <td class="py-2 px-3 border-r border-[#E3ECE6]">
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][nup]"
                                        value="{{ $nupVal }}"
                                        readonly
                                        class="item-nup w-full bg-[#F7FAF8] border border-[#E3ECE6] text-[#4D6357] font-mono rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                                        placeholder=""
                                    />
                                </td>
                                <td class="py-2 px-3 border-r border-[#E3ECE6]">
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][kondisi_awal]"
                                        value="{{ old("items.{$index}.kondisi_awal", $kondisiAwalVal) }}"
                                        readonly
                                        class="item-kondisi-awal w-full bg-[#F7FAF8] border border-[#E3ECE6] text-[#4D6357] font-medium rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                                        placeholder="Kondisi awal"
                                    />
                                </td>
                                <td class="py-2 px-3 border-r border-[#E3ECE6]">
                                    <select
                                        name="items[{{ $index }}][kondisi]"
                                        required
                                        class="item-kondisi w-full bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 text-[#132219] rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
                                    >
                                        <option value="Baik" @selected(old("items.{$index}.kondisi", $kondisiVal) === 'Baik')>Baik</option>
                                        <option value="Rusak Ringan" @selected(old("items.{$index}.kondisi", $kondisiVal) === 'Rusak Ringan')>Rusak Ringan</option>
                                        <option value="Rusak Berat" @selected(old("items.{$index}.kondisi", $kondisiVal) === 'Rusak Berat')>Rusak Berat</option>
                                    </select>
                                </td>
                                <td class="py-2 px-3 border-r border-[#E3ECE6]">
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][keterangan]"
                                        value="{{ old("items.{$index}.keterangan", $keteranganVal) }}"
                                        class="item-keterangan w-full bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 text-[#132219] placeholder-[#8D9D95] rounded-lg text-xs py-2 px-3 transition"
                                        placeholder="Keterangan (opsional)"
                                    />
                                </td>
                                <td class="py-2 px-3 text-center">
                                    <button
                                        type="button"
                                        class="btn-remove-row p-1.5 text-[#9C322C] hover:bg-[#FDF0EE] rounded-lg transition cursor-pointer"
                                        title="Hapus Baris"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TOMBOL TAMBAH BARIS BARU --}}
            <div>
                <button
                    type="button"
                    id="btn-add-row"
                    class="w-full py-2.5 bg-[#9AA9A1]/35 hover:bg-[#9AA9A1]/50 text-[#163828] border border-[#CBD7D0] rounded-xl text-xs font-bold tracking-wider uppercase transition flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>TAMBAH BARIS BARU</span>
                </button>
            </div>
        </div>

        {{-- CARD 3: TOMBOL SIMPAN / AKSI --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs flex items-center justify-between">
            <button
                type="submit"
                id="btn-submit-pengembalian"
                style="background-color: #163828 !important; color: #ffffff !important;"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-sm font-bold tracking-wide shadow-md transition cursor-pointer"
            >
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>Simpan</span>
            </button>

            <a
                href="{{ route('pengembalians.index') }}"
                class="px-5 py-2.5 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-xl text-xs font-semibold transition"
            >
                Batalkan
            </a>
        </div>
    </form>

</div>

{{-- TEMPLATE ROW BARU UNTUK JAVASCRIPT --}}
<template id="row-template">
    <tr class="item-row hover:bg-[#FDFEFE] transition">
        <td class="py-2.5 px-3 text-center text-[#62756A] font-mono text-[11px] font-semibold row-number border-r border-[#E3ECE6]">
            __NO__
        </td>
        <td class="py-2 px-3 border-r border-[#E3ECE6]">
            <div
                x-data="bmnSearchableSelect({
                    initialId: '',
                    initialLabel: ''
                })"
                class="relative w-full"
            >
                <input
                    type="hidden"
                    name="items[__INDEX__][bmn_id]"
                    :value="selectedId"
                    class="bmn-id-input"
                />

                <button
                    type="button"
                    x-ref="triggerBtn"
                    @click="toggleOpen()"
                    class="w-full flex items-center justify-between text-left text-xs px-3 py-2 rounded-lg border border-[#E3ECE6] bg-white hover:border-[#163828] focus:outline-none focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 transition cursor-pointer"
                    :class="open ? 'border-[#163828] ring-2 ring-[#163828]/20' : ''"
                >
                    <div class="flex items-center gap-1.5 truncate pr-2">
                        <span x-show="selectedId" x-text="selectedLabel" class="font-medium text-[#132219] truncate"></span>
                        <span x-show="!selectedId" class="text-[#8D9D95]">Pilih Barang</span>
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
                    <div class="p-2 border-b border-[#E3ECE6] bg-[#F7FAF8] shrink-0">
                        <div class="relative">
                            <input
                                x-ref="searchInput"
                                x-model="search"
                                type="text"
                                placeholder="Ketik nama, kode, atau NUP barang..."
                                class="w-full text-xs pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] text-[#132219] bg-white focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                @keydown.escape="closeDropdown()"
                                @keydown.enter.prevent="if (filteredItems.length > 0) selectItem(filteredItems[0])"
                            />
                            <svg class="w-3.5 h-3.5 text-[#8D9D95] absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <button
                                type="button"
                                x-show="search"
                                @click="search = ''; $refs.searchInput.focus()"
                                class="absolute right-2 top-2 text-[#8D9D95] hover:text-[#132219]"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 max-h-56 overflow-y-auto divide-y divide-[#E3ECE6]/50 overscroll-contain">
                        <template x-for="bmn in filteredItems" :key="bmn.id">
                            <div
                                @click="selectItem(bmn)"
                                class="px-3.5 py-2.5 text-xs cursor-pointer hover:bg-[#EAF2ED] transition flex items-center justify-between gap-2"
                                :class="String(selectedId) === String(bmn.id) ? 'bg-[#EAF2ED]/70 font-semibold text-[#163828]' : 'text-[#132219]'"
                            >
                                <div class="min-w-0">
                                    <div class="font-medium text-[#132219] truncate" x-text="bmn.nama_barang"></div>
                                    <div class="text-[11px] text-[#62756A] flex items-center gap-1.5 flex-wrap mt-0.5">
                                        <span class="font-mono text-[#163828]" x-text="'Kode: ' + (bmn.kode_barang || '-')"></span>
                                        <span>•</span>
                                        <span class="font-mono text-[#163828]" x-text="'NUP: ' + (bmn.nup || '-')"></span>
                                        <template x-if="bmn.merek">
                                            <span class="text-[#62756A]" x-text="'• ' + bmn.merek"></span>
                                        </template>
                                    </div>
                                </div>
                                <svg x-show="String(selectedId) === String(bmn.id)" class="w-4 h-4 text-[#163828] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
        </td>
        <td class="py-2 px-3 border-r border-[#E3ECE6]">
            <input
                type="text"
                name="items[__INDEX__][merek]"
                readonly
                class="item-merek w-full bg-[#F7FAF8] border border-[#E3ECE6] text-[#4D6357] rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                placeholder=""
            />
        </td>
        <td class="py-2 px-3 border-r border-[#E3ECE6]">
            <input
                type="text"
                name="items[__INDEX__][nup]"
                readonly
                class="item-nup w-full bg-[#F7FAF8] border border-[#E3ECE6] text-[#4D6357] font-mono rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                placeholder=""
            />
        </td>
        <td class="py-2 px-3 border-r border-[#E3ECE6]">
            <input
                type="text"
                name="items[__INDEX__][kondisi_awal]"
                readonly
                class="item-kondisi-awal w-full bg-[#F7FAF8] border border-[#E3ECE6] text-[#4D6357] font-medium rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                placeholder="Kondisi awal"
            />
        </td>
        <td class="py-2 px-3 border-r border-[#E3ECE6]">
            <select
                name="items[__INDEX__][kondisi]"
                required
                class="item-kondisi w-full bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 text-[#132219] rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
            >
                <option value="Baik">Baik</option>
                <option value="Rusak Ringan">Rusak Ringan</option>
                <option value="Rusak Berat">Rusak Berat</option>
            </select>
        </td>
        <td class="py-2 px-3 border-r border-[#E3ECE6]">
            <input
                type="text"
                name="items[__INDEX__][keterangan]"
                class="item-keterangan w-full bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 text-[#132219] placeholder-[#8D9D95] rounded-lg text-xs py-2 px-3 transition"
                placeholder="Keterangan (opsional)"
            />
        </td>
        <td class="py-2 px-3 text-center">
            <button
                type="button"
                class="btn-remove-row p-1.5 text-[#9C322C] hover:bg-[#FDF0EE] rounded-lg transition cursor-pointer"
                title="Hapus Baris"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </td>
    </tr>
</template>

<script>
window.bmnList = @json($bmnsJson);

function bmnSearchableSelect(config) {
    return {
        open: false,
        search: '',
        selectedId: config.initialId || '',
        selectedLabel: config.initialLabel || '',
        items: window.bmnList || [],
        panelStyle: '',

        init() {
            if (this.selectedId && !this.selectedLabel) {
                const found = this.items.find(i => String(i.id) === String(this.selectedId));
                if (found) {
                    this.selectedLabel = found.label;
                }
            }
        },

        get filteredItems() {
            if (!this.search || !this.search.trim()) {
                return this.items;
            }
            const q = this.search.toLowerCase().trim();
            return this.items.filter(item => {
                const nama = (item.nama_barang || '').toLowerCase();
                const kode = (item.kode_barang || '').toLowerCase();
                const nup = (item.nup || '').toLowerCase();
                const merek = (item.merek || '').toLowerCase();
                return nama.includes(q) || kode.includes(q) || nup.includes(q) || merek.includes(q);
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
            const trigger = this.$refs.triggerBtn || this.$el.querySelector('button');
            if (!trigger) return;
            const rect = trigger.getBoundingClientRect();
            const panelHeight = 280;
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            let top;
            if (spaceBelow < panelHeight && spaceAbove > spaceBelow) {
                top = Math.max(10, rect.top - panelHeight - 6);
            } else {
                top = rect.bottom + 6;
            }

            const width = Math.max(rect.width, 360);
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

            const row = this.$el.closest('.item-row');
            if (row) {
                const merekInput = row.querySelector('.item-merek');
                const nupInput = row.querySelector('.item-nup');
                const kondisiAwalInput = row.querySelector('.item-kondisi-awal');
                const kondisiSelect = row.querySelector('.item-kondisi');
                if (merekInput) merekInput.value = item.merek || '';
                if (nupInput) nupInput.value = item.nup || '';
                if (kondisiAwalInput) kondisiAwalInput.value = item.kondisi || 'Baik';
                if (kondisiSelect && !kondisiSelect.value) {
                    kondisiSelect.value = item.kondisi || 'Baik';
                }
            }
        },

        clearSelection() {
            this.selectedId = '';
            this.selectedLabel = '';
            this.open = false;
            this.search = '';

            const row = this.$el.closest('.item-row');
            if (row) {
                const merekInput = row.querySelector('.item-merek');
                const nupInput = row.querySelector('.item-nup');
                const kondisiAwalInput = row.querySelector('.item-kondisi-awal');
                const kondisiSelect = row.querySelector('.item-kondisi');
                const ketInput = row.querySelector('.item-keterangan');
                if (merekInput) merekInput.value = '';
                if (nupInput) nupInput.value = '';
                if (kondisiAwalInput) kondisiAwalInput.value = '';
                if (kondisiSelect) {
                    kondisiSelect.value = 'Baik';
                }
                if (ketInput) ketInput.value = '';
            }
        }
    };
}

document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('items-tbody');
    const template = document.getElementById('row-template');
    const btnAdd = document.getElementById('btn-add-row');
    const form = document.querySelector('form');
    let rowCounter = tbody.querySelectorAll('.item-row').length + 10;

    function updateRowNumbers() {
        const rows = tbody.querySelectorAll('.item-row');
        rows.forEach((row, idx) => {
            const noCell = row.querySelector('.row-number');
            if (noCell) {
                noCell.textContent = idx + 1;
            }
        });
    }

    function attachRowListeners(row) {
        const btnRemove = row.querySelector('.btn-remove-row');
        if (btnRemove) {
            btnRemove.addEventListener('click', function () {
                const rows = tbody.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    row.remove();
                    updateRowNumbers();
                } else {
                    const idInput = row.querySelector('.bmn-id-input');
                    if (idInput) {
                        idInput.value = '';
                    }
                    const merekInput = row.querySelector('.item-merek');
                    if (merekInput) merekInput.value = '';
                    const nupInput = row.querySelector('.item-nup');
                    if (nupInput) nupInput.value = '';
                    const kondisiAwal = row.querySelector('.item-kondisi-awal');
                    if (kondisiAwal) kondisiAwal.value = '';
                    const kondisi = row.querySelector('.item-kondisi');
                    if (kondisi) {
                        kondisi.value = 'Baik';
                    }
                    const ket = row.querySelector('.item-keterangan');
                    if (ket) {
                        ket.value = '';
                    }

                    const alpineEl = row.querySelector('[x-data]');
                    if (alpineEl && alpineEl._x_dataStack && alpineEl._x_dataStack[0]) {
                        alpineEl._x_dataStack[0].clearSelection();
                    }
                }
            });
        }
    }

    tbody.querySelectorAll('.item-row').forEach(attachRowListeners);

    if (form) {
        form.addEventListener('submit', function (e) {
            const rows = tbody.querySelectorAll('.item-row');
            let hasSelectedBmn = false;

            rows.forEach(row => {
                const idInput = row.querySelector('.bmn-id-input');
                if (idInput && idInput.value) {
                    hasSelectedBmn = true;
                }
            });

            if (!hasSelectedBmn) {
                e.preventDefault();
                alert('Silakan pilih minimal satu barang yang akan dikembalikan.');
                return;
            }
        });
    }

    btnAdd.addEventListener('click', function () {
        rowCounter++;
        const currentCount = tbody.querySelectorAll('.item-row').length + 1;
        let html = template.innerHTML
            .replace(/__INDEX__/g, rowCounter)
            .replace(/__NO__/g, currentCount);

        const tempContainer = document.createElement('tbody');
        tempContainer.innerHTML = html.trim();
        const newRow = tempContainer.firstElementChild;

        tbody.appendChild(newRow);
        attachRowListeners(newRow);
        updateRowNumbers();

        if (window.Alpine) {
            window.Alpine.initTree(newRow);
        }
    });
});
</script>
@endsection
