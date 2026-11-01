@extends('layouts.app')

@section('title', 'Verifikasi Pengajuan Pengembalian — SIBOB')
@section('page-title', 'Verifikasi Pengembalian BMN')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Verifikasi Pengembalian BMN</h1>
            <p class="text-xs text-[#62756A] mt-0.5">
                Daftar pengajuan pengembalian dan pelaporan rusak berat dari pegawai untuk diverifikasi dan diterbitkan Berita Acara.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('pengajuan-pengembalian.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#163828] hover:bg-[#0E281C] text-white text-xs font-semibold shadow-xs transition"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Pengajuan Baru</span>
            </a>
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

    {{-- STATS CARDS --}}
    @php
        $countDiajukan = \App\Models\PengajuanPengembalian::where('status', 'diajukan')->count();
        $countDisetujui = \App\Models\PengajuanPengembalian::where('status', 'disetujui')->count();
        $countDitolak = \App\Models\PengajuanPengembalian::where('status', 'ditolak')->count();
        $countTotal = \App\Models\PengajuanPengembalian::count();
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a
            href="{{ route('admin.pengajuan-pengembalian.index', ['status' => 'diajukan']) }}"
            class="bg-white p-4 rounded-xl border {{ request('status') === 'diajukan' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-[#E3ECE6]' }} shadow-2xs hover:border-amber-400 transition"
        >
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-[#62756A] uppercase tracking-wider">Menunggu Verifikasi</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            </div>
            <div class="text-2xl font-bold text-[#132219] mt-2">{{ $countDiajukan }}</div>
            <p class="text-[10px] text-amber-700 mt-0.5">Memerlukan peninjauan</p>
        </a>

        <a
            href="{{ route('admin.pengajuan-pengembalian.index', ['status' => 'disetujui']) }}"
            class="bg-white p-4 rounded-xl border {{ request('status') === 'disetujui' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-[#E3ECE6]' }} shadow-2xs hover:border-emerald-400 transition"
        >
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-[#62756A] uppercase tracking-wider">Disetujui</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-2xl font-bold text-[#132219] mt-2">{{ $countDisetujui }}</div>
            <p class="text-[10px] text-emerald-700 mt-0.5">BA resmi telah terbit</p>
        </a>

        <a
            href="{{ route('admin.pengajuan-pengembalian.index', ['status' => 'ditolak']) }}"
            class="bg-white p-4 rounded-xl border {{ request('status') === 'ditolak' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-[#E3ECE6]' }} shadow-2xs hover:border-rose-400 transition"
        >
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-[#62756A] uppercase tracking-wider">Ditolak</span>
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            </div>
            <div class="text-2xl font-bold text-[#132219] mt-2">{{ $countDitolak }}</div>
            <p class="text-[10px] text-rose-700 mt-0.5">Pengajuan ditolak</p>
        </a>

        <a
            href="{{ route('admin.pengajuan-pengembalian.index') }}"
            class="bg-white p-4 rounded-xl border {{ !request('status') ? 'border-[#163828] ring-2 ring-[#163828]/20' : 'border-[#E3ECE6]' }} shadow-2xs hover:border-[#163828] transition"
        >
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-[#62756A] uppercase tracking-wider">Semua Pengajuan</span>
                <span class="w-2.5 h-2.5 rounded-full bg-[#163828]"></span>
            </div>
            <div class="text-2xl font-bold text-[#132219] mt-2">{{ $countTotal }}</div>
            <p class="text-[10px] text-[#62756A] mt-0.5">Total riwayat</p>
        </a>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] p-4 lg:p-5 shadow-2xs">
        <form method="GET" action="{{ route('admin.pengajuan-pengembalian.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- SEARCH --}}
            <div>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nomor pengajuan, BA, atau pemohon..."
                    class="w-full text-xs rounded-lg border border-[#E3ECE6] focus:border-[#163828] focus:ring-1 focus:ring-[#163828] py-2 px-3 text-[#132219] placeholder-[#8D9D95]"
                />
            </div>

            {{-- STATUS --}}
            <div>
                <select
                    name="status"
                    class="w-full text-xs rounded-lg border border-[#E3ECE6] bg-white focus:border-[#163828] focus:ring-1 focus:ring-[#163828] py-2 pl-3 pr-8 text-[#132219]"
                >
                    <option value="">-- Semua Status --</option>
                    <option value="diajukan" @selected(request('status') === 'diajukan')>Menunggu Verifikasi</option>
                    <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                    <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
                    <option value="dibatalkan" @selected(request('status') === 'dibatalkan')>Dibatalkan</option>
                </select>
            </div>

            {{-- ALASAN --}}
            <div>
                <select
                    name="alasan"
                    class="w-full text-xs rounded-lg border border-[#E3ECE6] bg-white focus:border-[#163828] focus:ring-1 focus:ring-[#163828] py-2 pl-3 pr-8 text-[#132219]"
                >
                    <option value="">-- Semua Jenis Alasan --</option>
                    <option value="pengembalian_biasa" @selected(request('alasan') === 'pengembalian_biasa')>Pengembalian Layak Pakai</option>
                    <option value="rusak_berat" @selected(request('alasan') === 'rusak_berat')>Rusak Berat (Usul Hapus)</option>
                </select>
            </div>

            {{-- BUTTONS --}}
            <div class="flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 px-4 py-2 bg-[#163828] hover:bg-[#0E281C] text-white rounded-lg text-xs font-semibold transition"
                >
                    Terapkan
                </button>
                @if (request()->hasAny(['search', 'status', 'alasan', 'bidang']))
                    <a
                        href="{{ route('admin.pengajuan-pengembalian.index') }}"
                        class="px-3 py-2 border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-lg text-xs font-semibold transition"
                        title="Reset Filter"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL PENGAJUAN --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4">Nomor & Tanggal</th>
                        <th class="py-3.5 px-4">Pemohon & Bidang</th>
                        <th class="py-3.5 px-3">Jenis Alasan</th>
                        <th class="py-3.5 px-3 text-center">Jml Barang</th>
                        <th class="py-3.5 px-3">Status</th>
                        <th class="py-3.5 px-4">Berita Acara</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6]">
                    @forelse ($pengajuans as $pengajuan)
                        <tr class="hover:bg-[#FDFEFE] transition">
                            {{-- NOMOR & TANGGAL --}}
                            <td class="py-3.5 px-4">
                                <a
                                    href="{{ route('admin.pengajuan-pengembalian.show', $pengajuan) }}"
                                    class="font-mono font-bold text-[#163828] hover:underline"
                                >
                                    {{ $pengajuan->nomor_pengajuan }}
                                </a>
                                <div class="text-[11px] text-[#62756A] mt-0.5">
                                    {{ $pengajuan->tanggal ? \Carbon\Carbon::parse($pengajuan->tanggal)->isoFormat('D MMM Y') : '-' }}
                                </div>
                            </td>

                            {{-- PEMOHON & BIDANG --}}
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-[#132219]">
                                    {{ $pengajuan->pemohonUser?->name ?: '-' }}
                                </div>
                                <div class="text-[11px] text-[#62756A]">
                                    {{ $pengajuan->bidang ?: 'Subbag Tata Usaha' }}
                                </div>
                            </td>

                            {{-- ALASAN --}}
                            <td class="py-3.5 px-3">
                                @if ($pengajuan->alasan === 'rusak_berat')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-800">
                                        Rusak Berat
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        Layak Pakai
                                    </span>
                                @endif
                            </td>

                            {{-- JML BARANG --}}
                            <td class="py-3.5 px-3 text-center">
                                <span class="font-mono font-semibold text-[#132219]">
                                    {{ $pengajuan->items->count() }}
                                </span>
                            </td>

                            {{-- STATUS --}}
                            <td class="py-3.5 px-3">
                                @if ($pengajuan->status === 'diajukan')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">
                                        Menunggu
                                    </span>
                                @elseif ($pengajuan->status === 'disetujui')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        Disetujui
                                    </span>
                                @elseif ($pengajuan->status === 'ditolak')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">
                                        Ditolak
                                    </span>
                                @elseif ($pengajuan->status === 'dibatalkan')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>

                            {{-- NOMOR BA --}}
                            <td class="py-3.5 px-4 font-mono text-[11px]">
                                @if ($pengajuan->nomor_ba)
                                    <span class="text-emerald-800 font-semibold">{{ $pengajuan->nomor_ba }}</span>
                                @else
                                    <span class="text-[#8D9D95] italic">-</span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="py-3.5 px-4 text-center">
                                <a
                                    href="{{ route('admin.pengajuan-pengembalian.show', $pengajuan) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-[#163828] text-[#163828] hover:bg-[#163828] hover:text-white text-xs font-semibold transition"
                                >
                                    <span>Periksa</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-[#62756A]">
                                <svg class="w-10 h-10 mx-auto text-[#8D9D95] mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm font-semibold text-[#132219]">Belum ada data pengajuan</p>
                                <p class="text-xs text-[#8D9D95] mt-0.5">Pengajuan yang masuk dari pegawai akan muncul di daftar ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pengajuans->hasPages())
            <div class="p-4 border-t border-[#E3ECE6]">
                {{ $pengajuans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
