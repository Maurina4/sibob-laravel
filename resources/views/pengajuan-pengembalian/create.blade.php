@extends('layouts.app')

@section('title', 'Form Pengajuan Pengembalian BMN — SIBOB')
@section('page-title', 'Pengajuan Pengembalian BMN')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto" x-data="pengajuanForm()">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('pengajuan-pengembalian.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Riwayat Pengajuan</span>
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Pengajuan Pengembalian BMN</h1>
        <p class="text-xs text-[#62756A] mt-0.5">
            Ajukan pengembalian BMN yang telah selesai digunakan atau laporkan barang rusak berat untuk verifikasi pengelola BMN.
        </p>
    </div>

    {{-- ERROR VALIDASI --}}
    @if ($errors->any())
        <div class="p-4 rounded-xl border border-[#F8D4D0] bg-[#FDF0EE] text-[#9C322C] text-xs space-y-1">
            <strong class="font-semibold block mb-1">Terdapat kesalahan pada isian formulir:</strong>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM UTAMA --}}
    <form method="POST" action="{{ route('pengajuan-pengembalian.store') }}" class="space-y-6" @submit="handleSubmit($event)">
        @csrf

        {{-- CARD 1: DATA PEMOHON --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-7 shadow-2xs space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Identitas Pemohon</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- NAMA PEGAWAI --}}
                <div>
                    <label class="block text-xs font-semibold text-[#132219] mb-1">Nama Pemohon</label>
                    <input
                        type="text"
                        value="{{ $user->name }}"
                        readonly
                        class="w-full bg-[#F7FAF8] border border-[#E3ECE6] text-[#4D6357] font-medium rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                    />
                    <p class="text-[11px] text-[#62756A] mt-1">Sesuai akun login yang aktif.</p>
                </div>

                {{-- NIP & JABATAN --}}
                <div>
                    <label class="block text-xs font-semibold text-[#132219] mb-1">NIP & Jabatan</label>
                    <input
                        type="text"
                        value="{{ ($pegawai?->nip ?: '-') . ' / ' . ($pegawai?->jabatan ?: 'Pegawai') }}"
                        readonly
                        class="w-full bg-[#F7FAF8] border border-[#E3ECE6] text-[#4D6357] rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                    />
                    <p class="text-[11px] text-[#62756A] mt-1">Data master kepegawaian.</p>
                </div>

                {{-- BIDANG / SUBSTANSI --}}
                <div>
                    <label for="bidang" class="block text-xs font-semibold text-[#132219] mb-1">
                        Bidang / Substansi <span class="text-[#9C322C]">*</span>
                    </label>
                    <select
                        id="bidang"
                        name="bidang"
                        required
                        class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
                    >
                        <option value="">-- Pilih Bidang / Bagian --</option>
                        @foreach ($bidangs as $b)
                            <option
                                value="{{ $b }}"
                                @selected(old('bidang', $pegawai?->bidang) === $b)
                            >
                                {{ $b }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- CARD 2: JENIS PENGAJUAN (ALASAN) --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-7 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#E3ECE6]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#163828]"></span>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Jenis & Alasan Pengembalian <span class="text-[#9C322C]">*</span></h2>
                </div>
                <span class="text-[11px] text-[#62756A]">Pilih salah satu sesuai kondisi riil barang</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- PILIHAN A: PENGEMBALIAN BIASA --}}
                <label
                    @click="setAlasan('pengembalian_biasa')"
                    class="relative flex flex-col p-4 rounded-xl border-2 transition cursor-pointer"
                    :class="alasan === 'pengembalian_biasa' ? 'border-[#163828] bg-[#F7FAF8]' : 'border-[#E3ECE6] bg-white hover:border-[#163828]/40'"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2.5">
                            <input
                                type="radio"
                                name="alasan"
                                value="pengembalian_biasa"
                                x-model="alasan"
                                class="text-[#163828] focus:ring-[#163828] h-4 w-4"
                            />
                            <span class="text-xs font-bold text-[#132219]">Pengembalian Barang Layak Pakai</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                            Kondisi: Baik
                        </span>
                    </div>
                    <p class="text-[11px] text-[#62756A] mt-2.5 ml-6.5 leading-relaxed">
                        BMN dalam kondisi baik dan berfungsi normal yang sudah tidak digunakan lagi atau selesai masa penugasan.
                    </p>
                    <div class="mt-3 ml-6.5 text-[10px] text-emerald-700 bg-emerald-50 px-2.5 py-1.5 rounded-lg border border-emerald-200/60 font-medium">
                        ✓ <strong>Masuk ke BA Pengembalian</strong> (BA-PGM/...) & Gudang BMN.
                    </div>
                </label>

                {{-- PILIHAN B: RUSAK BERAT --}}
                <label
                    @click="setAlasan('rusak_berat')"
                    class="relative flex flex-col p-4 rounded-xl border-2 transition cursor-pointer"
                    :class="alasan === 'rusak_berat' ? 'border-[#9C322C] bg-[#FDF0EE]/50' : 'border-[#E3ECE6] bg-white hover:border-[#9C322C]/40'"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2.5">
                            <input
                                type="radio"
                                name="alasan"
                                value="rusak_berat"
                                x-model="alasan"
                                class="text-[#9C322C] focus:ring-[#9C322C] h-4 w-4"
                            />
                            <span class="text-xs font-bold text-[#9C322C]">Pelaporan Rusak Berat (Usul Hapus)</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">
                            Kondisi: Rusak Berat
                        </span>
                    </div>
                    <p class="text-[11px] text-[#62756A] mt-2.5 ml-6.5 leading-relaxed">
                        BMN mengalami kerusakan fatal, mati total, atau tidak ekonomis diperbaiki. Memerlukan tanggal kejadian dan deskripsi kerusakan per item.
                    </p>
                    <div class="mt-3 ml-6.5 text-[10px] text-rose-700 bg-rose-50 px-2.5 py-1.5 rounded-lg border border-rose-200/60 font-medium">
                        ✓ <strong>Masuk ke Daftar Rusak Berat</strong> (LK/... usulan hapus aset) • Tidak masuk ke BA Pengembalian.
                    </div>
                </label>
            </div>
        </div>

        {{-- CARD 3: DAFTAR BARANG YANG DIAJUKAN --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-[#E3ECE6]">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Daftar barang</h2>
                </div>
                <div class="text-[11px] text-[#62756A] flex items-center gap-2">
                    <span>Total barang dipilih: <strong class="text-[#132219]" x-text="items.length"></strong></span>
                </div>
            </div>

            @if ($initialBmns->isEmpty() && auth()->user()?->role !== 'Operator')
                <div class="p-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-800 text-xs flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <strong class="font-semibold block mb-0.5">Tidak Ada BMN Terdaftar Atas Nama Anda</strong>
                        <span>Saat ini belum ada BMN yang tercatat dengan penanggung jawab atas nama akun Anda. Anda hanya dapat mengajukan pengembalian untuk BMN yang menjadi tanggung jawab Anda.</span>
                    </div>
                </div>
            @endif

            {{-- TABEL DAFTAR BARANG --}}
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
                    <template x-for="(item, index) in items" :key="item.uid">
                        <tbody class="divide-y divide-[#E3ECE6] text-xs">
                            <tr class="item-row hover:bg-[#FDFEFE] transition">
                                {{-- NO --}}
                                <td class="py-2.5 px-3 text-center text-[#62756A] font-mono text-[11px] font-semibold border-r border-[#E3ECE6]" x-text="index + 1"></td>

                                {{-- NAMA BARANG --}}
                                <td class="py-2 px-3 border-r border-[#E3ECE6]">
                                    <div class="relative w-full">
                                        <input
                                            type="hidden"
                                            :name="`items[${index}][bmn_id]`"
                                            :value="item.bmn_id"
                                        />

                                        <button
                                            type="button"
                                            :id="`bmn-trigger-${index}`"
                                            @click="toggleDropdown(index, $event.currentTarget)"
                                            class="w-full flex items-center justify-between text-left text-xs px-3 py-2 rounded-lg border border-[#E3ECE6] bg-white hover:border-[#163828] focus:outline-none focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 transition cursor-pointer"
                                            :class="item.open ? 'border-[#163828] ring-2 ring-[#163828]/20' : ''"
                                        >
                                            <div class="flex items-center gap-1.5 truncate pr-2">
                                                <template x-if="item.bmn_id">
                                                    <div class="flex items-center gap-1.5 truncate">
                                                        <template x-if="item.is_mine">
                                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 shrink-0">Saya</span>
                                                        </template>
                                                        <span class="font-medium text-[#132219] truncate" x-text="item.nama_barang"></span>
                                                        <span class="text-[#62756A] text-[11px] font-mono shrink-0" x-text="`(Kode: ${item.kode_barang || '-'} | NUP: ${item.nup || '-'})`"></span>
                                                    </div>
                                                </template>
                                                <template x-if="!item.bmn_id">
                                                    <span class="text-[#8D9D95]">Pilih Barang</span>
                                                </template>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <span
                                                    x-show="item.bmn_id"
                                                    @click.stop="clearBmn(index)"
                                                    class="p-0.5 text-[#8D9D95] hover:text-[#9C322C] rounded transition cursor-pointer"
                                                    title="Kosongkan pilihan"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </span>
                                                <svg class="w-4 h-4 text-[#8D9D95] transition-transform duration-200" :class="item.open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </button>

                                        {{-- Floating Dropdown Panel --}}
                                        <div
                                            x-show="item.open"
                                            @click.outside="item.open = false"
                                            @scroll.window="if (item.open) updateDropdownPos(index)"
                                            @resize.window="if (item.open) updateDropdownPos(index)"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            style="display: none;"
                                            class="fixed z-[9999] bg-white border border-[#D5E5DA] rounded-xl shadow-2xl overflow-hidden flex flex-col"
                                            :style="item.panelStyle"
                                        >
                                            <div class="p-2 border-b border-[#E3ECE6] bg-[#F7FAF8] shrink-0">
                                                <div class="relative">
                                                    <input
                                                        :id="`search-input-${index}`"
                                                        x-model="item.searchQuery"
                                                        type="text"
                                                        placeholder="Ketik nama, kode, atau NUP barang..."
                                                        class="w-full text-xs pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] text-[#132219] bg-white focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                                        @keydown.escape="item.open = false"
                                                        @keydown.enter.prevent="const fl = filterBmnList(item.searchQuery, item.bmn_id); if (fl.length > 0) selectBmn(index, fl[0])"
                                                    />
                                                    <svg class="w-3.5 h-3.5 text-[#8D9D95] absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                    <button
                                                        type="button"
                                                        x-show="item.searchQuery"
                                                        @click="item.searchQuery = ''; document.getElementById(`search-input-${index}`)?.focus()"
                                                        class="absolute right-2 top-2 text-[#8D9D95] hover:text-[#132219]"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="flex-1 max-h-56 overflow-y-auto divide-y divide-[#E3ECE6]/50 overscroll-contain">
                                                <template x-for="bmn in filterBmnList(item.searchQuery, item.bmn_id)" :key="bmn.id">
                                                    <div
                                                        @click="selectBmn(index, bmn)"
                                                        class="px-3.5 py-2.5 text-xs cursor-pointer hover:bg-[#EAF2ED] transition flex items-center justify-between gap-2"
                                                        :class="String(item.bmn_id) === String(bmn.id) ? 'bg-[#EAF2ED]/70 font-semibold text-[#163828]' : 'text-[#132219]'"
                                                    >
                                                        <div class="min-w-0">
                                                            <div class="flex items-center gap-1.5">
                                                                <template x-if="bmn.is_mine">
                                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Milik Saya</span>
                                                                </template>
                                                                <span class="font-medium text-[#132219] truncate" x-text="bmn.nama_barang"></span>
                                                            </div>
                                                            <div class="text-[11px] text-[#62756A] flex items-center gap-1.5 flex-wrap mt-0.5">
                                                                <span class="font-mono text-[#163828]" x-text="'Kode: ' + (bmn.kode_barang || '-')"></span>
                                                                <span>•</span>
                                                                <span class="font-mono text-[#163828]" x-text="'NUP: ' + (bmn.nup || '-')"></span>
                                                                <template x-if="bmn.merek">
                                                                    <span class="text-[#62756A]" x-text="'• ' + bmn.merek"></span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <svg x-show="String(item.bmn_id) === String(bmn.id)" class="w-4 h-4 text-[#163828] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                </template>
                                                <div x-show="filterBmnList(item.searchQuery, item.bmn_id).length === 0" class="p-4 text-center text-xs text-[#62756A]">
                                                    Tidak ditemukan barang dengan kata kunci "<span class="font-semibold" x-text="item.searchQuery"></span>".
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- MERK --}}
                                <td class="py-2 px-3 border-r border-[#E3ECE6]">
                                    <input
                                        type="text"
                                        :value="item.merek || '-'"
                                        readonly
                                        class="item-merek w-full bg-[#F7FAF8] border border-[#E3ECE6] text-[#4D6357] rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                                        placeholder=""
                                    />
                                </td>

                                {{-- NUP --}}
                                <td class="py-2 px-3 border-r border-[#E3ECE6]">
                                    <input
                                        type="text"
                                        :value="item.nup || '-'"
                                        readonly
                                        class="item-nup w-full bg-[#F7FAF8] border border-[#E3ECE6] text-[#4D6357] font-mono rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                                        placeholder=""
                                    />
                                </td>

                                {{-- KONDISI AWAL --}}
                                <td class="py-2 px-3 border-r border-[#E3ECE6]">
                                    <input
                                        type="text"
                                        :value="item.kondisi_awal || '-'"
                                        readonly
                                        class="item-kondisi-awal w-full bg-[#F7FAF8] border border-[#E3ECE6] text-[#4D6357] font-medium rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                                        placeholder="Kondisi awal"
                                    />
                                </td>

                                {{-- KONDISI PENGEMBALIAN --}}
                                <td class="py-2 px-3 border-r border-[#E3ECE6]">
                                    <input
                                        type="hidden"
                                        :name="`items[${index}][kondisi]`"
                                        :value="alasan === 'pengembalian_biasa' ? 'Baik' : 'Rusak Berat'"
                                    />
                                    <template x-if="alasan === 'pengembalian_biasa'">
                                        <input
                                            type="text"
                                            readonly
                                            value="Baik"
                                            class="w-full bg-[#F0FDF4] border border-[#DCFCE7] text-emerald-800 font-semibold rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                                        />
                                    </template>
                                    <template x-if="alasan === 'rusak_berat'">
                                        <input
                                            type="text"
                                            readonly
                                            value="Rusak Berat"
                                            class="w-full bg-[#FEF2F2] border border-[#FEE2E2] text-rose-800 font-semibold rounded-lg text-xs py-2 px-3 cursor-not-allowed select-none"
                                        />
                                    </template>
                                </td>

                                {{-- KETERANGAN --}}
                                <td class="py-2 px-3 border-r border-[#E3ECE6]">
                                    <input
                                        type="text"
                                        :name="`items[${index}][keterangan]`"
                                        x-model="item.keterangan"
                                        class="item-keterangan w-full bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 text-[#132219] placeholder-[#8D9D95] rounded-lg text-xs py-2 px-3 transition"
                                        placeholder="Keterangan (opsional)"
                                    />
                                </td>

                                {{-- AKSI --}}
                                <td class="py-2 px-3 text-center">
                                    <button
                                        type="button"
                                        @click="removeItem(index)"
                                        x-show="items.length > 1"
                                        class="btn-remove-row p-1.5 text-[#9C322C] hover:bg-[#FDF0EE] rounded-lg transition cursor-pointer"
                                        title="Hapus Baris"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>

                            {{-- SUB-BARIS UNTUK RUSAK BERAT --}}
                            <tr x-show="alasan === 'rusak_berat'" class="bg-[#FFF8F7] border-b border-[#F8D4D0]">
                                <td colspan="8" class="py-3 px-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="w-2 h-2 rounded-full bg-[#9C322C]"></span>
                                        <span class="text-xs font-bold text-[#9C322C] uppercase tracking-wide">Rincian Kerusakan Berat (Barang #<span x-text="index + 1"></span>)</span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                        <div class="md:col-span-4">
                                            <label class="block text-[11px] font-semibold text-[#9C322C] mb-1">
                                                Tanggal Temuan Kerusakan <span class="text-[#9C322C]">*</span>
                                            </label>
                                            <input
                                                type="date"
                                                :name="`items[${index}][tanggal_temuan]`"
                                                x-model="item.tanggal_temuan"
                                                max="{{ date('Y-m-d') }}"
                                                :required="alasan === 'rusak_berat'"
                                                class="w-full text-xs rounded-lg border border-[#F8D4D0] bg-white focus:border-[#9C322C] focus:ring-1 focus:ring-[#9C322C] py-2 px-3 text-[#132219]"
                                            />
                                        </div>
                                        <div class="md:col-span-8">
                                            <label class="block text-[11px] font-semibold text-[#9C322C] mb-1">
                                                Rincian & Gejala Kerusakan Berat <span class="text-[#9C322C]">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                :name="`items[${index}][deskripsi_kerusakan]`"
                                                x-model="item.deskripsi_kerusakan"
                                                placeholder="Contoh: Korsleting listrik, mati total, motherboard terbakar dan tidak ekonomis diperbaiki."
                                                :required="alasan === 'rusak_berat'"
                                                class="w-full text-xs rounded-lg border border-[#F8D4D0] bg-white focus:border-[#9C322C] focus:ring-1 focus:ring-[#9C322C] py-2 px-3 text-[#132219]"
                                            />
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </template>
                </table>
            </div>

            {{-- TOMBOL TAMBAH BARIS BARU --}}
            <div>
                <button
                    type="button"
                    @click="addItem()"
                    class="w-full py-2.5 bg-[#9AA9A1]/35 hover:bg-[#9AA9A1]/50 text-[#163828] border border-[#CBD7D0] rounded-xl text-xs font-bold tracking-wider uppercase transition flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>TAMBAH BARIS BARU</span>
                </button>
            </div>
        </div>

        {{-- CARD 4: CATATAN PENGAJUAN (OPSIONAL) --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-7 shadow-2xs space-y-3">
            <div class="flex items-center gap-2 pb-2 border-b border-[#E3ECE6]">
                <span class="w-2.5 h-2.5 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Catatan Pemohon (Opsional)</h2>
            </div>
            <textarea
                name="catatan_pemohon"
                rows="3"
                placeholder="Tuliskan keterangan tambahan atau pengantar bila ada..."
                class="w-full text-xs rounded-lg border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 p-3 text-[#132219] placeholder-[#8D9D95]"
            >{{ old('catatan_pemohon') }}</textarea>
        </div>

        {{-- CARD 5: TOMBOL AKSI / SUBMIT --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs flex items-center justify-between">
            <button
                type="submit"
                style="background-color: #163828 !important; color: #ffffff !important;"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-sm font-bold tracking-wide shadow-md transition cursor-pointer"
            >
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>Kirim Pengajuan</span>
            </button>

            <a
                href="{{ route('pengajuan-pengembalian.index') }}"
                class="px-5 py-2.5 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-xl text-xs font-semibold transition"
            >
                Batalkan
            </a>
        </div>
    </form>
</div>

@push('scripts')
@php
    $oldItems = old('items');
    $initialItemsList = [];
    if (!empty($oldItems) && is_array($oldItems)) {
        foreach ($oldItems as $it) {
            $bmnModel = !empty($it['bmn_id']) ? $initialBmns->firstWhere('id', $it['bmn_id']) : null;
            $initialItemsList[] = [
                'uid' => uniqid(),
                'bmn_id' => $it['bmn_id'] ?? '',
                'nama_barang' => $bmnModel?->nama_barang ?? '',
                'kode_barang' => (string)($bmnModel?->kode_barang ?? ''),
                'nup' => (string)($bmnModel?->nup ?? ''),
                'merek' => $bmnModel?->merek ?? '',
                'kondisi_awal' => $bmnModel?->kondisi ?: 'Baik',
                'is_mine' => (bool)($bmnModel?->is_mine ?? false),
                'open' => false,
                'panelStyle' => '',
                'searchQuery' => '',
                'tanggal_temuan' => $it['tanggal_temuan'] ?? date('Y-m-d'),
                'deskripsi_kerusakan' => $it['deskripsi_kerusakan'] ?? '',
                'keterangan' => $it['keterangan'] ?? '',
            ];
        }
    }
    if (empty($initialItemsList)) {
        $initialItemsList[] = [
            'uid' => uniqid(),
            'bmn_id' => '',
            'nama_barang' => '',
            'kode_barang' => '',
            'nup' => '',
            'merek' => '',
            'kondisi_awal' => '',
            'is_mine' => false,
            'open' => false,
            'panelStyle' => '',
            'searchQuery' => '',
            'tanggal_temuan' => date('Y-m-d'),
            'deskripsi_kerusakan' => '',
            'keterangan' => '',
        ];
    }
@endphp
<script>
function pengajuanForm() {
    const rawBmns = @json($initialBmns);

    return {
        alasan: '{{ old('alasan', 'pengembalian_biasa') }}',
        allBmns: rawBmns || [],
        items: @json($initialItemsList),

        setAlasan(val) {
            this.alasan = val;
            fetch(`{{ route('pengajuan-pengembalian.api-search-bmn') }}?alasan=${val}`)
                .then(res => res.json())
                .then(data => {
                    this.allBmns = data;
                })
                .catch(err => console.error(err));
        },

        addItem() {
            this.items.push({
                uid: Date.now() + '-' + Math.random(),
                bmn_id: '',
                nama_barang: '',
                kode_barang: '',
                nup: '',
                merek: '',
                kondisi_awal: '',
                is_mine: false,
                open: false,
                panelStyle: '',
                searchQuery: '',
                tanggal_temuan: '{{ date('Y-m-d') }}',
                deskripsi_kerusakan: '',
                keterangan: ''
            });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },

        toggleDropdown(index, triggerEl) {
            const wasOpen = this.items[index].open;
            this.items.forEach(i => i.open = false);
            this.items[index].open = !wasOpen;

            if (this.items[index].open) {
                this.updateDropdownPos(index, triggerEl);
                this.$nextTick(() => {
                    this.updateDropdownPos(index, triggerEl);
                    const input = document.getElementById(`search-input-${index}`);
                    if (input) input.focus();
                });
            } else {
                this.items[index].searchQuery = '';
            }
        },

        updateDropdownPos(index, el) {
            const trigger = el || document.getElementById(`bmn-trigger-${index}`);
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

            this.items[index].panelStyle = `top: ${top}px; left: ${left}px; width: ${width}px; max-height: ${panelHeight}px;`;
        },

        filterBmnList(query, currentId) {
            const selectedIds = this.items.map(i => String(i.bmn_id)).filter(id => id && id !== String(currentId));
            const q = (query || '').toLowerCase().trim();

            return this.allBmns.filter(b => {
                if (selectedIds.includes(String(b.id))) {
                    return false;
                }
                if (!q) return true;
                const nama = (b.nama_barang || '').toLowerCase();
                const kode = (b.kode_barang || '').toLowerCase();
                const nup = String(b.nup || '').toLowerCase();
                const merek = (b.merek || '').toLowerCase();
                return nama.includes(q) || kode.includes(q) || nup.includes(q) || merek.includes(q);
            });
        },

        selectBmn(index, bmn) {
            const target = this.items[index];
            target.bmn_id = bmn.id;
            target.nama_barang = bmn.nama_barang;
            target.kode_barang = bmn.kode_barang || '';
            target.nup = bmn.nup || '';
            target.merek = bmn.merek || '';
            target.kondisi_awal = bmn.kondisi || 'Baik';
            target.is_mine = bmn.is_mine || false;
            target.open = false;
            target.searchQuery = '';
        },

        clearBmn(index) {
            const target = this.items[index];
            target.bmn_id = '';
            target.nama_barang = '';
            target.kode_barang = '';
            target.nup = '';
            target.merek = '';
            target.kondisi_awal = '';
            target.is_mine = false;
            target.open = false;
            target.searchQuery = '';
            target.keterangan = '';
            target.deskripsi_kerusakan = '';
        },

        handleSubmit(e) {
            const emptyItems = this.items.filter(i => !i.bmn_id);
            if (emptyItems.length > 0) {
                e.preventDefault();
                alert('Silakan pilih barang BMN pada setiap baris item yang diajukan.');
                return false;
            }

            if (this.alasan === 'rusak_berat') {
                for (let i = 0; i < this.items.length; i++) {
                    const it = this.items[i];
                    if (!it.tanggal_temuan || !it.deskripsi_kerusakan || it.deskripsi_kerusakan.trim().length < 5) {
                        e.preventDefault();
                        alert(`Baris #${i + 1}: Untuk usulan rusak berat, tanggal temuan dan rincian kerusakan (minimal 5 karakter) wajib diisi.`);
                        return false;
                    }
                }
            }

            return confirm('Apakah Anda yakin data pengajuan pengembalian ini sudah benar dan siap diajukan?');
        }
    };
}
</script>
@endpush
@endsection
