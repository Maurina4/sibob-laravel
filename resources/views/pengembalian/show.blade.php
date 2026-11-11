@extends('layouts.app')

@section('title', 'Detail Berita Acara Pengembalian — ' . $pengembalian->nomor_pengajuan)
@section('page-title', 'Detail Berita Acara Pengembalian')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a
                href="{{ session('nav_last_url.pengembalians', route('pengembalians.index')) }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Berita Acara Pengembalian</span>
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Berita Acara Pengembalian</h1>
                <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                    {{ $pengembalian->nomor_pengajuan }}
                </span>
                @if ($pengembalian->pengajuan)
                    <a
                        href="{{ route('admin.pengajuan-pengembalian.show', $pengembalian->pengajuan) }}"
                        class="text-[11px] font-medium text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2.5 py-1 rounded-lg transition"
                    >
                        Ref Pengajuan: {{ $pengembalian->pengajuan->nomor_pengajuan }} &rarr;
                    </a>
                @endif
            </div>
            <p class="text-xs text-[#62756A] mt-0.5">Dokumen resmi penyerahan kembali aset Barang Milik Negara ke Subbag Tata Usaha BBPOM di Banjarbaru.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            {{-- TOMBOL CETAK BA --}}
            @php
                $printUrl = $pengembalian->pengajuan_id
                    ? route('pengajuan-pengembalian.cetak-ba', $pengembalian->pengajuan_id)
                    : route('pengembalians.print', $pengembalian);
            @endphp
            <a
                href="{{ $printUrl }}"
                target="_blank"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#132219] rounded-xl text-xs font-semibold shadow-2xs transition"
                title="Cetak Berita Acara Pengembalian BMN"
            >
                <svg class="w-3.5 h-3.5 text-[#163828]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak BA</span>
            </a>

            {{-- TOMBOL EDIT --}}
            <a
                href="{{ route('pengembalians.edit', $pengembalian) }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-xl text-xs font-semibold shadow-2xs transition"
                title="Edit Data Pengembalian"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Data</span>
            </a>

            {{-- TOMBOL HAPUS --}}
            <form
                method="POST"
                action="{{ route('pengembalians.destroy', $pengembalian) }}"
                onsubmit="return confirm('Apakah Anda yakin ingin memindahkan data pengembalian ini ke Data Terhapus?')"
                class="inline"
            >
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#FDF0EE] hover:bg-[#FCE3DF] border border-[#F8D4D0] text-[#9C322C] rounded-xl text-xs font-semibold transition cursor-pointer"
                    title="Hapus Data Pengembalian"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Hapus</span>
                </button>
            </form>
        </div>
    </div>

    {{-- BENTO GRID: INFORMASI DOKUMEN & PEGAWAI --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- CARD 1: INFORMASI DOKUMEN --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Informasi Dokumen Pengembalian</h2>
            </div>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between items-center py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Nomor (SIPANDA)</dt>
                    <dd>
                        <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-0.5 rounded border border-[#D5E5DA]">
                            {{ $pengembalian->nomor_pengajuan }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Tanggal Dokumen</dt>
                    <dd class="font-mono text-[#132219]">
                        {{ $pengembalian->tanggal ? \Carbon\Carbon::parse($pengembalian->tanggal)->format('d F Y') : '—' }}
                    </dd>
                </div>

                <div class="flex justify-between py-1.5">
                    <dt class="text-[#62756A]">Total Barang Dikembalikan</dt>
                    <dd class="font-bold text-[#132219]">
                        {{ $pengembalian->items->count() }} Unit Barang
                    </dd>
                </div>
            </dl>
        </div>

        {{-- CARD 2: INFORMASI PIHAK TERKAIT --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Pihak Yang Menyerahkan (Pihak I)</h2>
            </div>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Nama Pegawai Pengaju</dt>
                    <dd class="font-bold text-[#132219] text-right">
                        {{ $pengembalian->pengajuPegawai?->nama ?? $pengembalian->nama_pengaju }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Nomor Induk Pegawai (NIP)</dt>
                    <dd class="font-mono text-[#132219] text-right">
                        {{ $pengembalian->pengajuPegawai?->nip ?: '—' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Jabatan</dt>
                    <dd class="text-[#132219] text-right">
                        {{ $pengembalian->pengajuPegawai?->jabatan ?: 'Pegawai Pemakai BMN' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Bidang / Unit Kerja</dt>
                    <dd class="font-semibold text-[#163828] text-right">
                        {{ $pengembalian->bidang ?: ($pengembalian->pengajuPegawai?->unit_kerja ?: 'Balai Besar POM di Banjarbaru') }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5">
                    <dt class="text-[#62756A]">Pihak Penerima (Pihak II)</dt>
                    <dd class="text-[#132219] text-right">
                        Sub Bagian Tata Usaha (Pengelola BMN)
                    </dd>
                </div>
            </dl>
        </div>

    </div>

    {{-- CARD 3: TABEL DAFTAR BARANG YANG DIKEMBALIKAN --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#E3ECE6]">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Daftar Rincian Barang yang Dikembalikan</h2>
            </div>
            <span class="text-xs font-semibold text-[#62756A]">
                Total: <span class="text-[#163828] font-bold">{{ $pengembalian->items->count() }}</span> Unit
            </span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-[#E3ECE6]">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3 px-3.5 text-center w-12">No</th>
                        <th class="py-3 px-3.5 whitespace-nowrap">Kode Barang</th>
                        <th class="py-3 px-3.5 text-center whitespace-nowrap">NUP</th>
                        <th class="py-3 px-3.5 min-w-[220px]">Nama Barang</th>
                        <th class="py-3 px-3.5">Lokasi / Ruangan Asal</th>
                        <th class="py-3 px-3.5 text-center">Kondisi Awal</th>
                        <th class="py-3 px-3.5 text-center">Kondisi Kembali</th>
                        <th class="py-3 px-3.5">Keterangan / Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6]/70 text-xs">
                    @forelse($pengembalian->items as $index => $item)
                        @php
                            $kondisiAwal = $item->kondisi_awal ?: ($item->bmn?->kondisi ?: 'Baik');
                            $kondisiKembali = $item->kondisi ?: 'Baik';

                            $kondisiKembaliBadge = match($kondisiKembali) {
                                'Rusak Berat' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                                default => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                            };
                        @endphp
                        <tr class="hover:bg-[#F9FCFA] transition">
                            <td class="py-3 px-3.5 text-center text-[#62756A] font-mono text-[11px]">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3 px-3.5 font-mono whitespace-nowrap">
                                @php
                                    $kodeVal = $item->kode_barang ?? $item->bmn?->kode_barang;
                                @endphp
                                @if($kodeVal)
                                    <span class="inline-block px-2 py-0.5 font-semibold text-[#163828] bg-[#EAF2ED] rounded border border-[#D5E5DA] text-[11px]">
                                        {{ $kodeVal }}
                                    </span>
                                @else
                                    <span class="text-[#8D9D95] font-mono">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 text-center font-mono whitespace-nowrap">
                                @php
                                    $nupVal = $item->nup ?? $item->bmn?->nup;
                                @endphp
                                @if($nupVal !== null && $nupVal !== '')
                                    <span class="inline-block px-2 py-0.5 font-semibold text-[#163828] bg-[#EAF2ED] rounded border border-[#D5E5DA] text-[11px]">
                                        {{ $nupVal }}
                                    </span>
                                @else
                                    <span class="text-[#8D9D95] font-mono">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3.5">
                                <div class="font-semibold text-[#132219]">
                                    {{ $item->nama_barang_snapshot ?: ($item->bmn?->nama_barang ?: 'Barang BMN') }}
                                </div>
                                @if($item->bmn)
                                    <div class="text-[10px] text-[#62756A] mt-0.5">
                                        Jenis: {{ $item->bmn->jenis ?: 'BMN' }} | Penanggung Jawab Awal: {{ $item->bmn->penanggungjawab ?: '—' }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-3.5 text-[#3C4F44]">
                                {{ $item->bmn?->lokasi ?: '—' }}
                            </td>
                            <td class="py-3 px-3.5 text-center">
                                <span class="inline-block px-2 py-0.5 text-[10px] font-medium text-[#4D6357] bg-[#F2F5F3] border border-[#D9E2DC] rounded">
                                    {{ $kondisiAwal }}
                                </span>
                            </td>
                            <td class="py-3 px-3.5 text-center">
                                <span class="inline-block px-2 py-0.5 text-[11px] font-semibold rounded border {{ $kondisiKembaliBadge }}">
                                    {{ $kondisiKembali }}
                                </span>
                            </td>
                            <td class="py-3 px-3.5 text-[#62756A] italic">
                                {{ $item->keterangan ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-[#62756A]">
                                Tidak ada data rincian barang dalam dokumen pengembalian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- SUMMARY FOOTER --}}
        @php
            $countBaik = $pengembalian->items->filter(fn($i) => ($i->kondisi ?: 'Baik') === 'Baik')->count();
            $countRusakBerat = $pengembalian->items->filter(fn($i) => in_array($i->kondisi, ['Rusak', 'Rusak Berat']))->count();
        @endphp
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-[#E3ECE6] text-xs">
            <div class="text-[#62756A]">
                Total Barang: <span class="font-bold text-[#132219]">{{ $pengembalian->items->count() }} unit</span>
            </div>
            <div class="flex items-center gap-2 flex-wrap text-[11px]">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-[#E6F4EA] text-[#137333] border border-[#CEEAD6] font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#137333]"></span>
                    Baik: <strong>{{ $countBaik }}</strong>
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-[#FDF0EE] text-[#9C322C] border border-[#F8D4D0] font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#9C322C]"></span>
                    Rusak Berat: <strong>{{ $countRusakBerat }}</strong>
                </span>
            </div>
        </div>
    </div>

    {{-- BOTTOM ACTION FOOTER --}}
    <div class="flex items-center justify-between pt-2">
        <a
            href="{{ session('nav_last_url.pengembalians', route('pengembalians.index')) }}"
            class="px-4 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-xl text-xs font-semibold transition"
        >
            &larr; Kembali ke Daftar Pengembalian
        </a>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('pengembalians.print', $pengembalian) }}"
                target="_blank"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#132219] rounded-xl text-xs font-semibold shadow-2xs transition"
            >
                <svg class="w-3.5 h-3.5 text-[#163828]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Berita Acara</span>
            </a>

            <a
                href="{{ route('pengembalians.edit', $pengembalian) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-xl text-xs font-semibold shadow-2xs transition"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Pengembalian</span>
            </a>
        </div>
    </div>

</div>
@endsection
