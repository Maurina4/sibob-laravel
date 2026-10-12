@extends('layouts.app')

@section('title', 'Detail BA Perpindahan — ' . ($baPerpindahan->nomor_ba ?: $baPerpindahan->id))
@section('page-title', 'Detail BA Perpindahan')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
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
                <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Berita Acara Perpindahan</h1>
                <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                    {{ $baPerpindahan->nomor_ba ?: 'BA-MUT/' . $baPerpindahan->id }}
                </span>
            </div>
            <p class="text-xs text-[#62756A] mt-0.5">Rincian mutasi internal dan riwayat persetujuan dokumen.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a
                href="{{ route('ba-perpindahan.print', $baPerpindahan) }}"
                target="_blank"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#132219] rounded-xl text-xs font-semibold shadow-2xs transition"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak BA</span>
            </a>
            @if($baPerpindahan->status === 'Diajukan' || auth()->user()->role === 'Operator')
                <a
                    href="{{ route('ba-perpindahan.edit', $baPerpindahan) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-xl text-xs font-semibold shadow-2xs transition"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Edit Data</span>
                </a>
            @endif
        </div>
    </div>

    {{-- BENTO DETAILS CARD --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- RINCIAN PERPINDAHAN --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Rincian Barang & Lokasi</h2>
            </div>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Nama Barang</dt>
                    <dd class="font-bold text-[#132219]">{{ $baPerpindahan->nama_barang }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Tanggal Dokumen</dt>
                    <dd class="font-mono text-[#132219]">
                        {{ $baPerpindahan->tanggal ? $baPerpindahan->tanggal->format('d F Y') : '—' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Lokasi Asal (Dari)</dt>
                    <dd class="font-medium text-[#132219]">{{ $baPerpindahan->dari }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Lokasi Tujuan (Ke)</dt>
                    <dd class="font-bold text-[#163828]">{{ $baPerpindahan->ke ?: '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Diajukan Oleh</dt>
                    <dd class="font-semibold text-[#132219]">{{ $baPerpindahan->pengaju_nama ?: 'Pegawai' }}</dd>
                </div>
                <div class="flex justify-between py-1.5">
                    <dt class="text-[#62756A]">Pihak Kedua / Penerima</dt>
                    <dd class="font-semibold text-[#132219]">{{ $baPerpindahan->pihak_kedua ?: '—' }}</dd>
                </div>
            </dl>

            @if($baPerpindahan->alasan)
                <div class="pt-3 border-t border-[#E3ECE6]">
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A] mb-1">Alasan Pemindahan</div>
                    <p class="text-xs text-[#3C4F44] whitespace-pre-line bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
                        {{ $baPerpindahan->alasan }}
                    </p>
                </div>
            @endif
        </div>

        {{-- STATUS & KEPUTUSAN OPERATOR --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Status Verifikasi & Persetujuan</h2>
            </div>

            @php
                $status = $baPerpindahan->status ?? 'Diajukan';
                $statusStyle = match($status) {
                    'Disetujui' => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                    'Ditolak' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                    default => 'bg-[#FEF7E0] text-[#B06000] border-[#FEEFC3]',
                };
            @endphp

            <div class="p-4 rounded-xl border {{ $statusStyle }} space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider">Status Dokumen</span>
                    <span class="font-bold text-sm">{{ $status }}</span>
                </div>
                @if($baPerpindahan->tanggal_keputusan)
                    <div class="text-[11px] opacity-80">
                        Diputuskan pada: {{ $baPerpindahan->tanggal_keputusan->format('d M Y') }} oleh {{ $baPerpindahan->diputuskan_oleh }}
                    </div>
                @endif
            </div>

            @if($baPerpindahan->catatan_operator)
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A] mb-1">Catatan Operator / Verifikator</div>
                    <p class="text-xs text-[#3C4F44] whitespace-pre-line bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
                        {{ $baPerpindahan->catatan_operator }}
                    </p>
                </div>
            @endif

            {{-- OPERATOR APPROVAL ACTIONS --}}
            @if(auth()->user()->role === 'Operator' && $status === 'Diajukan')
                <div class="pt-4 border-t border-[#E3ECE6] space-y-3">
                    <div class="text-xs font-semibold text-[#132219]">Tindakan Operator:</div>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('ba-perpindahan.approve', $baPerpindahan) }}" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                onclick="return confirm('Apakah Anda yakin ingin menyetujui mutasi barang ini?')"
                                class="w-full py-2 px-3 bg-[#137333] hover:bg-[#0E5C28] text-white rounded-lg text-xs font-semibold transition"
                            >
                                Setujui Pengajuan
                            </button>
                        </form>
                        <form method="POST" action="{{ route('ba-perpindahan.reject', $baPerpindahan) }}" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                onclick="return confirm('Apakah Anda yakin ingin menolak pengajuan ini?')"
                                class="w-full py-2 px-3 bg-[#9C322C] hover:bg-[#83261F] text-white rounded-lg text-xs font-semibold transition"
                            >
                                Tolak Pengajuan
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
