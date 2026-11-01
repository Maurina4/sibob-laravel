@extends('layouts.app')

@section('title', 'Berita Acara Distribusi — SIBOB')
@section('page-title', 'BA Distribusi')

@section('content')
<div class="space-y-6">

    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">Logistik & Penyaluran</div>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Berita Acara Distribusi BMN</h1>
            <p class="text-xs text-[#62756A] mt-0.5">Pencatatan mutasi dan pendistribusian barang antar ruangan atau unit kerja BBPOM.</p>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white p-3.5 rounded-xl border border-[#E3ECE6] shadow-2xs">
        <form method="GET" action="{{ route('distribusi.index') }}" class="flex flex-wrap items-center gap-2.5">
            {{-- SEARCH NO DOKUMEN BA --}}
            <div class="relative flex-1 min-w-[240px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#8D9D95]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari No. Dokumen BA, Kode BMN, NUP, Nama Barang..."
                    class="w-full pl-9 pr-3 py-2 bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] placeholder-[#8D9D95] transition"
                >
            </div>

            {{-- TOMBOL CARI --}}
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-lg text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
            >
                <span>Cari</span>
            </button>

            {{-- RESET --}}
            <a
                href="{{ route('distribusi.index') }}"
                class="inline-flex items-center px-3 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-lg text-xs font-medium transition"
            >
                Reset
            </a>
        </form>
    </div>

    {{-- TABEL DATA DISTRIBUSI --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">No. Dokumen BA</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Kode Barang</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">NUP</th>
                        <th class="py-3.5 px-4">Nama Barang</th>
                        <th class="py-3.5 px-4">Jumlah</th>
                        <th class="py-3.5 px-4">Rute (Asal &rarr; Tujuan)</th>
                        <th class="py-3.5 px-4">Penerima</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6]/70 text-xs">
                    @forelse($items as $item)
                        <tr class="hover:bg-[#F9FCFA] transition">
                            {{-- NO --}}
                            <td class="py-3.5 px-4 text-center text-[#62756A] font-mono text-[11px]">
                                {{ $loop->iteration }}
                            </td>

                            {{-- NOMOR BA --}}
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                    {{ $item->nomor }}
                                </span>
                            </td>

                            {{-- TANGGAL --}}
                            <td class="py-3.5 px-4 font-mono text-[11px] text-[#4D6357]">
                                {{ $item->tanggal?->format('d M Y') ?: '—' }}
                            </td>

                            {{-- KODE BARANG --}}
                            <td class="py-3.5 px-4 font-mono text-[11px] whitespace-nowrap">
                                @php
                                    $kodeVal = $item->kode_barang ?? $item->bmn?->kode_barang;
                                @endphp
                                @if($kodeVal)
                                    <span class="font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                        {{ $kodeVal }}
                                    </span>
                                @else
                                    <span class="text-[#8D9D95] font-mono">—</span>
                                @endif
                            </td>

                            {{-- NUP --}}
                            <td class="py-3.5 px-4 text-center font-mono text-[11px] whitespace-nowrap">
                                @php
                                    $nupVal = $item->nup ?? $item->bmn?->nup;
                                @endphp
                                @if($nupVal !== null && $nupVal !== '')
                                    <span class="font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                        {{ $nupVal }}
                                    </span>
                                @else
                                    <span class="text-[#8D9D95] font-mono">—</span>
                                @endif
                            </td>

                            {{-- NAMA BARANG --}}
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-[#132219]">
                                    {{ $item->nama_barang }}
                                </span>
                            </td>

                            {{-- JUMLAH --}}
                            <td class="py-3.5 px-4 font-medium text-[#132219]">
                                {{ $item->jumlah }} {{ $item->satuan }}
                            </td>

                            {{-- RUTE ASAL -> TUJUAN --}}
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 text-[#3C4F44]">
                                    <span>{{ $item->lokasi_asal ?: 'Gudang BMN' }}</span>
                                    <span class="text-[#8D9D95]">&rarr;</span>
                                    @if(!empty($item->lokasi_tujuan) && $item->lokasi_tujuan !== 'Belum Ditentukan')
                                        <span class="font-semibold text-[#163828]">{{ $item->lokasi_tujuan }}</span>
                                    @else
                                        <span class="inline-flex items-center text-[10px] text-amber-800 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded italic">Belum ditentukan</span>
                                    @endif
                                </div>
                            </td>

                            {{-- PENERIMA --}}
                            <td class="py-3.5 px-4">
                                @if(!empty($item->penerima) && $item->penerima !== 'Belum Ditentukan')
                                    <span class="font-medium text-[#132219]">{{ $item->penerima }}</span>
                                @else
                                    <span class="inline-flex items-center text-[10px] text-amber-800 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded italic">Belum ditentukan</span>
                                @endif
                            </td>

                            {{-- STATUS --}}
                            <td class="py-3.5 px-4 text-center">
                                @if($item->is_pending)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-800 bg-amber-100/90 border border-amber-300 px-2 py-0.5 rounded-full whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                        Menunggu Penempatan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-800 bg-emerald-100/90 border border-emerald-300 px-2 py-0.5 rounded-full whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Selesai Distribusi
                                    </span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    @if($item->is_pending)
                                        <a
                                            href="{{ route('distribusi.edit', array_merge(['distribusi' => $item->id], request()->query())) }}"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-[#163828] hover:bg-[#1E4834] text-white rounded-lg text-[11px] font-semibold transition shadow-2xs whitespace-nowrap"
                                            title="Tentukan Lokasi & Penerima"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                            <span>Distribusikan</span>
                                        </a>
                                    @endif

                                    {{-- DETAIL --}}
                                    <a
                                        href="{{ route('distribusi.show', $item) }}"
                                        class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                        title="Detail Berita Acara"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    @if(!$item->is_pending)
                                        {{-- PRINT --}}
                                        <a
                                            href="{{ route('distribusi.print', $item) }}"
                                            target="_blank"
                                            class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                            title="Cetak Berita Acara"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </a>

                                        {{-- EDIT --}}
                                        <a
                                            href="{{ route('distribusi.edit', array_merge(['distribusi' => $item->id], request()->query())) }}"
                                            class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                            title="Edit Data"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 text-center">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-[#EAF2ED] text-[#163828] flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-[#132219]">Belum Ada Berita Acara Distribusi</div>
                                    <p class="text-xs text-[#62756A]">Belum ada barang yang didistribusikan. Berita Acara Distribusi akan terbit otomatis saat barang baru didaftarkan di Gudang BMN.</p>
                                    <a
                                        href="{{ route('bmns.index') }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] text-white rounded-lg text-xs font-semibold hover:bg-[#1E4834] transition shadow-2xs"
                                    >
                                        Buka Daftar BMN
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="px-5 pb-5">
            <x-table-footer :paginator="$items" />
        </div>
    </div>

</div>
@endsection