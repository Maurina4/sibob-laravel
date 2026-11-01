@extends('layouts.app')

@section('title', 'Detail Pengajuan ' . $pengajuan->nomor_pengajuan . ' — SIBOB')
@section('page-title', 'Detail Pengajuan Pengembalian')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    {{-- KEMBALI & HEADER ACTION --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a
                href="{{ route('pengajuan-pengembalian.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Riwayat Pengajuan</span>
            </a>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold tracking-tight text-[#132219] font-mono">{{ $pengajuan->nomor_pengajuan }}</h1>
                @if ($pengajuan->status === 'diajukan')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                        Menunggu Verifikasi
                    </span>
                @elseif ($pengajuan->status === 'disetujui')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        Disetujui
                    </span>
                @elseif ($pengajuan->status === 'ditolak')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                        Ditolak
                    </span>
                @elseif ($pengajuan->status === 'dibatalkan')
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                        Dibatalkan
                    </span>
                @endif
            </div>
            <p class="text-xs text-[#62756A] mt-1">
                Diajukan pada {{ $pengajuan->tanggal ? \Carbon\Carbon::parse($pengajuan->tanggal)->isoFormat('dddd, D MMMM Y') : '-' }}
            </p>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex items-center gap-2">
            @if ($pengajuan->status === 'disetujui')
                <a
                    href="{{ route('pengajuan-pengembalian.cetak-ba', $pengajuan) }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#163828] hover:bg-[#0E281C] text-white text-xs font-semibold shadow-xs transition"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Berita Acara</span>
                </a>
            @endif

            @if ($pengajuan->status === 'diajukan' && $pengajuan->diajukan_oleh === auth()->id())
                <form
                    method="POST"
                    action="{{ route('pengajuan-pengembalian.batalkan', $pengajuan) }}"
                    onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan pengembalian ini?')"
                >
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#9C322C] text-[#9C322C] hover:bg-[#FDF0EE] text-xs font-semibold transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Batalkan Pengajuan</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ALERT BANNER SESUAI STATUS --}}
    @if ($pengajuan->status === 'diajukan')
        <div class="p-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-900 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <strong class="font-semibold block">Menunggu Persetujuan Operator BMN</strong>
                <span>Pengajuan Anda telah tercatat dan saat ini sedang dalam proses verifikasi fisik serta kelengkapan administrasi oleh tim pengelola BMN.</span>
            </div>
        </div>
    @elseif ($pengajuan->status === 'disetujui')
        <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <strong class="font-semibold block">Pengajuan Telah Disetujui</strong>
                <span>
                    Berita Acara resmi telah diterbitkan dengan nomor <strong class="font-mono text-emerald-800">{{ $pengajuan->nomor_ba }}</strong>.
                    Barang telah dikembalikan ke Gudang BMN dan tanggung jawab pemakaian telah diserahterimakan.
                </span>
            </div>
        </div>
    @elseif ($pengajuan->status === 'ditolak')
        <div class="p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-900 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <strong class="font-semibold block">Pengajuan Ditolak</strong>
                <p class="mt-0.5">Catatan Penolakan: <span class="font-semibold italic">{{ $pengajuan->catatan_penolakan ?: 'Tidak ada catatan khusus.' }}</span></p>
            </div>
        </div>
    @elseif ($pengajuan->status === 'dibatalkan')
        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 text-gray-700 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-gray-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            <div>
                <strong class="font-semibold block">Pengajuan Dibatalkan</strong>
                <span>Pengajuan ini telah dibatalkan oleh pemohon sebelum diproses oleh pengelola BMN.</span>
            </div>
        </div>
    @endif

    {{-- CARD 1: INFORMASI UMUM PENGAJUAN --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-7 shadow-2xs space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2.5 h-2.5 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Informasi Pengajuan</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 text-xs">
            <div>
                <span class="text-[#62756A] block text-[11px]">Nomor Pengajuan</span>
                <span class="font-semibold font-mono text-[#132219]">{{ $pengajuan->nomor_pengajuan }}</span>
            </div>
            <div>
                <span class="text-[#62756A] block text-[11px]">Tanggal Pengajuan</span>
                <span class="font-semibold text-[#132219]">{{ $pengajuan->tanggal ? \Carbon\Carbon::parse($pengajuan->tanggal)->isoFormat('D MMMM Y') : '-' }}</span>
            </div>
            <div>
                <span class="text-[#62756A] block text-[11px]">Jenis / Alasan</span>
                @if ($pengajuan->alasan === 'rusak_berat')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-100 text-rose-800">
                        Rusak Berat (Usul Hapus)
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                        Barang Layak Pakai
                    </span>
                @endif
            </div>
            <div>
                <span class="text-[#62756A] block text-[11px]">Nomor Berita Acara</span>
                @if ($pengajuan->nomor_ba)
                    <span class="font-mono font-bold text-emerald-800">{{ $pengajuan->nomor_ba }}</span>
                @else
                    <span class="text-[#8D9D95] italic">Belum terbit</span>
                @endif
            </div>
            <div>
                <span class="text-[#62756A] block text-[11px]">Pemohon</span>
                <span class="font-semibold text-[#132219]">{{ $pengajuan->pemohonUser?->name ?: '-' }}</span>
                @if ($pengajuan->pegawai?->nip)
                    <span class="text-[10px] text-[#62756A] block font-mono">NIP. {{ $pengajuan->pegawai->nip }}</span>
                @endif
            </div>
            <div>
                <span class="text-[#62756A] block text-[11px]">Bidang / Substansi</span>
                <span class="font-semibold text-[#132219]">{{ $pengajuan->bidang ?: '-' }}</span>
            </div>
            <div>
                <span class="text-[#62756A] block text-[11px]">Pengelola BMN / Peninjau</span>
                <span class="font-semibold text-[#132219]">{{ $pengajuan->penyetujuUser?->name ?: '-' }}</span>
                @if ($pengajuan->disetujui_pada)
                    <span class="text-[10px] text-[#62756A] block">{{ \Carbon\Carbon::parse($pengajuan->disetujui_pada)->isoFormat('D MMM Y, HH:mm') }}</span>
                @endif
            </div>
            <div>
                <span class="text-[#62756A] block text-[11px]">Total Item</span>
                <span class="font-semibold text-[#132219]">{{ $pengajuan->items->count() }} Barang</span>
            </div>
        </div>

        @if ($pengajuan->catatan_pemohon)
            <div class="pt-3 border-t border-[#E3ECE6] text-xs">
                <span class="text-[#62756A] block text-[11px] mb-0.5">Catatan Pemohon:</span>
                <div class="p-2.5 rounded-lg bg-[#F7FAF8] border border-[#E3ECE6] text-[#132219]">
                    {{ $pengajuan->catatan_pemohon }}
                </div>
            </div>
        @endif
    </div>

    {{-- CARD 2: DAFTAR BARANG YANG DIAJUKAN --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-7 shadow-2xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#E3ECE6]">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Daftar Rincian Barang</h2>
            </div>
            <span class="text-xs text-[#62756A]">{{ $pengajuan->items->count() }} item barang</span>
        </div>

        <div class="overflow-x-auto border border-[#E3ECE6] rounded-xl">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3 px-3 text-center w-12 border-r border-[#E3ECE6]">No</th>
                        <th class="py-3 px-4 border-r border-[#E3ECE6]">Kode & NUP</th>
                        <th class="py-3 px-4 border-r border-[#E3ECE6]">Nama Barang & Merk</th>
                        <th class="py-3 px-3 text-center border-r border-[#E3ECE6]">Kondisi Awal</th>
                        <th class="py-3 px-3 text-center border-r border-[#E3ECE6]">Kondisi Pengembalian</th>
                        <th class="py-3 px-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6]">
                    @foreach ($pengajuan->items as $item)
                        <tr class="hover:bg-[#FDFEFE] transition">
                            <td class="py-3 px-3 text-center text-[#62756A] font-mono border-r border-[#E3ECE6]">
                                {{ $loop->iteration }}
                            </td>
                            <td class="py-3 px-4 border-r border-[#E3ECE6]">
                                <div class="font-mono text-[11px] font-semibold text-[#163828]">
                                    {{ $item->kode_barang }}
                                </div>
                                <div class="text-[11px] text-[#62756A] font-mono">
                                    NUP: {{ $item->nup ?: '-' }}
                                </div>
                            </td>
                            <td class="py-3 px-4 border-r border-[#E3ECE6]">
                                <div class="font-semibold text-[#132219]">
                                    {{ $item->nama_barang }}
                                </div>
                                <div class="text-[11px] text-[#62756A]">
                                    {{ $item->merek ?: '-' }}
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center border-r border-[#E3ECE6]">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ strtolower($item->kondisi_awal) === 'baik' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $item->kondisi_awal ?: 'Baik' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center border-r border-[#E3ECE6]">
                                @if (strtolower($item->kondisi) === 'baik')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        Baik
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-800">
                                        Rusak Berat
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 space-y-1">
                                @if ($item->deskripsi_kerusakan)
                                    <div class="text-[11px] text-rose-800 bg-rose-50 p-2 rounded border border-rose-200">
                                        <div class="font-semibold">Temuan Kerusakan: {{ $item->tanggal_temuan ? \Carbon\Carbon::parse($item->tanggal_temuan)->isoFormat('D MMM Y') : '-' }}</div>
                                        <div>{{ $item->deskripsi_kerusakan }}</div>
                                        @if ($item->laporanKerusakan)
                                            <div class="mt-1 pt-1 border-t border-rose-200/60 font-mono text-[10px] text-rose-900">
                                                Laporan Kerusakan: <strong class="font-bold">{{ $item->laporanKerusakan->nomor_laporan }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                @if ($item->keterangan)
                                    <div class="text-[11px] text-[#62756A]">
                                        {{ $item->keterangan }}
                                    </div>
                                @endif
                                @if (!$item->deskripsi_kerusakan && !$item->keterangan)
                                    <span class="text-[#8D9D95]">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
