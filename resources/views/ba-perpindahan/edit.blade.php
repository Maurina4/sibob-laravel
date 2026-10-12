@extends('layouts.app')

@section('title', 'Ubah BA Perpindahan — ' . ($baPerpindahan->nomor_ba ?: $baPerpindahan->id))
@section('page-title', 'Ubah BA Perpindahan')

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
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Ubah BA Perpindahan</h1>
            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                {{ $baPerpindahan->nomor_ba ?: 'BA-MUT/' . $baPerpindahan->id }}
            </span>
        </div>
        <p class="text-xs text-[#62756A] mt-0.5">Perbarui rincian permohonan mutasi aset BMN.</p>
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
    <form method="POST" action="{{ route('ba-perpindahan.update', $baPerpindahan) }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf
        @method('PUT')

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
                    :value="old('nomor_ba', $baPerpindahan->nomor_ba)"
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
                    :value="old('tanggal', optional($baPerpindahan->tanggal)->format('Y-m-d'))"
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
                :value="old('nama_barang', $baPerpindahan->nama_barang)"
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
                    :value="old('dari', $baPerpindahan->dari)"
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
                    :value="old('ke', $baPerpindahan->ke)"
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
                list="pegawai_pihak_kedua_list_edit"
                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                value="{{ old('pihak_kedua', $baPerpindahan->pihak_kedua) }}"
                placeholder="Pilih atau ketik: Nama (NIP Pegawai)"
            />
            <datalist id="pegawai_pihak_kedua_list_edit">
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
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
                required
            >{{ old('alasan', $baPerpindahan->alasan) }}</textarea>
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
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>

</div>
@endsection
