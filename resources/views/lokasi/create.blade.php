@extends('layouts.app')

@section('title', 'Tambah Lokasi — SIBOB')
@section('page-title', 'Tambah Lokasi')

@section('content')
<div class="space-y-6 max-w-xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('lokasis.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Master Lokasi</span>
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Tambah Lokasi Baru</h1>
        <p class="text-xs text-[#62756A] mt-0.5">Daftarkan nama ruangan atau lokasi penempatan aset inventaris.</p>
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
    <form method="POST" action="{{ route('lokasis.store') }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Nama Ruangan / Lokasi</h2>
        </div>

        <div>
            <x-input-label for="nama" value="Nama Lokasi *" />
            <x-text-input
                id="nama"
                name="nama"
                type="text"
                class="w-full text-xs font-medium"
                :value="old('nama')"
                placeholder="mis. Ruang Pengujian Mikrobiologi / Gedung Arsip"
                required
                autofocus
            />
            <p class="text-[11px] text-[#62756A] mt-1.5">
                Gunakan penamaan yang konsisten dan mudah dikenali saat pengisian formulir BMN.
            </p>
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
            <a
                href="{{ route('lokasis.index') }}"
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
                <span>Simpan Lokasi</span>
            </button>
        </div>
    </form>

</div>
@endsection