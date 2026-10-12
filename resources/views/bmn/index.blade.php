@extends('layouts.app')

@section('title', 'Daftar BMN — SIBOB')
@section('page-title', 'Daftar BMN')

@section('content')
<div class="space-y-6">

    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">Manajemen Inventaris</div>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Daftar Barang Milik Negara</h1>
        </div>

        <a
            href="{{ route('bmns.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition shrink-0"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah BMN</span>
        </a>
    </div>

    {{-- FILTER BAR --}}
    @include('bmn.partials.filter-bar')

    {{-- TABEL BMN --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Kode & NUP</th>
                        <th class="py-3.5 px-4">Nama Barang</th>
                        <th class="py-3.5 px-4">Jenis</th>
                        <th class="py-3.5 px-4">Merek</th>
                        <th class="py-3.5 px-4">Lokasi</th>
                        <th class="py-3.5 px-4">Penanggung Jawab</th>
                        <th class="py-3.5 px-4">Kondisi</th>
                        <th class="py-3.5 px-4">Pemeliharaan</th>
                        <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6]/70 text-xs">
                    @forelse($bmns as $index => $bmn)
                        <tr class="hover:bg-[#F9FCFA] transition">
                            {{-- NO --}}
                            <td class="py-3.5 px-4 text-center text-[#62756A] font-mono text-[11px]">
                                {{ $bmns->firstItem() + $index }}
                            </td>

                            {{-- KODE BARANG --}}
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                    {{ $bmn->kode_barang }}@if($bmn->nup)-NUP.{{ $bmn->nup }}@endif
                                </span>
                            </td>

                            {{-- NAMA BARANG --}}
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-[#132219]">
                                    {{ $bmn->nama_barang }}
                                </div>
                            </td>

                            {{-- JENIS --}}
                            <td class="py-3.5 px-4">
                                <x-bmn-badge :type="$bmn->jenis" />
                            </td>

                            {{-- MEREK --}}
                            <td class="py-3.5 px-4 text-[#4D6357]">
                                {{ $bmn->merek ?: '—' }}
                            </td>

                            {{-- LOKASI --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 text-[#3C4F44]">
                                    <svg class="w-3.5 h-3.5 text-[#8D9D95] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <span>{{ $bmn->lokasi ?: '—' }}</span>
                                </span>
                            </td>

                            {{-- PENANGGUNG JAWAB --}}
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-[#132219]">
                                    {{ $bmn->penanggungjawab ?: '—' }}
                                </div>
                                @if($bmn->penanggungjawab_nip)
                                    <div class="text-[10px] text-[#62756A] font-mono">
                                        NIP. {{ $bmn->penanggungjawab_nip }}
                                    </div>
                                @endif
                            </td>

                            {{-- KONDISI --}}
                            <td class="py-3.5 px-4">
                                @php
                                    $kondisi = $bmn->kondisi ?: 'Baik';
                                    $kondisiStyle = match(strtolower($kondisi)) {
                                        'baik' => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                                        'rusak', 'rusak berat', 'rusak ringan' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                                        default => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium border {{ $kondisiStyle }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ strtolower($kondisi) === 'baik' ? 'bg-[#137333]' : 'bg-[#9C322C]' }}"></span>
                                    <span>{{ $kondisi }}</span>
                                </span>
                            </td>

                            {{-- JADWAL --}}
                            <td class="py-3.5 px-4 text-[#4D6357]">
                                @if($bmn->jadwal_pemeliharaan)
                                    <span class="inline-flex items-center gap-1 font-mono text-[11px]">
                                        <svg class="w-3.5 h-3.5 text-[#8D9D95]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ \Carbon\Carbon::parse($bmn->jadwal_pemeliharaan)->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-[#8D9D95] font-mono">—</span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('bmns.edit', $bmn) }}"
                                        class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                        title="Edit Data"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    {{-- HAPUS --}}
                                    <form
                                        method="POST"
                                        action="{{ route('bmns.destroy', $bmn) }}"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data BMN ini? Data akan dipindahkan ke Data Terhapus.')"
                                        class="inline"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 text-[#9C322C]/70 hover:text-[#9C322C] hover:bg-[#FDF0EE] rounded-lg transition cursor-pointer"
                                            title="Hapus Data"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-[#EAF2ED] text-[#163828] flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-[#132219]">Belum Ada Data BMN</div>
                                    <p class="text-xs text-[#62756A]">Belum ada barang inventaris yang terdaftar atau sesuai kriteria filter pencarian Anda.</p>
                                    <a
                                        href="{{ route('bmns.create') }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] text-white rounded-lg text-xs font-semibold hover:bg-[#1E4834] transition shadow-2xs"
                                    >
                                        + Tambah BMN Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- FOOTER / PAGINATION --}}
        <div class="px-5 pb-5">
            <x-table-footer :paginator="$bmns" />
        </div>
    </div>

</div>
@endsection