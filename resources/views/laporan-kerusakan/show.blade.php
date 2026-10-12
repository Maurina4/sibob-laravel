@extends('layouts.app')

@section('title', 'Detail Laporan — ' . $laporanKerusakan->nomor)
@section('page-title', 'Detail Laporan Kerusakan')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
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
                <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Laporan Kerusakan BMN</h1>
                <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                    {{ $laporanKerusakan->nomor }}
                </span>
            </div>
            <p class="text-xs text-[#62756A] mt-0.5">Rincian aduan teknis dan status penyelesaian perbaikan.</p>
        </div>

        @if(auth()->user()->role === 'Operator')
            <div class="flex items-center gap-2 shrink-0">
                <a
                    href="{{ route('laporan-kerusakan.edit', $laporanKerusakan) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-xl text-xs font-semibold shadow-2xs transition"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Tindak Lanjut Laporan</span>
                </a>
            </div>
        @endif
    </div>

    {{-- BENTO DETAILS CARD --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- RINCIAN ADUAN --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Informasi Barang & Pelapor</h2>
            </div>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Nama Barang</dt>
                    <dd class="font-bold text-[#132219]">{{ $laporanKerusakan->nama_barang }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Lokasi Barang</dt>
                    <dd class="font-medium text-[#132219]">{{ $laporanKerusakan->lokasi }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Tanggal Laporan</dt>
                    <dd class="font-mono text-[#132219]">
                        {{ $laporanKerusakan->tanggal ? $laporanKerusakan->tanggal->format('d F Y') : '—' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Tingkat Kerusakan</dt>
                    <dd>
                        @php
                            $tingkat = $laporanKerusakan->tingkat ?? 'Ringan';
                            $tingkatStyle = match($tingkat) {
                                'Berat' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                                'Sedang' => 'bg-[#FFF4E5] text-[#B25E00] border-[#FFE2BF]',
                                default => 'bg-[#F7FAF8] text-[#4D6357] border-[#E3ECE6]',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $tingkatStyle }}">
                            {{ $tingkat }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between py-1.5">
                    <dt class="text-[#62756A]">Pelapor</dt>
                    <dd class="font-semibold text-[#132219]">{{ $laporanKerusakan->pelapor_nama }}</dd>
                </div>
            </dl>

            <div class="pt-3 border-t border-[#E3ECE6]">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A] mb-1">Kronologi / Keluhan</div>
                <p class="text-xs text-[#3C4F44] whitespace-pre-line bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
                    {{ $laporanKerusakan->deskripsi }}
                </p>
            </div>
        </div>

        {{-- STATUS & RESOLUSI TEKNISI --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Status Penanganan & Solusi</h2>
            </div>

            @php
                $status = $laporanKerusakan->status ?? 'Dilaporkan';
                $statusStyle = match($status) {
                    'Selesai' => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                    'Diproses' => 'bg-[#FEF7E0] text-[#B06000] border-[#FEEFC3]',
                    default => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]',
                };
            @endphp

            <div class="p-4 rounded-xl border {{ $statusStyle }} space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider">Status Terkini</span>
                    <span class="font-bold text-sm">{{ $status }}</span>
                </div>
                @if($laporanKerusakan->tanggal_update)
                    <div class="text-[11px] opacity-80">
                        Diperbarui: {{ $laporanKerusakan->tanggal_update->format('d M Y') }} oleh {{ $laporanKerusakan->ditanggapi_oleh }}
                    </div>
                @endif
            </div>

            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A] mb-1">Tanggapan / Catatan Teknisi</div>
                @if($laporanKerusakan->tanggapan)
                    <p class="text-xs text-[#3C4F44] whitespace-pre-line bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
                        {{ $laporanKerusakan->tanggapan }}
                    </p>
                @else
                    <div class="p-4 rounded-xl bg-[#F7FAF8] border border-dashed border-[#E3ECE6] text-center text-xs text-[#62756A]">
                        Belum ada tanggapan teknis dari operator/teknisi.
                    </div>
                @endif
            </div>

            <div class="p-4 rounded-xl bg-[#EAF2ED]/60 border border-[#D5E5DA] text-xs text-[#163828] space-y-1 mt-4">
                <div class="font-bold text-[11px] uppercase tracking-wider">Instruksi Pemeliharaan</div>
                <p class="text-[11px] text-[#2C4A38] leading-relaxed">
                    Setiap kerusakan yang dilaporkan diverifikasi langsung oleh Sub Bagian Tata Usaha Balai Besar POM di Banjarmasin.
                </p>
            </div>
        </div>

    </div>

</div>
@endsection
