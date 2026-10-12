@extends('layouts.app')

@section('title', 'Ubah BMN — ' . $bmn->nama_barang)
@section('page-title', 'Ubah Data BMN')

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
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Ubah Data BMN</h1>
            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                {{ $bmn->kode_barang }}@if($bmn->nup)-NUP.{{ $bmn->nup }}@endif
            </span>
        </div>
        <p class="text-xs text-[#62756A] mt-0.5">Perbarui informasi barang inventaris: <span class="font-medium text-[#132219]">{{ $bmn->nama_barang }}</span></p>
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

    {{-- FORM EDIT --}}
    <form method="POST" action="{{ route('bmns.update', $bmn) }}" class="space-y-6">
        @csrf
        @method('PUT')

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
                            <div class="flex items-center justify-between mb-1.5">
                                <x-input-label for="kode_barang" value="Kode Barang *" class="!mb-0" />
                                <button
                                    type="button"
                                    onclick="unlockField('kode_barang')"
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
                                :value="old('kode_barang', $bmn->kode_barang)"
                                readonly
                                required
                            />
                            <x-input-error :messages="$errors->get('kode_barang')" />
                        </div>

                        {{-- NUP --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <x-input-label for="nup" value="NUP *" class="!mb-0" />
                                <button
                                    type="button"
                                    onclick="unlockField('nup')"
                                    class="text-[11px] text-[#163828] font-medium hover:underline cursor-pointer"
                                >
                                    Ubah NUP
                                </button>
                            </div>
                            <x-text-input
                                id="nup"
                                name="nup"
                                type="text"
                                class="w-full font-mono text-xs bg-[#F7FAF8]"
                                :value="old('nup', $bmn->nup)"
                                readonly
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
                            :value="old('nama_barang', $bmn->nama_barang)"
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
                                :value="old('merek', $bmn->merek)"
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
                                :value="old('tanggal_terima', optional($bmn->tanggal_terima)->format('Y-m-d'))"
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
                                <option value="BMN-TIK" {{ old('jenis', $bmn->jenis) == 'BMN-TIK' ? 'selected' : '' }}>BMN-TIK</option>
                                <option value="BMN-LAB" {{ old('jenis', $bmn->jenis) == 'BMN-LAB' ? 'selected' : '' }}>BMN-LAB</option>
                                <option value="BMN-NON TIK" {{ old('jenis', $bmn->jenis) == 'BMN-NON TIK' ? 'selected' : '' }}>BMN-NON TIK</option>
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
                                <option value="unit" {{ old('satuan', $bmn->satuan) == 'unit' ? 'selected' : '' }}>unit</option>
                                <option value="buah" {{ old('satuan', $bmn->satuan) == 'buah' ? 'selected' : '' }}>buah</option>
                                <option value="set" {{ old('satuan', $bmn->satuan) == 'set' ? 'selected' : '' }}>set</option>
                                <option value="paket" {{ old('satuan', $bmn->satuan) == 'paket' ? 'selected' : '' }}>paket</option>
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
                            class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
                        >{{ old('spesifikasi', $bmn->spesifikasi) }}</textarea>
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
                                <option value="{{ $lokasi->nama }}" {{ old('lokasi', $bmn->lokasi) == $lokasi->nama ? 'selected' : '' }}>
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
                                    {{ old('penanggungjawab', $bmn->penanggungjawab) == $pjFormatted || old('penanggungjawab', $bmn->penanggungjawab) == $pegawai->nama ? 'selected' : '' }}
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
                            :value="old('penanggungjawab_nip', $bmn->penanggungjawab_nip)"
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
                                    {{ old('kondisi', $bmn->kondisi) == 'Baik' ? 'checked' : '' }}
                                >
                                <span class="text-xs font-semibold text-[#137333]">Baik</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#E3ECE6] cursor-pointer hover:bg-[#F7FAF8] transition">
                                <input
                                    type="radio"
                                    name="kondisi"
                                    value="Rusak"
                                    class="text-[#163828] focus:ring-[#163828]"
                                    {{ old('kondisi', $bmn->kondisi) == 'Rusak' ? 'checked' : '' }}
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
                            :value="old('jadwal_pemeliharaan', optional($bmn->jadwal_pemeliharaan)->format('Y-m-d'))"
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
                        <span>Simpan Perubahan</span>
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
    function unlockField(id) {
        const input = document.getElementById(id);
        if (input) {
            input.removeAttribute('readonly');
            input.classList.remove('bg-[#F7FAF8]');
            input.classList.add('bg-white');
            input.focus();
        }
    }

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