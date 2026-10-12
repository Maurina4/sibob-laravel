@extends('layouts.app')

@section('title', 'Data Terhapus — SIBOB')
@section('page-title', 'Data Terhapus')

@section('content')
<div class="space-y-6">

    {{-- HEADER HALAMAN --}}
    <div>
        <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">Pemulihan & Recycle Bin</div>
        <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Data Terhapus (Arsip Sementara)</h1>
        <p class="text-xs text-[#62756A] mt-0.5">
            Data yang dihapus diamankan di sini sebelum dihapus permanen. Gunakan tombol pulihkan untuk mengembalikan data ke modul aktif.
        </p>
    </div>

    {{-- INFO CALLOUT --}}
    <div class="p-4 rounded-2xl bg-white border border-[#E3ECE6] shadow-2xs flex items-start gap-3.5">
        <span class="w-8 h-8 rounded-xl bg-[#EAF2ED] text-[#163828] flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </span>
        <div class="text-xs">
            <strong class="font-semibold text-[#132219] block mb-0.5">Mekanisme Soft Deletes</strong>
            <p class="text-[#4D6357] leading-relaxed">
                Setiap penghapusan aset BMN atau dokumen transaksi dicatat jejak auditnya (waktu dan user penghapus). Data dapat dikembalikan secara utuh tanpa merusak relasi database.
            </p>
        </div>
    </div>

    {{-- TOOLBAR PENCARIAN --}}
    <div class="bg-white p-3.5 rounded-xl border border-[#E3ECE6] shadow-2xs">
        <form method="GET" action="{{ route('data-terhapus.index') }}" class="flex flex-wrap items-center gap-2.5">
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
                    placeholder="Cari data yang dihapus..."
                    class="w-full pl-9 pr-3 py-2 bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] placeholder-[#8D9D95] transition"
                >
            </div>

            {{-- FILTER JENIS --}}
            <div>
                <select
                    name="jenis"
                    onchange="this.form.submit()"
                    class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 px-3 transition cursor-pointer"
                >
                    <option value="">Semua Kategori Data</option>
                    @foreach($jenis as $item)
                        <option value="{{ $item }}" @selected(request('jenis') === $item)>
                            {{ $item }}
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
            @if(request('search') || request('jenis'))
                <a
                    href="{{ route('data-terhapus.index') }}"
                    class="inline-flex items-center px-3 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-lg text-xs font-medium transition"
                >
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- TABEL DATA TERHAPUS --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] overflow-hidden shadow-2xs">
        @if($dataTerhapus->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                            <th class="py-3.5 px-4 text-center w-12">No</th>
                            <th class="py-3.5 px-4">Kategori Modul</th>
                            <th class="py-3.5 px-4">Nama / Nomor Data</th>
                            <th class="py-3.5 px-4">Waktu Dihapus</th>
                            <th class="py-3.5 px-4">Dihapus Oleh</th>
                            <th class="py-3.5 px-4 text-center w-28">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3ECE6]/70 text-xs">
                        @foreach($dataTerhapus as $item)
                            <tr class="hover:bg-[#F9FCFA] transition">
                                {{-- NO --}}
                                <td class="py-3.5 px-4 text-center text-[#62756A] font-mono text-[11px]">
                                    {{ $dataTerhapus->firstItem() + $loop->index }}
                                </td>

                                {{-- JENIS --}}
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-[#EAF2ED] text-[#163828] border border-[#D5E5DA]">
                                        {{ $item->jenis }}
                                    </span>
                                </td>

                                {{-- NAMA --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-[#132219]">
                                        {{ $item->nama }}
                                    </div>
                                </td>

                                {{-- WAKTU DIHAPUS --}}
                                <td class="py-3.5 px-4 font-mono text-[11px] text-[#4D6357]">
                                    @if($item->dihapus_pada)
                                        <div>{{ $item->dihapus_pada->format('d/m/Y') }}</div>
                                        <div class="text-[10px] text-[#8D9D95]">{{ $item->dihapus_pada->format('H:i') }} WITA</div>
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- DIHAPUS OLEH --}}
                                <td class="py-3.5 px-4 text-[#4D6357]">
                                    {{ $item->dihapus_oleh ?? '—' }}
                                </td>

                                {{-- AKSI PULIHKAN --}}
                                <td class="py-3.5 px-4 text-center">
                                    <form
                                        method="POST"
                                        action="{{ route('data-terhapus.restore', $item) }}"
                                        onsubmit="return confirm('Apakah Anda yakin ingin memulihkan data ini kembali ke modul aktif?')"
                                        class="inline"
                                    >
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#EAF5EE] hover:bg-[#D7EDE0] border border-[#BDE0CB] text-[#1E5E38] rounded-lg text-xs font-semibold transition cursor-pointer shadow-2xs"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.038 8.038 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <span>Pulihkan</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="px-5 pb-5">
                <x-table-footer :paginator="$dataTerhapus" />
            </div>
        @else
            <div class="py-16 text-center">
                <div class="max-w-xs mx-auto space-y-3">
                    <div class="w-12 h-12 rounded-full bg-[#EAF2ED] text-[#163828] flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-[#132219]">Recycle Bin Bersih</div>
                    <p class="text-xs text-[#62756A]">Tidak ada data yang sedang berada di tempat penampungan terhapus.</p>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
