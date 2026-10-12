@extends('layouts.app')

@section('title', 'Ubah BMN TIK — ' . $bmnTik->nama_barang)
@section('page-title', 'Ubah BMN TIK')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('bmns-tik.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar BMN TIK</span>
        </a>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Ubah Data BMN TIK</h1>
            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                {{ $bmnTik->kode_barang }}-NUP.{{ $bmnTik->nup }}
            </span>
        </div>
        <p class="text-xs text-[#62756A] mt-0.5">Perbarui rincian perangkat: <span class="font-medium text-[#132219]">{{ $bmnTik->nama_barang }}</span></p>
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
    <form method="POST" action="{{ route('bmns-tik.update', ['bmnTik' => $bmnTik->id]) }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Rincian Perangkat TIK</h2>
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
                    :value="old('kode_barang', $bmnTik->kode_barang)"
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
                    :value="old('nup', $bmnTik->nup)"
                    required
                />
            </div>
        </div>

        {{-- NAMA BARANG --}}
        <div>
            <x-input-label for="nama_barang" value="Nama Barang TIK *" />
            <x-text-input
                id="nama_barang"
                name="nama_barang"
                type="text"
                class="w-full text-xs font-medium"
                :value="old('nama_barang', $bmnTik->nama_barang)"
                required
            />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- JENIS --}}
            <div>
                <x-input-label for="jenis" value="Kategori Perangkat *" />
                <select
                    id="jenis"
                    name="jenis"
                    required
                    class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 px-3 transition cursor-pointer"
                >
                    <option value="">Pilih Kategori</option>
                    @foreach (['Komputer', 'Laptop', 'Printer', 'UPS', 'Scanner', 'Monitor', 'Server', 'Jaringan', 'Lainnya'] as $jenis)
                        <option value="{{ $jenis }}" @selected(old('jenis', $bmnTik->jenis) === $jenis)>{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>

            {{-- LOKASI --}}
            <div>
                <x-input-label for="lokasi" value="Lokasi Ruangan *" />
                <select
                    id="lokasi"
                    name="lokasi"
                    required
                    class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 px-3 transition cursor-pointer"
                >
                    <option value="">Pilih Lokasi</option>
                    @foreach ($lokasis as $lokasi)
                        <option value="{{ $lokasi->nama }}" @selected(old('lokasi', $bmnTik->lokasi) === $lokasi->nama)>
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
                    <option value="{{ $pjFormatted }}" @selected(old('penanggungjawab', $bmnTik->penanggungjawab) === $pjFormatted || old('penanggungjawab', $bmnTik->penanggungjawab) === $pegawai->nama)>
                        {{ $pjFormatted }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
            <a
                href="{{ route('bmns-tik.index') }}"
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
