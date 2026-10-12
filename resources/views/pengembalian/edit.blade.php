@extends('layouts.app')

@section('title', 'Ubah Pengembalian — ' . $pengembalian->nomor_pengajuan)
@section('page-title', 'Ubah Pengembalian')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

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
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Ubah Pengembalian BMN</h1>
            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                {{ $pengembalian->nomor_pengajuan }}
            </span>
        </div>
        <p class="text-xs text-[#62756A] mt-0.5">Perbarui dokumen penyerahan kembali aset milik negara.</p>
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
    <form method="POST" action="{{ route('pengembalians.update', $pengembalian) }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Informasi Pengembalian</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- NOMOR PENGAJUAN --}}
            <div>
                <x-input-label for="nomor_pengajuan" value="Nomor Pengajuan *" />
                <x-text-input
                    id="nomor_pengajuan"
                    name="nomor_pengajuan"
                    type="text"
                    class="w-full font-mono text-xs"
                    :value="old('nomor_pengajuan', $pengembalian->nomor_pengajuan)"
                    required
                />
            </div>

            {{-- TANGGAL --}}
            <div>
                <x-input-label for="tanggal" value="Tanggal Pengembalian *" />
                <x-text-input
                    id="tanggal"
                    name="tanggal"
                    type="date"
                    class="w-full text-xs"
                    :value="old('tanggal', optional($pengembalian->tanggal)->format('Y-m-d'))"
                    required
                />
            </div>
        </div>

        {{-- NAMA PENGAJU --}}
        <div>
            <x-input-label for="nama_pengaju" value="Nama Pegawai Pengaju *" />
            <select
                id="nama_pengaju"
                name="nama_pengaju"
                required
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 px-3 transition cursor-pointer"
            >
                <option value="">Pilih Nama Pegawai</option>
                @foreach ($pegawais as $pegawai)
                    @php
                        $pjFormatted = $pegawai->nip ? "{$pegawai->nama} ({$pegawai->nip})" : $pegawai->nama;
                    @endphp
                    <option value="{{ $pjFormatted }}" @selected(old('nama_pengaju', $pengembalian->nama_pengaju) === $pjFormatted || old('nama_pengaju', $pengembalian->nama_pengaju) === $pegawai->nama)>
                        {{ $pjFormatted }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- BIDANG --}}
        <div>
            <x-input-label for="bidang" value="Bidang / Unit Kerja *" />
            <select
                id="bidang"
                name="bidang"
                required
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 px-3 transition cursor-pointer"
            >
                <option value="">Pilih Bidang</option>
                @foreach ($bidangs as $bidang)
                    <option value="{{ $bidang }}" @selected(old('bidang', $pengembalian->bidang) === $bidang)>
                        {{ $bidang }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
            <a
                href="{{ route('pengembalians.index') }}"
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
