@extends('layouts.app')

@section('title', 'Detail Distribusi — ' . $distribusi->nomor)
@section('page-title', 'Detail Distribusi')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a
                href="{{ route('distribusi.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar Distribusi</span>
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Berita Acara Distribusi</h1>
                <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                    {{ $distribusi->nomor }}
                </span>
            </div>
            <p class="text-xs text-[#62756A] mt-0.5">Dokumen resmi serah terima barang internal instansi BBPOM di Banjarmasin.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a
                href="{{ route('distribusi.edit', $distribusi) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-xl text-xs font-semibold shadow-2xs transition"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Dokumen</span>
            </a>
        </div>
    </div>

    {{-- BENTO DETAILS CARD --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- RINCIAN BARANG & STATUS --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Rincian Barang yang Didistribusikan</h2>
            </div>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Nama Barang</dt>
                    <dd class="font-bold text-[#132219]">{{ $distribusi->nama_barang }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Jumlah Penyerahan</dt>
                    <dd class="font-semibold text-[#163828]">{{ $distribusi->jumlah }} {{ $distribusi->satuan }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Tanggal Dokumen</dt>
                    <dd class="font-mono text-[#132219]">
                        {{ $distribusi->tanggal ? $distribusi->tanggal->format('d F Y') : '—' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5">
                    <dt class="text-[#62756A]">Status Pelaksanaan</dt>
                    <dd>
                        @php
                            $statusLower = strtolower($distribusi->status ?? '');
                            $statusStyle = match(true) {
                                str_contains($statusLower, 'selesai') || str_contains($statusLower, 'terdistribusi') => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                                str_contains($statusLower, 'proses') || str_contains($statusLower, 'diajukan') => 'bg-[#FEF7E0] text-[#B06000] border-[#FEEFC3]',
                                default => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $statusStyle }}">
                            {{ $distribusi->status }}
                        </span>
                    </dd>
                </div>
            </dl>

            @if($distribusi->keterangan)
                <div class="pt-3 border-t border-[#E3ECE6]">
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A] mb-1">Keterangan / Maksud</div>
                    <p class="text-xs text-[#3C4F44] whitespace-pre-line bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
                        {{ $distribusi->keterangan }}
                    </p>
                </div>
            @endif
        </div>

        {{-- RUTE MUTASI & PIHAK TERKAIT --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Rute & Pihak Serah Terima</h2>
            </div>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Lokasi Asal (Pengirim)</dt>
                    <dd class="font-medium text-[#132219]">{{ $distribusi->lokasi_asal }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Lokasi Tujuan (Penempatan)</dt>
                    <dd class="font-bold text-[#163828]">{{ $distribusi->lokasi_tujuan }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Pegawai Penerima</dt>
                    <dd class="font-semibold text-[#132219]">{{ $distribusi->penerima }}</dd>
                </div>
            </dl>

            <div class="p-4 rounded-xl bg-[#EAF2ED]/60 border border-[#D5E5DA] text-xs text-[#163828] space-y-1 mt-4">
                <div class="font-bold text-[11px] uppercase tracking-wider">Verifikasi Mutasi SIBOB</div>
                <p class="text-[11px] text-[#2C4A38] leading-relaxed">
                    Barang yang tercatat telah diverifikasi perpindahannya dari lokasi asal ke unit tujuan sesuai instruksi kerja manajemen BMN.
                </p>
            </div>
        </div>

    </div>

</div>
@endsection
