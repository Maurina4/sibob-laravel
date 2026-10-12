@extends('layouts.app')

@section('title', 'Laporkan Kerusakan — SIBOB')
@section('page-title', 'Buat Laporan Kerusakan')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('laporan-kerusakan.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Laporan</span>
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Form Pelaporan Kerusakan BMN</h1>
        <p class="text-xs text-[#62756A] mt-0.5">Sampaikan keluhan kendala teknis atau kerusakan barang inventaris kepada tim pengelola BMN.</p>
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
    <form method="POST" action="{{ route('laporan-kerusakan.store') }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Rincian Kerusakan Barang</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- NOMOR LAPORAN --}}
            <div>
                <x-input-label for="nomor" value="Nomor Laporan *" />
                <x-text-input
                    id="nomor"
                    name="nomor"
                    type="text"
                    class="w-full font-mono text-xs"
                    :value="old('nomor', 'LK/' . date('Ymd') . '/' . rand(100, 999))"
                    required
                />
            </div>

            {{-- TANGGAL --}}
            <div>
                <x-input-label for="tanggal" value="Tanggal Temuan Kerusakan *" />
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
            <x-input-label for="nama_barang" value="Nama Barang BMN Yang Rusak *" />
            <x-text-input
                id="nama_barang"
                name="nama_barang"
                type="text"
                class="w-full text-xs font-medium"
                :value="old('nama_barang')"
                placeholder="mis. Komputer PC All-in-One / Printer Laser / AC Ruang Rapat"
                required
            />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- LOKASI --}}
            <div>
                <x-input-label for="lokasi" value="Lokasi / Ruangan Barang Berada *" />
                <x-text-input
                    id="lokasi"
                    name="lokasi"
                    type="text"
                    class="w-full text-xs"
                    :value="old('lokasi')"
                    placeholder="mis. Ruang Pengujian Pangan / Lt. 2 TU"
                    required
                />
            </div>

            {{-- TINGKAT KERUSAKAN --}}
            <div>
                <x-input-label for="tingkat" value="Tingkat Kerusakan *" />
                <select
                    id="tingkat"
                    name="tingkat"
                    required
                    class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 px-3 transition cursor-pointer"
                >
                    <option value="Ringan" {{ old('tingkat') === 'Ringan' ? 'selected' : '' }}>Ringan (Masih bisa digunakan dengan kendala)</option>
                    <option value="Sedang" {{ old('tingkat') === 'Sedang' ? 'selected' : '' }}>Sedang (Fungsi utama terganggu)</option>
                    <option value="Berat" {{ old('tingkat') === 'Berat' ? 'selected' : '' }}>Berat (Mati total / tidak dapat beroperasi)</option>
                </select>
            </div>
        </div>

        {{-- DESKRIPSI KERUSAKAN --}}
        <div>
            <x-input-label for="deskripsi" value="Deskripsi Kerusakan & Kronologi *" />
            <textarea
                id="deskripsi"
                name="deskripsi"
                rows="4"
                placeholder="Jelaskan secara rinci gejala kerusakan yang dialami, indikator eror, atau kronologi kejadian..."
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
                required
            >{{ old('deskripsi') }}</textarea>
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
            <a
                href="{{ route('laporan-kerusakan.index') }}"
                class="px-4 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-xl text-xs font-semibold transition"
            >
                Batalkan
            </a>
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Kirim Laporan Kerusakan</span>
            </button>
        </div>
    </form>

</div>
@endsection
