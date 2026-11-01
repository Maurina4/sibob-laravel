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
        <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Formulir Pengajuan Pengembalian BMN</h1>
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
                    <div class="mt-3 ml-6.5 text-[10px] text-emerald-700 bg-emerald-50 px-2.5 py-1.5 rounded-lg border border-emerald-200/60">
                        ✓ Berita Acara: <strong>BA-PGM/...</strong> • Masuk kembali ke Gudang BMN.
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
                    <div class="mt-3 ml-6.5 text-[10px] text-rose-700 bg-rose-50 px-2.5 py-1.5 rounded-lg border border-rose-200/60">
                        ✓ Berita Acara: <strong>BA-RB/...</strong> • Otomatis menerbitkan Laporan Kerusakan untuk usulan penghapusan.
                    </div>
                </label>
            </div>
        </div>

        {{-- CARD 3: DAFTAR BARANG YANG DIAJUKAN --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-7 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-[#E3ECE6]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#163828]"></span>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Daftar Barang BMN <span class="text-[#9C322C]">*</span></h2>
                </div>
                <div class="text-[11px] text-[#62756A] flex items-center gap-2">
                    <span>Total barang dipilih: <strong class="text-[#132219]" x-text="items.length"></strong></span>
                </div>
            </div>

            {{-- DAFTAR BARANG LOOP --}}
            <div class="space-y-4">
                <template x-for="(item, index) in items" :key="item.uid">
                    <div class="p-4 rounded-xl border border-[#E3ECE6] bg-[#FAFCFA] space-y-3 relative transition hover:border-[#163828]/30">
                        {{-- BARIS 1: NOMOR, PILIH BARANG, KONDISI & HAPUS --}}
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                            {{-- INDEX BADGE --}}
                            <div class="md:col-span-1 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-[#163828] text-white text-[11px] font-bold flex items-center justify-center" x-text="index + 1"></span>
                                <span class="text-xs font-semibold text-[#132219] md:hidden">Barang #<span x-text="index + 1"></span></span>
                            </div>

                            {{-- SELECTOR SEARCHABLE BARANG --}}
                            <div class="md:col-span-6 relative">
                                <label class="block text-[11px] font-semibold text-[#4D6357] mb-1 md:hidden">Pilih BMN</label>
                                <div class="relative">
                                    {{-- Hidden input for form submission --}}
                                    <input
                                        type="hidden"
                                        :name="`items[${index}][bmn_id]`"
                                        :value="item.bmn_id"
                                    />
                                    <input
                                        type="hidden"
                                        :name="`items[${index}][kondisi]`"
                                        :value="alasan === 'pengembalian_biasa' ? 'Baik' : 'Rusak Berat'"
                                    />

                                    {{-- Trigger Dropdown --}}
                                    <button
                                        type="button"
                                        @click="toggleDropdown(index)"
                                        class="w-full flex items-center justify-between text-left text-xs px-3 py-2 rounded-lg border border-[#E3ECE6] bg-white hover:border-[#163828] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                        :class="item.open ? 'border-[#163828] ring-1 ring-[#163828]' : ''"
                                    >
                                        <div class="truncate pr-2">
                                            <template x-if="item.bmn_id">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <template x-if="item.is_mine">
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Saya</span>
                                                    </template>
                                                    <span class="font-medium text-[#132219] truncate" x-text="item.nama_barang"></span>
                                                    <span class="text-[#62756A] text-[11px] font-mono" x-text="`(${item.kode_barang} / ${item.nup})`"></span>
                                                </div>
                                            </template>
                                            <template x-if="!item.bmn_id">
                                                <span class="text-[#8D9D95]">-- Cari & Pilih Barang BMN --</span>
                                            </template>
                                        </div>
                                        <svg class="w-4 h-4 text-[#8D9D95] shrink-0 transition-transform" :class="item.open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    {{-- DROPDOWN PANEL --}}
                                    <div
                                        x-show="item.open"
                                        @click.outside="item.open = false"
                                        class="absolute z-50 left-0 right-0 mt-1 bg-white border border-[#D5E5DA] rounded-xl shadow-xl overflow-hidden flex flex-col"
                                        style="display: none;"
                                    >
                                        {{-- Search box in dropdown --}}
                                        <div class="p-2 border-b border-[#E3ECE6] bg-[#F7FAF8]">
                                            <input
                                                type="text"
                                                x-model="item.searchQuery"
                                                placeholder="Ketik nama, kode, atau NUP..."
                                                class="w-full text-xs pl-3 pr-3 py-1.5 rounded-lg border border-[#E3ECE6] text-[#132219] bg-white focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828]"
                                                @click.stop
                                            />
                                        </div>

                                        {{-- List items --}}
                                        <div class="max-h-56 overflow-y-auto divide-y divide-[#E3ECE6]/60 overscroll-contain">
                                            <template x-for="bmn in filterBmnList(item.searchQuery, item.bmn_id)" :key="bmn.id">
                                                <div
                                                    @click="selectBmn(index, bmn)"
                                                    class="px-3 py-2 text-xs cursor-pointer hover:bg-[#EAF2ED] transition flex items-center justify-between gap-2"
                                                    :class="item.bmn_id == bmn.id ? 'bg-[#EAF2ED] font-semibold text-[#163828]' : 'text-[#132219]'"
                                                >
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-1.5">
                                                            <template x-if="bmn.is_mine">
                                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Milik Saya</span>
                                                            </template>
                                                            <span class="font-medium text-[#132219] truncate" x-text="bmn.nama_barang"></span>
                                                        </div>
                                                        <div class="text-[10px] text-[#62756A] flex items-center gap-2 mt-0.5">
                                                            <span class="font-mono text-[#163828]" x-text="'Kode: ' + (bmn.kode_barang || '-')"></span>
                                                            <span>•</span>
                                                            <span class="font-mono text-[#163828]" x-text="'NUP: ' + (bmn.nup || '-')"></span>
                                                            <span x-text="'• ' + (bmn.merek || '-')"></span>
                                                        </div>
                                                    </div>
                                                    <svg x-show="item.bmn_id == bmn.id" class="w-4 h-4 text-[#163828] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </template>
                                            <div x-show="filterBmnList(item.searchQuery, item.bmn_id).length === 0" class="p-3 text-center text-xs text-[#62756A]">
                                                Tidak ada barang yang cocok.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- MERK & NUP INFO --}}
                            <div class="md:col-span-3 text-xs text-[#4D6357]">
                                <div class="flex md:flex-col justify-between text-[11px] gap-1">
                                    <span>Merk: <strong class="text-[#132219]" x-text="item.merek || '-'"></strong></span>
                                    <span>NUP: <strong class="font-mono text-[#132219]" x-text="item.nup || '-'"></strong></span>
                                </div>
                            </div>

                            {{-- KONDISI BADGE & HAPUS --}}
                            <div class="md:col-span-2 flex items-center justify-between md:justify-end gap-2">
                                <template x-if="alasan === 'pengembalian_biasa'">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        Baik
                                    </span>
                                </template>
                                <template x-if="alasan === 'rusak_berat'">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">
                                        Rusak Berat
                                    </span>
                                </template>

                                <button
                                    type="button"
                                    @click="removeItem(index)"
                                    x-show="items.length > 1"
                                    class="p-1.5 text-[#9C322C] hover:bg-[#FDF0EE] rounded-lg transition"
                                    title="Hapus baris"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- SUB-FORM KONDISI RUSAK BERAT (TANGGAL TEMUAN & DESKRIPSI) --}}
                        <div x-show="alasan === 'rusak_berat'" class="pt-3 border-t border-[#E3ECE6] grid grid-cols-1 md:grid-cols-12 gap-3 bg-white p-3 rounded-lg border">
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
                                    class="w-full text-xs rounded-lg border border-[#E3ECE6] focus:border-[#9C322C] focus:ring-1 focus:ring-[#9C322C] py-1.5 px-2.5 text-[#132219]"
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
                                    class="w-full text-xs rounded-lg border border-[#E3ECE6] focus:border-[#9C322C] focus:ring-1 focus:ring-[#9C322C] py-1.5 px-2.5 text-[#132219]"
                                />
                            </div>
                        </div>

                        {{-- KETERANGAN OPSIONAL --}}
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                            <div class="md:col-span-12">
                                <input
                                    type="text"
                                    :name="`items[${index}][keterangan]`"
                                    x-model="item.keterangan"
                                    placeholder="Catatan / keterangan tambahan untuk item ini (opsional)"
                                    class="w-full text-[11px] rounded-lg border border-[#E3ECE6] focus:border-[#163828] focus:ring-1 focus:ring-[#163828] py-1.5 px-2.5 text-[#132219] bg-white placeholder-[#8D9D95]"
                                />
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- TOMBOL TAMBAH BARIS --}}
            <div class="pt-2">
                <button
                    type="button"
                    @click="addItem()"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-[#163828] text-[#163828] bg-white hover:bg-[#F7FAF8] text-xs font-semibold transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Barang Lainnya</span>
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

        {{-- ACTIONS FOOTER --}}
        <div class="flex items-center justify-between pt-2">
            <a
                href="{{ route('pengajuan-pengembalian.index') }}"
                class="px-4 py-2.5 rounded-xl border border-[#E3ECE6] text-xs font-semibold text-[#62756A] hover:bg-[#F7FAF8] hover:text-[#132219] transition"
            >
                Batal
            </a>
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#163828] hover:bg-[#0E281C] text-white text-xs font-semibold shadow-xs transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Kirim Pengajuan Pengembalian</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function pengajuanForm() {
    const rawBmns = @json($initialBmns);

    return {
        alasan: '{{ old('alasan', 'pengembalian_biasa') }}',
        allBmns: rawBmns || [],
        items: [
            {
                uid: Date.now() + '-' + Math.random(),
                bmn_id: '',
                nama_barang: '',
                kode_barang: '',
                nup: '',
                merek: '',
                kondisi_awal: '',
                is_mine: false,
                open: false,
                searchQuery: '',
                tanggal_temuan: '{{ date('Y-m-d') }}',
                deskripsi_kerusakan: '',
                keterangan: ''
            }
        ],

        setAlasan(val) {
            this.alasan = val;
            // Fetch updated list of available BMNs from API
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

        toggleDropdown(index) {
            const current = this.items[index].open;
            this.items.forEach(i => i.open = false);
            this.items[index].open = !current;
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
            target.kode_barang = bmn.kode_barang;
            target.nup = bmn.nup;
            target.merek = bmn.merek;
            target.kondisi_awal = bmn.kondisi || 'Baik';
            target.is_mine = bmn.is_mine;
            target.open = false;
            target.searchQuery = '';
        },

        handleSubmit(e) {
            // Validasi client-side: pastikan minimal 1 bmn terpilih
            const emptyItems = this.items.filter(i => !i.bmn_id);
            if (emptyItems.length > 0) {
                e.preventDefault();
                alert('Silakan pilih barang BMN pada setiap baris item yang diajukan.');
                return false;
            }

            // Validasi rusak berat: pastikan tanggal dan deskripsi terisi
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
