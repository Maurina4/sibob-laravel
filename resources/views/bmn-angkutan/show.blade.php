@extends('layouts.app')

@section('title', 'Detail Kendaraan — ' . $bmnAngkutan->nama_barang)
@section('page-title', 'Detail Alat Angkutan Bermotor')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a
                href="{{ session('nav_last_url.bmns-angkutan', route('bmns-angkutan.index')) }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar Alat Angkutan Bermotor</span>
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">{{ $bmnAngkutan->nama_barang }}</h1>
            <div class="flex flex-wrap items-center gap-2 mt-1">
                <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-0.5 rounded-md border border-[#D5E5DA]">
                    {{ $bmnAngkutan->kode_barang ?: '—' }}
                </span>
                <span class="font-mono text-xs font-semibold text-[#132219] bg-[#F4F8F6] px-2.5 py-0.5 rounded-md border border-[#E3ECE6]">
                    NUP {{ $bmnAngkutan->nup ?? '—' }}
                </span>
                @if($bmnAngkutan->no_polisi)
                    <span class="font-mono text-xs font-bold text-[#B45309] bg-[#FEF6E9] px-2.5 py-0.5 rounded-md border border-[#FDE68A]">
                        {{ $bmnAngkutan->no_polisi }}
                    </span>
                @endif
                <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-md border border-emerald-200">
                    Alat Angkutan Bermotor
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button
                type="button"
                onclick="openQrModal({
                    title: 'QR Code Alat Angkutan Bermotor',
                    code: '{{ $bmnAngkutan->kode_nup }}',
                    name: '{{ addslashes($bmnAngkutan->nama_barang) }} ({{ addslashes($bmnAngkutan->no_polisi ?: '-') }})',
                    lokasi: '{{ addslashes($bmnAngkutan->lokasi ?: '—') }}',
                    pj: '{{ addslashes($bmnAngkutan->penanggungjawab ?: '—') }}'
                })"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#132219] rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer"
            >
                <svg class="w-4 h-4 text-[#163828]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                    <path d="M7 7h.01M17 7h.01M7 17h.01M14 14h3v3h-3zM20 14v3M14 20h3M20 20h.01"></path>
                </svg>
                <span>Cetak QR</span>
            </button>
            <a
                href="{{ route('bmns-angkutan.edit', $bmnAngkutan) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-xl text-xs font-semibold shadow-2xs transition"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Kendaraan</span>
            </a>
        </div>
    </div>

    {{-- BENTO DETAILS CARD --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- KARTU SPESIFIKASI KENDARAAN --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#B45309]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Spesifikasi Kendaraan</h2>
            </div>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Nomor Polisi (Plat Nomor)</dt>
                    <dd class="font-mono font-bold text-[#B45309] bg-[#FEF6E9] px-2 py-0.5 rounded border border-[#FDE68A]">
                        {{ $bmnAngkutan->no_polisi ?: '—' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Merek / Pabrikan</dt>
                    <dd class="font-semibold text-[#132219]">{{ $bmnAngkutan->merek ?: '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Tahun Perolehan</dt>
                    <dd class="font-mono text-[#132219]">{{ $bmnAngkutan->tahun_perolehan ?: '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Nilai Perolehan</dt>
                    <dd class="font-mono font-semibold text-[#132219]">
                        {{ $bmnAngkutan->nilai_perolehan !== null ? 'Rp ' . number_format($bmnAngkutan->nilai_perolehan, 0, ',', '.') : '—' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Sinkronisasi Master BMN</dt>
                    <dd class="font-mono text-[#132219]">
                        @if($bmnAngkutan->bmn_id)
                            <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                ID Master #{{ $bmnAngkutan->bmn_id }}
                            </span>
                        @else
                            <span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                Mandiri
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        {{-- KARTU STATUS PENEMPATAN & OPERASIONAL --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Status Operasional & Penempatan</h2>
            </div>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Lokasi / Garasi</dt>
                    <dd class="font-medium text-[#132219]">{{ $bmnAngkutan->lokasi ?: '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Penanggung Jawab</dt>
                    <dd class="font-medium text-[#132219]">{{ $bmnAngkutan->penanggungjawab ?: '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Kondisi Fisik</dt>
                    <dd>
                        @php
                            $kondisi = $bmnAngkutan->kondisi ?: 'Baik';
                            $kondisiLower = strtolower($kondisi);
                            $kondisiStyle = match($kondisiLower) {
                                'baik' => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                                'rusak sedang' => 'bg-[#FFF4E5] text-[#B25E00] border-[#FFE2BF]',
                                'rusak ringan' => 'bg-[#FEF7E0] text-[#B06000] border-[#FEEFC3]',
                                'rusak', 'rusak berat' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                                default => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $kondisiStyle }}">
                            {{ $kondisi }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Status Pengembalian</dt>
                    <dd>
                        @if($bmnAngkutan->is_dikembalikan)
                            <span class="inline-flex items-center text-[10px] font-semibold text-[#B25E00] bg-[#FFF4E5] px-1.5 py-0.5 rounded border border-[#FFE2BF]">
                                Dikembalikan ke Gudang
                            </span>
                        @else
                            <span class="inline-flex items-center text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                Aktif Operasional
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Terdaftar Sejak</dt>
                    <dd class="font-mono text-[#62756A]">
                        {{ $bmnAngkutan->created_at ? $bmnAngkutan->created_at->format('d M Y, H:i') : '—' }}
                    </dd>
                </div>
            </dl>
        </div>

    </div>

    {{-- TOMBOL KEMBALI BAWAH --}}
    <div class="flex items-center justify-between pt-2">
        <a
            href="{{ session('nav_last_url.bmns-angkutan', route('bmns-angkutan.index')) }}"
            class="px-4 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-xl text-xs font-semibold transition"
        >
            &larr; Kembali ke Daftar Alat Angkutan Bermotor
        </a>
    </div>

</div>

<x-qr-modal />

@endsection
