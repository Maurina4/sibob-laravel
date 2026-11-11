@extends('layouts.app')

@section('title', 'Verifikasi ' . $pengajuan->nomor_pengajuan . ' — SIBOB')
@section('page-title', 'Verifikasi Pengajuan Pengembalian')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="{ showSetujuiModal: false, showTolakModal: false }">

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
                <span>Kembali ke Daftar Pengajuan</span>
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
                Diajukan oleh <strong class="text-[#132219]">{{ $pengajuan->pemohonUser?->name }}</strong> pada {{ $pengajuan->tanggal ? \Carbon\Carbon::parse($pengajuan->tanggal)->isoFormat('dddd, D MMMM Y') : '-' }}
            </p>
        </div>

        {{-- ACTION BUTTONS OPERATOR --}}
        <div class="flex items-center gap-2">
            @if ($pengajuan->status === 'disetujui')
                <form
                    method="POST"
                    action="{{ route('admin.pengajuan-pengembalian.batalkan', $pengajuan) }}"
                    onsubmit="return confirm('Apakah Anda yakin ingin membatalkan persetujuan pengembalian BMN ini? Status akan diubah menjadi Dibatalkan, Berita Acara dicabut, dan aset BMN dikembalikan ke kondisi/penanggung jawab semula.')"
                    class="inline"
                >
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-[#9C322C] text-[#9C322C] hover:bg-[#FDF0EE] text-xs font-semibold transition cursor-pointer"
                        title="Batalkan Persetujuan"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Batalkan Persetujuan</span>
                    </button>
                </form>

                @if ($pengajuan->alasan === 'rusak_berat')
                    <a
                        href="{{ route('laporan-kerusakan.index', ['search' => $pengajuan->nomor_ba]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#F8D4D0] bg-[#FFF8F7] hover:bg-[#FDF0EE] text-[#9C322C] text-xs font-semibold shadow-xs transition"
                    >
                        <svg class="w-4 h-4 text-[#9C322C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Buka di Daftar Rusak Berat</span>
                    </a>
                @else
                    <a
                        href="{{ route('pengembalians.index', ['search' => $pengajuan->nomor_ba]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E3ECE6] bg-white hover:bg-[#F2F6F3] text-[#132219] text-xs font-semibold shadow-xs transition"
                    >
                        <svg class="w-4 h-4 text-[#163828]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Buka di BA Pengembalian</span>
                    </a>
                @endif
            @elseif ($pengajuan->status === 'dibatalkan')
                <form
                    method="POST"
                    action="{{ route('admin.pengajuan-pengembalian.batalkan', $pengajuan) }}"
                    onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pembatalan pengajuan ini? Status pengajuan akan kembali menjadi Diajukan.')"
                    class="inline"
                >
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-[#163828] bg-[#EAF2ED] hover:bg-[#D5E6DC] text-[#163828] text-xs font-semibold transition cursor-pointer"
                        title="Batalkan Pembatalan (Kembalikan ke Diajukan)"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Batalkan Pembatalan (Aktifkan Kembali)</span>
                    </button>
                </form>
            @endif

            @if ($pengajuan->status === 'diajukan')
                <button
                    type="button"
                    @click="showTolakModal = true"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-[#9C322C] text-[#9C322C] hover:bg-[#FDF0EE] text-xs font-semibold transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Tolak</span>
                </button>

                <button
                    type="button"
                    @click="showSetujuiModal = true"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#163828] hover:bg-[#0E281C] text-white text-xs font-semibold shadow-xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Terima Pengembalian & Terbitkan BA</span>
                </button>
            @endif
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if (session('success'))
        <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-900 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- ALERT BANNER STATUS --}}
    @if ($pengajuan->status === 'disetujui')
        <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <strong class="font-semibold block">Pengajuan Telah Disetujui</strong>
                <span>
                    Nomor Berita Acara: <strong class="font-mono text-emerald-800">{{ $pengajuan->nomor_ba }}</strong>.
                    Disetujui oleh <strong class="text-emerald-900">{{ $pengajuan->penyetujuUser?->name }}</strong> pada
                    {{ \Carbon\Carbon::parse($pengajuan->disetujui_pada)->isoFormat('dddd, D MMMM Y HH:mm') }}.
                </span>
            </div>
        </div>
    @elseif ($pengajuan->status === 'ditolak')
        <div class="p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-900 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <strong class="font-semibold block">Pengajuan Telah Ditolak</strong>
                <p class="mt-0.5">Catatan Penolakan: <span class="font-semibold italic">{{ $pengajuan->catatan_penolakan ?: 'Tidak ada catatan khusus.' }}</span></p>
            </div>
        </div>
    @endif

    {{-- CARD 1: INFORMASI IDENTITAS & DOKUMEN --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-7 shadow-2xs space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2.5 h-2.5 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Data Administrasi Pengajuan</h2>
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
                <span class="text-[#62756A] block text-[11px]">Jenis Pengajuan</span>
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
                    <span class="text-[#8D9D95] italic">Akan diterbitkan saat disetujui</span>
                @endif
            </div>

            <div>
                <span class="text-[#62756A] block text-[11px]">Pemohon (Pihak Pertama)</span>
                <span class="font-semibold text-[#132219]">{{ $pengajuan->pemohonUser?->name ?: '-' }}</span>
                @if ($pengajuan->pegawai?->nip)
                    <span class="text-[10px] text-[#62756A] block font-mono">NIP. {{ $pengajuan->pegawai->nip }}</span>
                @endif
            </div>
            <div>
                <span class="text-[#62756A] block text-[11px]">Bidang / Bagian</span>
                <span class="font-semibold text-[#132219]">{{ $pengajuan->bidang ?: '-' }}</span>
            </div>
            <div>
                <span class="text-[#62756A] block text-[11px]">Operator Penyetuju</span>
                <span class="font-semibold text-[#132219]">{{ $pengajuan->penyetujuUser?->name ?: '-' }}</span>
            </div>
            <div>
                <span class="text-[#62756A] block text-[11px]">Total Jumlah BMN</span>
                <span class="font-semibold text-[#132219]">{{ $pengajuan->items->count() }} Item Barang</span>
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

    {{-- CARD 2: TABEL RINCIAN BARANG --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-7 shadow-2xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#E3ECE6]">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Pemeriksaan Fisik Barang BMN</h2>
            </div>
            <span class="text-xs text-[#62756A]">Periksa kecocokan data fisik dengan sistem</span>
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
                        <th class="py-3 px-4">Rincian & Keterangan</th>
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

    {{-- MODAL PERSETUJUAN --}}
    <div
        x-show="showSetujuiModal"
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
        @keydown.escape.window="showSetujuiModal = false"
    >
        <div
            @click.outside="showSetujuiModal = false"
            class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl"
        >
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-[#132219]">Konfirmasi Terima Pengembalian BMN</h3>
                    <p class="text-xs text-[#62756A]">Pastikan pemeriksaan fisik barang telah selesai dilakukan.</p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-[#F7FAF8] border border-[#E3ECE6] text-xs space-y-2 text-[#4D6357]">
                <p class="font-semibold text-[#132219]">Dengan menerima pengembalian ini:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Nomor Berita Acara resmi (BA-PGM atau BA-RB) akan diterbitkan secara otomatis.</li>
                    <li>Status pengajuan berubah menjadi <strong class="text-emerald-800">Disetujui</strong>.</li>
                    <li>Lokasi barang dipindahkan ke <strong class="text-[#132219]">Gudang BMN</strong> dan penanggung jawab diubah ke <strong class="text-[#132219]">Pengelola BMN</strong>.</li>
                    @if ($pengajuan->alasan === 'rusak_berat')
                        <li class="text-rose-800">Kondisi barang diubah ke <strong>Rusak Berat</strong> dan dokumen <strong>Laporan Kerusakan</strong> otomatis diterbitkan per item barang.</li>
                    @endif
                </ul>
            </div>

            <form method="POST" action="{{ route('admin.pengajuan-pengembalian.setujui', $pengajuan) }}" class="flex items-center justify-end gap-2 pt-2">
                @csrf
                <button
                    type="button"
                    @click="showSetujuiModal = false"
                    class="px-4 py-2 rounded-xl border border-[#E3ECE6] text-xs font-semibold text-[#62756A] hover:bg-[#F7FAF8] transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl bg-[#163828] hover:bg-[#0E281C] text-white text-xs font-semibold shadow-xs transition"
                >
                    Ya, Terima Pengembalian & Terbitkan BA
                </button>
            </form>
        </div>
    </div>

    {{-- MODAL PENOLAKAN --}}
    <div
        x-show="showTolakModal"
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
        @keydown.escape.window="showTolakModal = false"
    >
        <div
            @click.outside="showTolakModal = false"
            class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl"
        >
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-rose-100 text-[#9C322C] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-[#132219]">Tolak Pengajuan Pengembalian</h3>
                    <p class="text-xs text-[#62756A]">Berikan alasan penolakan yang jelas agar pemohon dapat mengetahuinya.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.pengajuan-pengembalian.tolak', $pengajuan) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="catatan_penolakan" class="block text-xs font-semibold text-[#132219] mb-1">
                        Alasan / Catatan Penolakan <span class="text-[#9C322C]">* (minimal 5 karakter)</span>
                    </label>
                    <textarea
                        id="catatan_penolakan"
                        name="catatan_penolakan"
                        rows="4"
                        required
                        minlength="5"
                        placeholder="Contoh: Kondisi fisik barang belum lengkap (adaptor/kabel hilang), mohon dilengkapi sebelum diajukan kembali..."
                        class="w-full text-xs rounded-xl border border-[#E3ECE6] focus:border-[#9C322C] focus:ring-2 focus:ring-[#9C322C]/20 p-3 text-[#132219]"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button
                        type="button"
                        @click="showTolakModal = false"
                        class="px-4 py-2 rounded-xl border border-[#E3ECE6] text-xs font-semibold text-[#62756A] hover:bg-[#F7FAF8] transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2 rounded-xl bg-[#9C322C] hover:bg-[#7D2823] text-white text-xs font-semibold shadow-xs transition"
                    >
                        Tolak Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
