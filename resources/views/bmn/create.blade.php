@extends('layouts.app')

@section('title', 'Tambah BMN — SIBOB')
@section('page-title', 'Tambah Data BMN')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('bmns.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar BMN</span>
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Tambah Data BMN</h1>
        <p class="text-xs text-[#62756A] mt-0.5">Lengkapi formulir di bawah ini untuk mendaftarkan aset baru ke dalam sistem SIBOB.</p>
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

    {{-- FORMULIR --}}
    <form method="POST" action="{{ route('bmns.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- KOLOM UTAMA (2/3) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- CARD INFORMASI DASAR --}}
                <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-5">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                        <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Informasi Identitas Barang</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- KODE BARANG --}}
                        <div>
                            <x-input-label for="kode_barang" value="Kode Barang *" />
                            <x-text-input
                                id="kode_barang"
                                name="kode_barang"
                                type="text"
                                class="w-full font-mono text-xs"
                                :value="old('kode_barang')"
                                placeholder="mis. 3080305002"
                                required
                            />
                            <x-input-error :messages="$errors->get('kode_barang')" />
                        </div>

                        {{-- NUP --}}
                        <div>
                            <x-input-label for="nup" value="NUP *" />
                            <x-text-input
                                id="nup"
                                name="nup"
                                type="text"
                                class="w-full font-mono text-xs"
                                :value="old('nup')"
                                placeholder="mis. 1"
                                required
                            />
                            <x-input-error :messages="$errors->get('nup')" />
                        </div>
                    </div>

                    {{-- NAMA BARANG --}}
                    <div>
                        <x-input-label for="nama_barang" value="Nama Barang *" />
                        <x-text-input
                            id="nama_barang"
                            name="nama_barang"
                            type="text"
                            class="w-full text-xs font-medium"
                            :value="old('nama_barang')"
                            placeholder="mis. Komputer PC / Spektrofotometer UV-Vis"
                            required
                        />
                        <x-input-error :messages="$errors->get('nama_barang')" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- MEREK / TIPE --}}
                        <div>
                            <x-input-label for="merek" value="Merek / Tipe" />
                            <x-text-input
                                id="merek"
                                name="merek"
                                type="text"
                                class="w-full text-xs"
                                :value="old('merek')"
                                placeholder="mis. Lenovo ThinkCentre M70q"
                            />
                        </div>

                        {{-- TANGGAL TERIMA --}}
                        <div>
                            <x-input-label for="tanggal_terima" value="Tanggal Terima" />
                            <x-text-input
                                id="tanggal_terima"
                                name="tanggal_terima"
                                type="date"
                                class="w-full text-xs"
                                :value="old('tanggal_terima')"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- JENIS BARANG --}}
                        <div>
                            <x-input-label for="jenis" value="Jenis Barang *" />
                            <select
                                id="jenis"
                                name="jenis"
                                required
                                class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 px-3 transition cursor-pointer"
                            >
                                <option value="">Pilih Jenis</option>
                                <option value="BMN-TIK" {{ old('jenis') == 'BMN-TIK' ? 'selected' : '' }}>BMN-TIK</option>
                                <option value="BMN-LAB" {{ old('jenis') == 'BMN-LAB' ? 'selected' : '' }}>BMN-LAB</option>
                                <option value="BMN-NON TIK" {{ old('jenis') == 'BMN-NON TIK' ? 'selected' : '' }}>BMN-NON TIK</option>
                            </select>
                            <x-input-error :messages="$errors->get('jenis')" />
                        </div>

                        {{-- SATUAN --}}
                        <div>
                            <x-input-label for="satuan" value="Satuan" />
                            <select
                                id="satuan"
                                name="satuan"
                                class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 px-3 transition cursor-pointer"
                            >
                                <option value="unit" {{ old('satuan', 'unit') == 'unit' ? 'selected' : '' }}>unit</option>
                                <option value="buah" {{ old('satuan') == 'buah' ? 'selected' : '' }}>buah</option>
                                <option value="set" {{ old('satuan') == 'set' ? 'selected' : '' }}>set</option>
                                <option value="paket" {{ old('satuan') == 'paket' ? 'selected' : '' }}>paket</option>
                            </select>
                        </div>
                    </div>

                    {{-- SPESIFIKASI --}}
                    <div>
                        <x-input-label for="spesifikasi" value="Spesifikasi / Catatan Teknis" />
                        <textarea
                            id="spesifikasi"
                            name="spesifikasi"
                            rows="4"
                            placeholder="Detail spesifikasi teknis, kelengkapan, nomor seri, dsb..."
                            class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
                        >{{ old('spesifikasi') }}</textarea>
                    </div>
                </div>

            </div>

            {{-- KOLOM SAMPING (1/3) --}}
            <div class="space-y-6">

                {{-- CARD PENEMPATAN & TANGGUNG JAWAB --}}
                <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-5">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                        <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Penempatan & PJ</h2>
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
                            @foreach($lokasis ?? [] as $lokasi)
                                <option value="{{ $lokasi->nama }}" {{ old('lokasi') == $lokasi->nama ? 'selected' : '' }}>
                                    {{ $lokasi->nama }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('lokasi')" />
                    </div>

                    {{-- PENANGGUNG JAWAB --}}
                    <div>
                        <x-input-label for="penanggungjawab" value="Penanggung Jawab *" />
                        <select
                            id="penanggungjawab"
                            name="penanggungjawab"
                            required
                            onchange="autoFillBmnNip(this)"
                            class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 px-3 transition cursor-pointer"
                        >
                            <option value="">Pilih Pegawai PJ</option>
                            @foreach($pegawais ?? [] as $pegawai)
                                @php
                                    $pjFormatted = $pegawai->nip ? "{$pegawai->nama} ({$pegawai->nip})" : $pegawai->nama;
                                @endphp
                                <option
                                    value="{{ $pjFormatted }}"
                                    data-nip="{{ $pegawai->nip }}"
                                    data-nama="{{ $pegawai->nama }}"
                                    {{ old('penanggungjawab') == $pjFormatted || old('penanggungjawab') == $pegawai->nama ? 'selected' : '' }}
                                >
                                    {{ $pjFormatted }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('penanggungjawab')" />
                    </div>

                    {{-- NIP PJ --}}
                    <div>
                        <x-input-label for="penanggungjawab_nip" value="NIP Penanggung Jawab" />
                        <x-text-input
                            id="penanggungjawab_nip"
                            name="penanggungjawab_nip"
                            type="text"
                            class="w-full font-mono text-xs"
                            :value="old('penanggungjawab_nip')"
                            placeholder="mis. 198501012010121001"
                        />
                    </div>

                    {{-- KONDISI --}}
                    <div>
                        <x-input-label value="Kondisi Fisik *" />
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#E3ECE6] cursor-pointer hover:bg-[#F7FAF8] transition">
                                <input
                                    type="radio"
                                    name="kondisi"
                                    value="Baik"
                                    class="text-[#163828] focus:ring-[#163828]"
                                    {{ old('kondisi', 'Baik') == 'Baik' ? 'checked' : '' }}
                                >
                                <span class="text-xs font-semibold text-[#137333]">Baik</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#E3ECE6] cursor-pointer hover:bg-[#F7FAF8] transition">
                                <input
                                    type="radio"
                                    name="kondisi"
                                    value="Rusak"
                                    class="text-[#163828] focus:ring-[#163828]"
                                    {{ old('kondisi') == 'Rusak' ? 'checked' : '' }}
                                >
                                <span class="text-xs font-semibold text-[#9C322C]">Rusak</span>
                            </label>
                        </div>
                    </div>

                    {{-- JADWAL PEMELIHARAAN --}}
                    <div>
                        <x-input-label for="jadwal_pemeliharaan" value="Jadwal Pemeliharaan" />
                        <x-text-input
                            id="jadwal_pemeliharaan"
                            name="jadwal_pemeliharaan"
                            type="date"
                            class="w-full text-xs"
                            :value="old('jadwal_pemeliharaan')"
                        />
                    </div>
                </div>

                {{-- TOMBOL AKSI --}}
                <div class="bg-white rounded-2xl border border-[#E3ECE6] p-5 shadow-2xs space-y-3">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Data BMN</span>
                    </button>

                    <a
                        href="{{ route('bmns.index') }}"
                        class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-xl text-xs font-semibold transition"
                    >
                        Batal
                    </a>
                </div>

            </div>

        </div>
    </form>

</div>

<script>
    function autoFillBmnNip(sel) {
        const opt = sel.options[sel.selectedIndex];
        const nip = opt ? (opt.getAttribute('data-nip') || '') : '';
        const nipInput = document.getElementById('penanggungjawab_nip');
        if (nipInput && nip) {
            nipInput.value = nip;
        }
    }
</script>
@endsection