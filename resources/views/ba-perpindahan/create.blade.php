@extends('layouts.app')

@section('title', 'Ajukan BA Perpindahan — SIBOB')
@section('page-title', 'Ajukan BA Perpindahan')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('ba-perpindahan.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar BA Perpindahan</span>
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Pengajuan Berita Acara Perpindahan</h1>
        <p class="text-xs text-[#62756A] mt-0.5">Ajukan mutasi atau pemindahan barang BMN antar ruangan atau pemegang aset.</p>
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
    <form method="POST" action="{{ route('ba-perpindahan.store') }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf

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
                    :value="old('nomor_ba', 'BA-MUT/' . date('Ymd') . '/' . rand(100, 999))"
                    placeholder="mis. BA-MUT/20261008/001"
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
                    :value="old('tanggal', date('Y-m-d'))"
                    required
                />
            </div>
        </div>

        {{-- NAMA BARANG --}}
        <div>
            <x-input-label for="nama_barang" value="Nama Barang BMN *" />
            <x-text-input
                id="nama_barang"
                name="nama_barang"
                type="text"
                class="w-full text-xs font-medium"
                :value="old('nama_barang')"
                placeholder="mis. Kursi Kerja Putar / Laptop Asus TUF / AC Split 2PK"
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
                    class="w-full text-xs"
                    :value="old('dari')"
                    placeholder="mis. Ruang Tata Usaha"
                    required
                />
            </div>

            {{-- KE --}}
            <div>
                <x-input-label for="ke" value="Lokasi Tujuan (Ke)" />
                <x-text-input
                    id="ke"
                    name="ke"
                    type="text"
                    class="w-full text-xs"
                    :value="old('ke')"
                    placeholder="mis. Ruang Pengujian Mikrobiologi"
                />
            </div>
        </div>

        {{-- PIHAK KEDUA / PENERIMA --}}
        <div>
            <x-input-label for="pihak_kedua" value="Pihak Kedua (Pegawai Penerima Baru)" />
            <input
                id="pihak_kedua"
                name="pihak_kedua"
                type="text"
                list="pegawai_pihak_kedua_list"
                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                value="{{ old('pihak_kedua') }}"
                placeholder="Pilih atau ketik: Nama (NIP Pegawai)"
            />
            <datalist id="pegawai_pihak_kedua_list">
                @foreach(\App\Models\Pegawai::orderBy('nama')->get() as $p)
                    <option value="{{ $p->nip ? "{$p->nama} ({$p->nip})" : $p->nama }}"></option>
                @endforeach
            </datalist>
        </div>

        {{-- ALASAN --}}
        <div>
            <x-input-label for="alasan" value="Alasan Perpindahan *" />
            <textarea
                id="alasan"
                name="alasan"
                rows="3"
                placeholder="Jelaskan alasan pemindahan (mis. reorganisasi ruangan, peremajaan alat, penugasan baru)..."
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
                required
            >{{ old('alasan') }}</textarea>
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
            <a
                href="{{ route('ba-perpindahan.index') }}"
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
                <span>Kirim Pengajuan</span>
            </button>
        </div>
    </form>

</div>
@endsection
