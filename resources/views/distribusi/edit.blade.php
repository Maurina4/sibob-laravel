@extends('layouts.app')

@section('title', 'Ubah BA Distribusi — ' . $distribusi->nomor)
@section('page-title', 'Ubah BA Distribusi')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('distribusi.index', session('distribusi_edit_return_query.'.$distribusi->id, [])) }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Distribusi</span>
        </a>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Ubah BA Distribusi</h1>
            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                {{ $distribusi->nomor }}
            </span>
        </div>
        <p class="text-xs text-[#62756A] mt-0.5">Perbarui rincian dokumen berita acara penyerahan barang.</p>
    </div>

    {{-- INFO BMN TERKAIT --}}
    @if($distribusi->bmn)
        <div class="p-4 rounded-2xl bg-[#EAF2ED] border border-[#CDE1D4] text-[#163828] flex items-start gap-3.5 shadow-2xs">
            <div class="w-8 h-8 rounded-xl bg-[#163828] text-white flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div class="space-y-1">
                <div class="text-xs font-bold uppercase tracking-wider text-[#163828]">Aset BMN Terkait: {{ $distribusi->bmn->nama_barang }}</div>
                <div class="text-[11px] text-[#2C4A38] space-y-0.5">
                    <div>Kode Barang: <span class="font-mono font-semibold">{{ $distribusi->bmn->kode_barang }}</span> | NUP: <span class="font-mono font-semibold">{{ $distribusi->bmn->nup }}</span> | Jenis: <span class="font-semibold">{{ $distribusi->bmn->jenis }}</span></div>
                    <div class="text-xs text-[#163828] font-medium pt-1">
                        Menyimpan perubahan Berita Acara ini akan <strong>secara otomatis memperbarui lokasi ruangan dan penanggung jawab</strong> pada data induk BMN.
                    </div>
                </div>
            </div>
        </div>
    @endif

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
    <form method="POST" action="{{ route('distribusi.update', $distribusi) }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Informasi Berita Acara</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- NOMOR DOKUMEN --}}
            <div>
                <x-input-label for="nomor" value="Nomor Berita Acara *" />
                <x-text-input
                    id="nomor"
                    name="nomor"
                    type="text"
                    class="w-full font-mono text-xs"
                    :value="old('nomor', $distribusi->nomor)"
                    required
                />
            </div>

            {{-- TANGGAL --}}
            <div>
                <x-input-label for="tanggal" value="Tanggal Distribusi *" />
                <x-text-input
                    id="tanggal"
                    name="tanggal"
                    type="date"
                    class="w-full text-xs"
                    :value="old('tanggal', optional($distribusi->tanggal)->format('Y-m-d'))"
                    max="{{ date('Y-m-d') }}"
                    required
                />
                <x-input-error :messages="$errors->get('tanggal')" class="mt-1" />
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
                :value="old('nama_barang', $distribusi->nama_barang)"
                required
            />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- JUMLAH --}}
            <div>
                <x-input-label for="jumlah" value="Jumlah Barang *" />
                <x-text-input
                    id="jumlah"
                    name="jumlah"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    data-numeric="true"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    class="w-full text-xs"
                    :value="old('jumlah', $distribusi->jumlah)"
                    required
                />
            </div>

            {{-- SATUAN --}}
            <div>
                <x-input-label for="satuan" value="Satuan *" />
                <x-text-input
                    id="satuan"
                    name="satuan"
                    type="text"
                    class="w-full text-xs"
                    :value="old('satuan', $distribusi->satuan)"
                    required
                />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- LOKASI ASAL --}}
            <div>
                <x-input-label for="lokasi_asal" value="Lokasi Asal *" />
                <input
                    type="text"
                    id="lokasi_asal"
                    name="lokasi_asal"
                    list="lokasi_asal_list_edit"
                    class="w-full text-xs font-medium px-3.5 py-2 rounded-lg border border-[#E3ECE6] text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                    value="{{ old('lokasi_asal', $distribusi->lokasi_asal ?: 'Gudang BMN') }}"
                    placeholder="mis. Gudang BMN"
                    required
                />
                <datalist id="lokasi_asal_list_edit">
                    @foreach($lokasis ?? [] as $lokasi)
                        <option value="{{ $lokasi->nama }}" label="{{ $lokasi->format_kombinasi }}">{{ $lokasi->format_kombinasi }}</option>
                    @endforeach
                </datalist>
            </div>

            {{-- LOKASI TUJUAN --}}
            <div>
                <x-input-label for="lokasi_tujuan" value="Lokasi Tujuan (Ruangan Baru) *" />
                <input
                    type="text"
                    id="lokasi_tujuan"
                    name="lokasi_tujuan"
                    list="lokasi_tujuan_list_edit"
                    class="w-full text-xs font-medium px-3.5 py-2 rounded-lg border border-[#E3ECE6] text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                    value="{{ old('lokasi_tujuan', $distribusi->lokasi_tujuan) }}"
                    placeholder="Pilih atau ketik ruangan tujuan..."
                    required
                />
                <datalist id="lokasi_tujuan_list_edit">
                    @foreach($lokasis ?? [] as $lokasi)
                        <option value="{{ $lokasi->nama }}" label="{{ $lokasi->format_kombinasi }}">{{ $lokasi->format_kombinasi }}</option>
                    @endforeach
                </datalist>
            </div>
        </div>

        {{-- PENERIMA --}}
        <div>
            <x-input-label for="penerima" value="Nama Pegawai Penerima *" />
            <input
                id="penerima"
                name="penerima"
                type="text"
                list="pegawai_penerima_list_edit"
                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                value="{{ old('penerima', $distribusi->penerima) }}"
                placeholder="Pilih atau ketik: Nama (NIP Pegawai)"
                required
            />
            <datalist id="pegawai_penerima_list_edit">
                @foreach($pegawais ?? [] as $p)
                    <option value="{{ $p->nip ? "{$p->nama} ({$p->nip})" : $p->nama }}"></option>
                @endforeach
            </datalist>
        </div>

        {{-- KETERANGAN --}}
        <div>
            <x-input-label for="keterangan" value="Keterangan / Catatan Tambahan" />
            <textarea
                id="keterangan"
                name="keterangan"
                rows="3"
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
            >{{ old('keterangan', $distribusi->keterangan) }}</textarea>
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
            <a
                href="{{ route('distribusi.index', session('distribusi_edit_return_query.'.$distribusi->id, [])) }}"
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
