@extends('layouts.app')

@section('title', 'Pengajuan Pengembalian — SIBOB')
@section('page-title', 'Pengajuan Pengembalian BMN')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    {{-- HEADER HALAMAN & TOMBOL TAMBAH --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">
                {{ auth()->check() && auth()->user()->role === 'Operator' ? 'Kelola & Penerimaan Pengembalian' : 'Layanan Pegawai' }}
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Pengajuan Pengembalian BMN</h1>
            <p class="text-xs text-[#62756A] mt-0.5">
                {{ auth()->check() && auth()->user()->role === 'Operator' ? 'Daftar permohonan pengembalian aset dari pegawai untuk diverifikasi, diterima, dan diterbitkan Berita Acara.' : 'Daftar permohonan pengembalian aset inventaris yang Anda ajukan ke Pengelola BMN.' }}
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('pengajuan-pengembalian.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition shrink-0"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Pengajuan Baru</span>
            </a>
        </div>
    </div>

    {{-- ALERT NOTIFIKASI --}}
    @if(session('success'))
        <div class="p-4 rounded-xl border border-[#CDE1D4] bg-[#EAF2ED] text-[#163828] text-xs flex items-center gap-2.5">
            <svg class="w-4 h-4 shrink-0 text-[#137333]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl border border-[#F8D4D0] bg-[#FDF0EE] text-[#9C322C] text-xs flex items-center gap-2.5">
            <svg class="w-4 h-4 shrink-0 text-[#9C322C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- FILTER & SEARCH --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] p-4 shadow-2xs space-y-3">
        <form method="GET" action="{{ route('pengajuan-pengembalian.index') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 flex-wrap">
                <a
                    href="{{ route('pengajuan-pengembalian.index') }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ !request('status') && !request('alasan') ? 'bg-[#163828] text-white shadow-2xs' : 'bg-[#F7FAF8] text-[#62756A] hover:bg-[#EAF2ED]' }}"
                >
                    Semua
                </a>
                <a
                    href="{{ route('pengajuan-pengembalian.index', array_merge(request()->query(), ['status' => 'diajukan'])) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'diajukan' ? 'bg-[#FEF7E0] text-[#B06000] border border-[#FEEFC3]' : 'bg-[#F7FAF8] text-[#62756A] hover:bg-[#EAF2ED]' }}"
                >
                    Diajukan
                </a>
                <a
                    href="{{ route('pengajuan-pengembalian.index', array_merge(request()->query(), ['status' => 'disetujui'])) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'disetujui' ? 'bg-[#EAF2ED] text-[#137333] border border-[#CDE1D4]' : 'bg-[#F7FAF8] text-[#62756A] hover:bg-[#EAF2ED]' }}"
                >
                    Disetujui
                </a>
                <a
                    href="{{ route('pengajuan-pengembalian.index', array_merge(request()->query(), ['status' => 'ditolak'])) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'ditolak' ? 'bg-[#FDF0EE] text-[#9C322C] border border-[#F8D4D0]' : 'bg-[#F7FAF8] text-[#62756A] hover:bg-[#EAF2ED]' }}"
                >
                    Ditolak
                </a>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari SIPANDA / No. BA..."
                        class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828]"
                    >
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#8D9D95]">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
                <button
                    type="submit"
                    class="px-3.5 py-1.5 bg-[#163828] text-white rounded-lg text-xs font-semibold hover:bg-[#1E4834] transition"
                >
                    Cari
                </button>
            </div>
        </form>
    </div>

    {{-- TABEL RIWAYAT PENGAJUAN --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] shadow-2xs overflow-hidden">
        @if($pengajuans->isEmpty())
            <div class="py-12 text-center text-xs text-[#8D9D95] space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-[#F7FAF8] border border-[#E3ECE6] text-[#8D9D95] mx-auto flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p>Belum ada pengajuan pengembalian yang tercatat.</p>
                <a
                    href="{{ route('pengajuan-pengembalian.create') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#163828] hover:underline"
                >
                    <span>Klik di sini untuk membuat pengajuan baru</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-[#F7FAF8] text-[#4A5D52] font-semibold border-b border-[#E3ECE6] whitespace-nowrap">
                        <tr>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Nomor Pengajuan</th>
                            <th class="py-3 px-4">Alasan Pengembalian</th>
                            <th class="py-3 px-4 text-center">Jumlah Barang</th>
                            <th class="py-3 px-4">Nomor Berita Acara</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3ECE6] text-[#132219]">
                        @foreach($pengajuans as $pengajuan)
                            <tr class="hover:bg-[#FBFDFB] transition">
                                <td class="py-3 px-4 font-mono text-[11px] text-[#62756A] whitespace-nowrap">
                                    {{ $pengajuan->tanggal->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 font-mono">
                                    <a href="{{ auth()->check() && auth()->user()->role === 'Operator' ? route('admin.pengajuan-pengembalian.show', $pengajuan) : route('pengajuan-pengembalian.show', $pengajuan) }}" class="font-bold text-[#163828] hover:underline font-mono">
                                        {{ $pengajuan->nomor_pengajuan }}
                                    </a>
                                    @if(auth()->check() && auth()->user()->role === 'Operator' && ($pengajuan->pemohonUser || $pengajuan->nama_pegawai))
                                        <div class="text-[11px] text-[#4A5D52] font-sans font-medium mt-0.5">
                                            {{ $pengajuan->pemohonUser?->name ?: $pengajuan->nama_pegawai }}
                                            @if($pengajuan->bidang)
                                                <span class="text-[#8D9D95]">({{ $pengajuan->bidang }})</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if($pengajuan->nomor_sipanda)
                                        <div class="text-[10px] text-[#8D9D95]">SIPANDA: {{ $pengajuan->nomor_sipanda }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if($pengajuan->alasan === 'rusak_berat')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FDF0EE] text-[#9C322C] border border-[#F8D4D0]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#9C322C]"></span>
                                            Rusak Berat
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#EAF2ED] text-[#163828] border border-[#CDE1D4]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#163828]"></span>
                                            Pengembalian Biasa
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-bold">
                                    {{ $pengajuan->items->count() }} item
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-[#163828] font-semibold whitespace-nowrap">
                                    {{ $pengajuan->nomor_ba ?: '-' }}
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if($pengajuan->status === 'disetujui')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EAF2ED] text-[#137333] border border-[#CDE1D4]">
                                            Disetujui
                                        </span>
                                    @elseif($pengajuan->status === 'diajukan')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FEF7E0] text-[#B06000] border border-[#FEEFC3]">
                                            Diajukan
                                        </span>
                                    @elseif($pengajuan->status === 'ditolak')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FDF0EE] text-[#9C322C] border border-[#F8D4D0]">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                            Dibatalkan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- TOMBOL LIHAT DETAIL --}}
                                        <a
                                            href="{{ auth()->check() && auth()->user()->role === 'Operator' ? route('admin.pengajuan-pengembalian.show', $pengajuan) : route('pengajuan-pengembalian.show', $pengajuan) }}"
                                            class="p-1.5 rounded-lg border border-[#E3ECE6] hover:bg-[#F2F6F3] text-[#163828] transition"
                                            title="Lihat Detail Pengajuan"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        {{-- AKSI SAAT DIAJUKAN: TANDA CENTANG (TERIMA) & TANDA SILANG (BATALKAN/TOLAK) --}}
                                        @if($pengajuan->status === 'diajukan')
                                            {{-- TANDA CENTANG: TERIMA PENGEMBALIAN LANGSUNG (KHUSUS OPERATOR) --}}
                                            @if(auth()->check() && auth()->user()->role === 'Operator')
                                                <form method="POST" action="{{ route('admin.pengajuan-pengembalian.setujui', $pengajuan) }}" onsubmit="return confirm('Apakah Anda yakin ingin menerima pengembalian BMN {{ $pengajuan->nomor_pengajuan }} dan menerbitkan Berita Acara?')">
                                                    @csrf
                                                    <input type="hidden" name="redirect_to" value="{{ route('pengajuan-pengembalian.index') }}">
                                                    <button
                                                        type="submit"
                                                        class="p-1.5 rounded-lg border border-[#CDE1D4] bg-[#EAF2ED] hover:bg-[#D5E6DC] text-[#137333] transition cursor-pointer"
                                                        title="Terima Pengembalian"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- TANDA SILANG: BATALKAN ATAU TOLAK --}}
                                            @if($pengajuan->diajukan_oleh === auth()->id())
                                                <form method="POST" action="{{ route('pengajuan-pengembalian.batalkan', $pengajuan) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?')">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="p-1.5 rounded-lg border border-[#F8D4D0] hover:bg-[#FDF0EE] text-[#9C322C] transition cursor-pointer"
                                                        title="Batalkan Pengajuan"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @elseif(auth()->check() && auth()->user()->role === 'Operator')
                                                <a
                                                    href="{{ route('admin.pengajuan-pengembalian.show', $pengajuan) }}"
                                                    class="p-1.5 rounded-lg border border-[#F8D4D0] hover:bg-[#FDF0EE] text-[#9C322C] transition cursor-pointer"
                                                    title="Tolak Pengajuan"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </a>
                                            @endif
                                        @endif

                                        {{-- TOMBOL CETAK BERITA ACARA (JIKA DISETUJUI) --}}
                                        @if($pengajuan->status === 'disetujui')
                                            <a
                                                href="{{ route('pengajuan-pengembalian.cetak-ba', $pengajuan) }}"
                                                target="_blank"
                                                class="p-1.5 rounded-lg border border-[#CDE1D4] bg-[#EAF2ED] hover:bg-[#D5E6DC] text-[#163828] transition"
                                                title="Cetak Berita Acara"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pengajuans->hasPages())
                <div class="p-4 border-t border-[#E3ECE6]">
                    {{ $pengajuans->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
