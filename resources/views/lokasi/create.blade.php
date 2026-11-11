@extends('layouts.app')

@section('title', 'Tambah Lokasi — SIBOB')
@section('page-title', 'Tambah Lokasi')

@section('content')
<div class="space-y-6 max-w-xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('lokasis.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Master Lokasi</span>
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Tambah Lokasi Baru</h1>
        <p class="text-xs text-[#62756A] mt-0.5">Daftarkan nama ruangan atau lokasi penempatan aset inventaris.</p>
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
    <form
        method="POST"
        action="{{ route('lokasis.store') }}"
        x-data="{ kode: '{{ old('kode_ruangan', '') }}', nama: '{{ old('nama', '') }}' }"
        class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6"
    >
        @csrf

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Data Ruangan / Lokasi</h2>
        </div>

        <div class="space-y-4">
            <div>
                <div class="flex items-center justify-between mb-1.5 flex-wrap gap-1">
                    <x-input-label for="kode_ruangan" value="Kode Ruangan (Opsional)" />
                    <div class="flex items-center gap-1 flex-wrap">
                        <span class="text-[10px] text-[#62756A] mr-0.5">Pilih Kelompok:</span>
                        <div class="inline-flex rounded-md shadow-2xs border border-[#D5E5DA] overflow-hidden text-[10px]">
                            <button type="button" @click="kode = 'A1.'" class="px-1.5 py-0.5 font-semibold bg-[#EAF2ED] text-[#163828] hover:bg-[#D5E5DA]" title="Gedung A Lantai 1">A1</button>
                            <button type="button" @click="kode = 'A2.'" class="px-1.5 py-0.5 font-semibold bg-[#EAF2ED] text-[#163828] hover:bg-[#D5E5DA] border-l border-[#D5E5DA]" title="Gedung A Lantai 2">A2</button>
                        </div>
                        <div class="inline-flex rounded-md shadow-2xs border border-[#D5E5DA] overflow-hidden text-[10px]">
                            <button type="button" @click="kode = 'B1.'" class="px-1.5 py-0.5 font-semibold bg-[#EAF2ED] text-[#163828] hover:bg-[#D5E5DA]" title="Gedung B Lantai 1">B1</button>
                            <button type="button" @click="kode = 'B2.'" class="px-1.5 py-0.5 font-semibold bg-[#EAF2ED] text-[#163828] hover:bg-[#D5E5DA] border-l border-[#D5E5DA]" title="Gedung B Lantai 2">B2</button>
                            <button type="button" @click="kode = 'B.BS.'" class="px-1.5 py-0.5 font-semibold bg-[#EAF2ED] text-[#163828] hover:bg-[#D5E5DA] border-l border-[#D5E5DA]" title="Gedung B Basement">B.BS</button>
                        </div>
                        <div class="inline-flex rounded-md shadow-2xs border border-[#D5E5DA] overflow-hidden text-[10px]">
                            <button type="button" @click="kode = 'C1.'" class="px-1.5 py-0.5 font-semibold bg-[#EAF2ED] text-[#163828] hover:bg-[#D5E5DA]" title="Gedung C Lantai 1">C1</button>
                            <button type="button" @click="kode = 'C2.'" class="px-1.5 py-0.5 font-semibold bg-[#EAF2ED] text-[#163828] hover:bg-[#D5E5DA] border-l border-[#D5E5DA]" title="Gedung C Lantai 2">C2</button>
                            <button type="button" @click="kode = 'C.Bs.'" class="px-1.5 py-0.5 font-semibold bg-[#EAF2ED] text-[#163828] hover:bg-[#D5E5DA] border-l border-[#D5E5DA]" title="Gedung C Basement">C.Bs</button>
                        </div>
                    </div>
                </div>
                <x-text-input
                    id="kode_ruangan"
                    name="kode_ruangan"
                    type="text"
                    class="w-full text-xs font-medium uppercase font-mono"
                    x-model="kode"
                    placeholder="mis. A1.36 / B1.14 / C1.18"
                    autofocus
                />
                <p class="text-[11px] text-[#62756A] mt-1.5">
                    Ruangan berkode <strong>A1</strong> akan otomatis masuk urutan A1, <strong>A2</strong> di urutan A2, <strong>B1/B2</strong> di urutan B1/B2, <strong>C1/C2</strong> di urutan C1/C2, dan <strong>Basement</strong> di urutan Basement.
                </p>
                <x-input-error :messages="$errors->get('kode_ruangan')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="nama" value="Nama Lokasi *" />
                <x-text-input
                    id="nama"
                    name="nama"
                    type="text"
                    class="w-full text-xs font-medium"
                    x-model="nama"
                    placeholder="mis. Ruang Tata Usaha / Laboratorium Pengujian Kimia"
                    required
                />
                <p class="text-[11px] text-[#62756A] mt-1.5">
                    Gunakan penamaan yang konsisten dan mudah dikenali saat pengisian formulir BMN.
                </p>
                <x-input-error :messages="$errors->get('nama')" class="mt-1" />
            </div>

            {{-- PERINGATAN KODE & NAMA SAMA --}}
            <div
                x-show="kode.trim() !== '' && nama.trim() !== '' && kode.trim().toLowerCase() === nama.trim().toLowerCase()"
                class="p-3 rounded-xl border border-[#F8D4D0] bg-[#FDF0EE] text-[#9C322C] text-xs flex items-center gap-2"
                style="display: none;"
            >
                <svg class="w-4 h-4 shrink-0 text-[#9C322C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Kode ruangan dan nama lokasi tidak boleh sama.</span>
            </div>

            {{-- FORMAT KOMBINASI PREVIEW --}}
            <div class="p-3.5 bg-[#F2F6F3] rounded-xl border border-[#D1E0D7] space-y-1.5">
                <span class="text-[11px] font-semibold text-[#62756A] block">Format Kombinasi (Pencarian):</span>
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-mono font-semibold bg-white border border-[#D1E0D7] text-[#163828] shadow-2xs">
                    <span x-text="kode.trim() !== '' ? (kode.trim() + ' - ') : ''"></span>
                    <span x-text="nama.trim() !== '' ? nama.trim() : '-'">-</span>
                </div>
                <p class="text-[10px] text-[#62756A]">
                    Format kombinasi <strong>Kode Ruangan - Nama Lokasi</strong> akan otomatis dibentuk dan diurutkan sesuai kelompok gedung.
                </p>
            </div>
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
            <a
                href="{{ route('lokasis.index') }}"
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
                <span>Simpan Lokasi</span>
            </button>
        </div>
    </form>

</div>
@endsection