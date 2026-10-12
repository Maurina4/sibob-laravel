@extends('layouts.app')

@section('title', 'Daftar BMN Laboratorium — SIBOB')
@section('page-title', 'Daftar BMN Lab')

@section('content')
<div class="space-y-6">

    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">Sub-Kategori Pengujian</div>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Daftar BMN Laboratorium</h1>
            <p class="text-xs text-[#62756A] mt-0.5">Pemantauan instrumen pengujian dan status siklus kalibrasi berkala BBPOM di Banjarmasin.</p>
        </div>

        <a
            href="{{ route('bmns-lab.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition shrink-0"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah BMN Lab</span>
        </a>
    </div>

    {{-- FILTER TOOLBAR --}}
    <div class="bg-white p-3.5 rounded-xl border border-[#E3ECE6] shadow-2xs">
        <form method="GET" action="{{ route('bmns-lab.index') }}" class="flex flex-wrap items-center gap-2.5">
            {{-- SEARCH --}}
            <div class="relative flex-1 min-w-[220px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#8D9D95]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari kode atau nama instrumen lab..."
                    class="w-full pl-9 pr-3 py-2 bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] placeholder-[#8D9D95] transition"
                >
            </div>

            {{-- FILTER LOKASI --}}
            <div>
                <select
                    name="lokasi"
                    onchange="this.form.submit()"
                    class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 px-3 transition cursor-pointer"
                >
                    <option value="">Semua Lokasi Lab</option>
                    @foreach($lokasiFilters ?? [] as $lokasi)
                        <option value="{{ $lokasi }}" @selected(request('lokasi') === $lokasi)>
                            {{ $lokasi }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- FILTER STATUS KALIBRASI --}}
            <div>
                <select
                    name="status_kalibrasi"
                    onchange="this.form.submit()"
                    class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 px-3 transition cursor-pointer"
                >
                    <option value="">Semua Status Kalibrasi</option>
                    @foreach($statusKalibrasiFilters ?? ['Berlaku', 'Jatuh Tempo', 'Belum Ada Data'] as $status)
                        <option value="{{ $status }}" @selected(request('status_kalibrasi') === $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- CARI BUTTON --}}
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-lg text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
            >
                <span>Cari</span>
            </button>

            {{-- RESET --}}
            @if(request()->filled('search') || request()->filled('lokasi') || request()->filled('status_kalibrasi'))
                <a
                    href="{{ route('bmns-lab.index') }}"
                    class="inline-flex items-center px-3 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-lg text-xs font-medium transition"
                >
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- TABEL DATA LAB --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Kode & NUP</th>
                        <th class="py-3.5 px-4">Nama Alat Lab</th>
                        <th class="py-3.5 px-4">Merek</th>
                        <th class="py-3.5 px-4">Lokasi</th>
                        <th class="py-3.5 px-4">Penanggung Jawab</th>
                        <th class="py-3.5 px-4">Kondisi</th>
                        <th class="py-3.5 px-4">Status Kalibrasi</th>
                        <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6]/70 text-xs">
                    @forelse($items as $index => $item)
                        @php
                            $tanggalKalibrasi = $item->tanggal_kalibrasi;
                            if ($tanggalKalibrasi) {
                                if ($tanggalKalibrasi->isPast() && !$tanggalKalibrasi->isToday()) {
                                    $statusKalibrasi = 'Jatuh Tempo';
                                    $kalibrasiStyle = 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]';
                                } else {
                                    $statusKalibrasi = 'Berlaku';
                                    $kalibrasiStyle = 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]';
                                }
                            } else {
                                $statusKalibrasi = $item->status_kalibrasi ?: 'Belum Ada Data';
                                $kalibrasiStyle = 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]';
                            }

                            $kondisi = $item->kondisi ?: 'Baik';
                            $kondisiStyle = strtolower($kondisi) === 'baik'
                                ? 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]'
                                : 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]';
                        @endphp
                        <tr class="hover:bg-[#F9FCFA] transition">
                            {{-- NO --}}
                            <td class="py-3.5 px-4 text-center text-[#62756A] font-mono text-[11px]">
                                {{ $items->firstItem() + $index }}
                            </td>

                            {{-- KODE BARANG --}}
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                    {{ $item->kode_barang }}@if(isset($item->nup) && $item->nup)-NUP.{{ $item->nup }}@endif
                                </span>
                            </td>

                            {{-- NAMA BARANG --}}
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-[#132219]">
                                    {{ $item->nama_barang }}
                                </div>
                            </td>

                            {{-- MEREK --}}
                            <td class="py-3.5 px-4 text-[#4D6357]">
                                {{ $item->merek ?: '—' }}
                            </td>

                            {{-- LOKASI --}}
                            <td class="py-3.5 px-4 text-[#3C4F44]">
                                {{ $item->lokasi ?: '—' }}
                            </td>

                            {{-- PENANGGUNG JAWAB --}}
                            <td class="py-3.5 px-4 font-medium text-[#132219]">
                                {{ $item->penanggungjawab ?: '—' }}
                            </td>

                            {{-- KONDISI --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium border {{ $kondisiStyle }}">
                                    <span>{{ $kondisi }}</span>
                                </span>
                            </td>

                            {{-- KALIBRASI --}}
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col gap-0.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold border w-fit {{ $kalibrasiStyle }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $statusKalibrasi === 'Berlaku' ? 'bg-[#137333]' : ($statusKalibrasi === 'Jatuh Tempo' ? 'bg-[#9C322C]' : 'bg-[#62756A]') }}"></span>
                                        <span>{{ $statusKalibrasi }}</span>
                                    </span>
                                    @if($tanggalKalibrasi)
                                        <span class="text-[10px] text-[#62756A] font-mono mt-0.5">
                                            s/d {{ $tanggalKalibrasi->format('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- AKSI --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('bmns-lab.edit', $item) }}"
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
                                        action="{{ route('bmns-lab.destroy', $item) }}"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data BMN Lab ini?')"
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
                            <td colspan="9" class="py-12 text-center">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-[#EAF2ED] text-[#163828] flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-[#132219]">Belum Ada Data BMN Lab</div>
                                    <p class="text-xs text-[#62756A]">Belum ada alat laboratorium yang terdaftar.</p>
                                    <a
                                        href="{{ route('bmns-lab.create') }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] text-white rounded-lg text-xs font-semibold hover:bg-[#1E4834] transition shadow-2xs"
                                    >
                                        + Tambah BMN Lab Sekarang
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
            <x-table-footer :paginator="$items" />
        </div>
    </div>

</div>
@endsection
