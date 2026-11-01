@extends('layouts.app')

@section('title', 'Detail BMN — ' . $bmn->nama_barang)
@section('page-title', 'Detail BMN')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a
                href="{{ session('nav_last_url.bmns', route('bmns.index')) }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar BMN</span>
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">{{ $bmn->nama_barang }}</h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-0.5 rounded-md border border-[#D5E5DA]">
                    {{ $bmn->kode_barang ?: '—' }}
                </span>
                <span class="font-mono text-xs font-semibold text-[#132219] bg-[#F4F8F6] px-2.5 py-0.5 rounded-md border border-[#E3ECE6]" title="NUP">
                    NUP {{ $bmn->nup ?? '—' }}
                </span>
                <x-bmn-badge :type="$bmn->jenis" />
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a
                href="{{ route('bmns.edit', $bmn) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-xl text-xs font-semibold shadow-2xs transition"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit BMN</span>
            </a>
        </div>
    </div>

    {{-- BENTO DETAILS CARD --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- KARTU SPESIFIKASI & IDENTITAS --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Spesifikasi & Identitas</h2>
            </div>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Nomor Seri</dt>
                    <dd class="font-mono font-semibold text-[#132219]">{{ $bmn->no_seri ?: '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Merek / Tipe</dt>
                    <dd class="font-semibold text-[#132219]">{{ $bmn->merek ?: '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Satuan</dt>
                    <dd class="font-medium text-[#132219]">{{ $bmn->satuan ?: 'unit' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Tahun Perolehan</dt>
                    <dd class="font-mono text-[#132219]">{{ $bmn->tahun_perolehan ?: '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Nilai Perolehan</dt>
                    <dd class="font-mono font-semibold text-[#132219]">
                        {{ $bmn->nilai_perolehan !== null ? 'Rp ' . number_format($bmn->nilai_perolehan, 0, ',', '.') : '—' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Tanggal Terima</dt>
                    <dd class="font-mono text-[#132219]">
                        {{ $bmn->tanggal_terima ? \Carbon\Carbon::parse($bmn->tanggal_terima)->format('d F Y') : '—' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Kondisi Fisik</dt>
                    <dd>
                        @php
                            $kondisi = $bmn->kondisi ?: 'Baik';
                            $kondisiLower = strtolower($kondisi);
                            $kondisiStyle = match($kondisiLower) {
                                'baik' => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                                'rusak sedang' => 'bg-[#FFF4E5] text-[#B25E00] border-[#FFE2BF]',
                                'rusak ringan' => 'bg-[#FEF7E0] text-[#B06000] border-[#FEEFC3]',
                                'rusak', 'rusak berat' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                                default => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]',
                            };
                            $dotColor = match($kondisiLower) {
                                'baik' => 'bg-[#137333]',
                                'rusak sedang', 'rusak ringan' => 'bg-[#B06000]',
                                'rusak', 'rusak berat' => 'bg-[#9C322C]',
                                default => 'bg-[#4A5D52]',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $kondisiStyle }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                            <span>{{ $kondisi }}</span>
                        </span>
                    </dd>
                </div>
            </dl>

            @if($bmn->spesifikasi)
                <div class="pt-3 border-t border-[#E3ECE6]">
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A] mb-1">Catatan Teknis</div>
                    <p class="text-xs text-[#3C4F44] whitespace-pre-line bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
                        {{ $bmn->spesifikasi }}
                    </p>
                </div>
            @endif
        </div>

        {{-- KARTU PENEMPATAN & PENANGGUNG JAWAB --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Penempatan & Kepemilikan</h2>
            </div>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Lokasi Ruangan</dt>
                    <dd class="font-semibold text-[#132219]">
                        @if($bmn->lokasiRef?->kode_ruangan)
                            <span class="font-mono text-[11px] font-bold px-1.5 py-0.5 rounded bg-[#EAF2ED] text-[#163828] border border-[#D5E5DA] mr-1">
                                {{ $bmn->lokasiRef->kode_ruangan }}
                            </span>
                            <span>{{ $bmn->lokasiRef->nama }}</span>
                        @else
                            {{ $bmn->lokasi ?: '—' }}
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Penanggung Jawab</dt>
                    <dd class="font-semibold text-[#132219]">{{ $bmn->penanggungjawab ?: '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">NIP Penanggung Jawab</dt>
                    <dd class="font-mono text-[#132219]">{{ $bmn->penanggungjawab_nip ?: '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Kategori Terdaftar</dt>
                    <dd class="font-medium text-[#132219]">{{ $bmn->kategori?->nama_kategori ?: 'Inventaris Umum' }}</dd>
                </div>
            </dl>

            {{-- INSTANSI FOOTPRINT --}}
            <div class="p-3.5 rounded-xl bg-[#EAF2ED]/60 border border-[#D5E5DA] text-xs text-[#163828] space-y-1">
                <div class="font-bold text-[11px] uppercase tracking-wider">Aset Resmi Satuan Kerja</div>
                <p class="text-[11px] text-[#2C4A38] leading-relaxed">
                    Balai Besar Pengawas Obat dan Makanan di Banjarbaru — Bagian Tata Usaha & Pengelolaan BMN.
                </p>
            </div>
        </div>

    </div>

</div>
@endsection
