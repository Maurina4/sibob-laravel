@extends('layouts.app')

@section('title', 'Tanggapi Laporan — ' . $laporanKerusakan->nomor)
@section('page-title', 'Tanggapi Laporan Kerusakan')

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
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Tindak Lanjut Kerusakan</h1>
            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                {{ $laporanKerusakan->nomor }}
            </span>
        </div>
        <p class="text-xs text-[#62756A] mt-0.5">Berikan tanggapan teknis dan perbarui status perbaikan barang inventaris.</p>
    </div>

    {{-- RINGKASAN LAPORAN ASAL --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] p-5 shadow-2xs space-y-3">
        <div class="flex items-center justify-between pb-3 border-b border-[#E3ECE6]/70">
            <div>
                <div class="text-[10px] uppercase font-bold tracking-wider text-[#62756A]">Barang Yang Dilaporkan</div>
                <div class="text-sm font-bold text-[#132219]">{{ $laporanKerusakan->nama_barang }}</div>
            </div>
            <div class="text-right">
                <div class="text-[10px] uppercase font-bold tracking-wider text-[#62756A]">Pelapor</div>
                <div class="text-xs font-semibold text-[#132219]">{{ $laporanKerusakan->pelapor_nama }}</div>
            </div>
        </div>
        <div class="text-xs text-[#4D6357] bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
            <strong>Keluhan:</strong> {{ $laporanKerusakan->deskripsi }}
        </div>
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

    {{-- FORM TANGGAPAN OPERATOR --}}
    <form method="POST" action="{{ route('laporan-kerusakan.update', $laporanKerusakan) }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Tanggapan & Solusi Teknisi / Operator</h2>
        </div>

        {{-- STATUS --}}
        <div>
            <x-input-label for="status" value="Status Penanganan *" />
            <select
                id="status"
                name="status"
                required
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 px-3 transition cursor-pointer"
            >
                <option value="Dilaporkan" @selected(old('status', $laporanKerusakan->status) === 'Dilaporkan')>Dilaporkan (Menunggu antrean perbaikan)</option>
                <option value="Diproses" @selected(old('status', $laporanKerusakan->status) === 'Diproses')>Diproses (Sedang dalam perbaikan teknisi)</option>
                <option value="Selesai" @selected(old('status', $laporanKerusakan->status) === 'Selesai')>Selesai (Sudah diperbaiki / selesai ditangani)</option>
            </select>
        </div>

        {{-- TANGGAPAN --}}
        <div>
            <x-input-label for="tanggapan" value="Tanggapan / Catatan Hasil Penanganan" />
            <textarea
                id="tanggapan"
                name="tanggapan"
                rows="4"
                placeholder="Tuliskan tindakan perbaikan yang telah dilakukan, penggantian suku cadang, atau rekomendasi teknis..."
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
            >{{ old('tanggapan', $laporanKerusakan->tanggapan) }}</textarea>
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>Simpan Tindak Lanjut</span>
            </button>
        </div>
    </form>

</div>
@endsection
