@extends('layouts.app')

@section('title', 'Ubah BMN Lab — ' . $bmnsLab->nama_barang)
@section('page-title', 'Ubah BMN Lab')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('bmns-lab.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar BMN Lab</span>
        </a>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Ubah Data BMN Lab</h1>
            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                {{ $bmnsLab->kode_barang }}-NUP.{{ $bmnsLab->nup }}
            </span>
        </div>
        <p class="text-xs text-[#62756A] mt-0.5">Perbarui rincian instrumen: <span class="font-medium text-[#132219]">{{ $bmnsLab->nama_barang }}</span></p>
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
    <form method="POST" action="{{ route('bmns-lab.update', ['bmnLab' => $bmnsLab->id]) }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Rincian Instrumen Laboratorium</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- KODE BARANG --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <x-input-label for="kode_barang" value="Kode Barang *" class="!mb-0" />
                    <button
                        type="button"
                        id="ubahKodeBtn"
                        onclick="toggleKode()"
                        class="text-[11px] text-[#163828] font-medium hover:underline cursor-pointer"
                    >
                        Ubah Kode
                    </button>
                </div>
                <x-text-input
                    id="kode_barang"
                    name="kode_barang"
                    type="text"
                    class="w-full font-mono text-xs bg-[#F7FAF8]"
                    :value="old('kode_barang', $bmnsLab->kode_barang)"
                    readonly
                    required
                />
            </div>

            {{-- NUP --}}
            <div>
                <x-input-label for="nup" value="NUP *" />
                <x-text-input
                    id="nup"
                    name="nup"
                    type="number"
                    min="1"
                    class="w-full font-mono text-xs"
                    :value="old('nup', $bmnsLab->nup)"
                    required
                />
            </div>
        </div>

        {{-- NAMA BARANG --}}
        <div>
            <x-input-label for="nama_barang" value="Nama Alat Laboratorium *" />
            <x-text-input
                id="nama_barang"
                name="nama_barang"
                type="text"
                class="w-full text-xs font-medium"
                :value="old('nama_barang', $bmnsLab->nama_barang)"
                required
            />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- MEREK --}}
            <div>
                <x-input-label for="merek" value="Merek / Pabrikan" />
                <x-text-input
                    id="merek"
                    name="merek"
                    type="text"
                    class="w-full text-xs"
                    :value="old('merek', $bmnsLab->merek)"
                />
            </div>

            {{-- LOKASI --}}
            <div>
                <x-input-label for="lokasi" value="Lokasi Lab *" />
                <select
                    id="lokasi"
                    name="lokasi"
                    required
                    class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 px-3 transition cursor-pointer"
                >
                    <option value="">Pilih Lokasi</option>
                    @foreach ($lokasis as $lokasi)
                        <option value="{{ $lokasi->nama }}" @selected(old('lokasi', $bmnsLab->lokasi) === $lokasi->nama)>
                            {{ $lokasi->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- PENANGGUNG JAWAB --}}
        <div>
            <x-input-label for="penanggungjawab" value="Penanggung Jawab *" />
            <select
                id="penanggungjawab"
                name="penanggungjawab"
                required
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 px-3 transition cursor-pointer"
            >
                <option value="">Pilih Penanggung Jawab</option>
                @foreach ($pegawais as $pegawai)
                    @php
                        $pjFormatted = $pegawai->nip ? "{$pegawai->nama} ({$pegawai->nip})" : $pegawai->nama;
                    @endphp
                    <option value="{{ $pjFormatted }}" @selected(old('penanggungjawab', $bmnsLab->penanggungjawab) === $pjFormatted || old('penanggungjawab', $bmnsLab->penanggungjawab) === $pegawai->nama)>
                        {{ $pjFormatted }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- KONDISI & STATUS KALIBRASI --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-3 border-t border-[#E3ECE6]">
            {{-- KONDISI --}}
            <div>
                <x-input-label value="Kondisi Fisik *" />
                <div class="grid grid-cols-3 gap-2 mt-1">
                    <label class="flex items-center gap-1.5 p-2 rounded-lg border border-[#E3ECE6] cursor-pointer hover:bg-[#F7FAF8] transition text-center">
                        <input type="radio" name="kondisi" value="Baik" class="text-[#163828]" {{ old('kondisi', $bmnsLab->kondisi ?? 'Baik') === 'Baik' ? 'checked' : '' }}>
                        <span class="text-xs font-semibold text-[#137333]">Baik</span>
                    </label>
                    <label class="flex items-center gap-1.5 p-2 rounded-lg border border-[#E3ECE6] cursor-pointer hover:bg-[#F7FAF8] transition text-center">
                        <input type="radio" name="kondisi" value="Rusak" class="text-[#163828]" {{ old('kondisi', $bmnsLab->kondisi ?? '') === 'Rusak' ? 'checked' : '' }}>
                        <span class="text-xs font-semibold text-[#9C322C]">Rusak</span>
                    </label>
                    <label class="flex items-center gap-1.5 p-2 rounded-lg border border-[#E3ECE6] cursor-pointer hover:bg-[#F7FAF8] transition text-center">
                        <input type="radio" name="kondisi" value="Perlu Kalibrasi" class="text-[#163828]" {{ old('kondisi', $bmnsLab->kondisi ?? '') === 'Perlu Kalibrasi' ? 'checked' : '' }}>
                        <span class="text-xs font-semibold text-[#62756A]">Kalibrasi</span>
                    </label>
                </div>
            </div>

            {{-- STATUS KALIBRASI --}}
            <div>
                <x-input-label for="status_kalibrasi" value="Status Kalibrasi" />
                <select
                    id="status_kalibrasi"
                    name="status_kalibrasi"
                    class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 px-3 transition cursor-pointer"
                >
                    <option value="Belum Dikalibrasi" @selected(old('status_kalibrasi', $bmnsLab->status_kalibrasi ?? 'Belum Dikalibrasi') === 'Belum Dikalibrasi')>Belum Dikalibrasi</option>
                    <option value="Berlaku" @selected(old('status_kalibrasi', $bmnsLab->status_kalibrasi ?? '') === 'Berlaku')>Berlaku</option>
                    <option value="Jatuh Tempo" @selected(old('status_kalibrasi', $bmnsLab->status_kalibrasi ?? '') === 'Jatuh Tempo')>Jatuh Tempo</option>
                </select>
            </div>
        </div>

        {{-- TANGGAL KALIBRASI --}}
        <div>
            <x-input-label for="tanggal_kalibrasi" value="Berlaku Sampai (Jatuh Tempo Kalibrasi)" />
            <x-text-input
                id="tanggal_kalibrasi"
                name="tanggal_kalibrasi"
                type="date"
                class="w-full text-xs"
                :value="old('tanggal_kalibrasi', optional($bmnsLab->tanggal_kalibrasi)->format('Y-m-d'))"
            />
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
            <a
                href="{{ route('bmns-lab.index') }}"
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

<script>
function toggleKode() {
    const input = document.getElementById('kode_barang');
    const btn = document.getElementById('ubahKodeBtn');
    if (!input) return;
    if (input.hasAttribute('readonly')) {
        input.removeAttribute('readonly');
        input.classList.remove('bg-[#F7FAF8]');
        input.classList.add('bg-white');
        input.focus();
        btn.textContent = 'Kunci Kode';
    } else {
        input.setAttribute('readonly', 'readonly');
        input.classList.add('bg-[#F7FAF8]');
        input.classList.remove('bg-white');
        btn.textContent = 'Ubah Kode';
    }
}
</script>
@endsection
