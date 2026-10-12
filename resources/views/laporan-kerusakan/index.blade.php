@extends('layouts.app')

@section('title', 'Laporan Kerusakan BMN — SIBOB')
@section('page-title', 'Laporan Kerusakan')

@section('content')
<div class="space-y-6">

    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">Pemeliharaan & Aduan</div>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Laporan Kerusakan BMN</h1>
            <p class="text-xs text-[#62756A] mt-0.5">Daftar aduan kerusakan aset BMN dari pegawai untuk ditindaklanjuti oleh pengelola inventaris.</p>
        </div>

        <a
            href="{{ route('laporan-kerusakan.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition shrink-0"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Laporkan Kerusakan</span>
        </a>
    </div>

    {{-- TABEL LAPORAN KERUSAKAN --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">No. Laporan</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Barang & Lokasi</th>
                        <th class="py-3.5 px-4">Tingkat Kerusakan</th>
                        <th class="py-3.5 px-4">Pelapor</th>
                        <th class="py-3.5 px-4">Status Aduan</th>
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6]/70 text-xs">
                    @forelse($items as $item)
                        @php
                            $status = $item->status ?? 'Dilaporkan';
                            $statusStyle = match($status) {
                                'Selesai' => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                                'Diproses' => 'bg-[#FEF7E0] text-[#B06000] border-[#FEEFC3]',
                                default => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]',
                            };

                            $tingkat = $item->tingkat ?? 'Ringan';
                            $tingkatStyle = match($tingkat) {
                                'Berat' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                                'Sedang' => 'bg-[#FFF4E5] text-[#B25E00] border-[#FFE2BF]',
                                default => 'bg-[#F7FAF8] text-[#4D6357] border-[#E3ECE6]',
                            };
                        @endphp
                        <tr class="hover:bg-[#F9FCFA] transition">
                            {{-- NO --}}
                            <td class="py-3.5 px-4 text-center text-[#62756A] font-mono text-[11px]">
                                {{ $loop->iteration }}
                            </td>

                            {{-- NOMOR --}}
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                    {{ $item->nomor ?: 'LK-#' . $item->id }}
                                </span>
                            </td>

                            {{-- TANGGAL --}}
                            <td class="py-3.5 px-4 font-mono text-[11px] text-[#4D6357]">
                                {{ $item->tanggal?->format('d M Y') ?: '—' }}
                            </td>

                            {{-- BARANG & LOKASI --}}
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-[#132219]">
                                    {{ $item->nama_barang }}
                                </div>
                                <div class="text-[11px] text-[#62756A] flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <span>{{ $item->lokasi }}</span>
                                </div>
                            </td>

                            {{-- TINGKAT KERUSAKAN --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $tingkatStyle }}">
                                    <span>{{ $tingkat }}</span>
                                </span>
                            </td>

                            {{-- PELAPOR --}}
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-[#132219]">
                                    {{ $item->pelapor_nama ?: 'Pegawai' }}
                                </div>
                                @if($item->pelapor_email)
                                    <div class="text-[10px] text-[#62756A]">
                                        {{ $item->pelapor_email }}
                                    </div>
                                @endif
                            </td>

                            {{-- STATUS --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $statusStyle }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $status === 'Selesai' ? 'bg-[#137333]' : ($status === 'Diproses' ? 'bg-[#B06000]' : 'bg-[#62756A]') }}"></span>
                                    <span>{{ $status }}</span>
                                </span>
                            </td>

                            {{-- AKSI --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    {{-- DETAIL --}}
                                    <a
                                        href="{{ route('laporan-kerusakan.show', $item) }}"
                                        class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                        title="Detail Laporan"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    {{-- EDIT / TANGGAPI (OPERATOR) --}}
                                    @if(auth()->user()->role === 'Operator')
                                        <a
                                            href="{{ route('laporan-kerusakan.edit', $item) }}"
                                            class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                            title="Tanggapi / Perbarui Status"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        {{-- HAPUS --}}
                                        <form
                                            method="POST"
                                            action="{{ route('laporan-kerusakan.destroy', $item) }}"
                                            onsubmit="return confirm('Hapus laporan kerusakan ini?')"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 text-[#9C322C]/70 hover:text-[#9C322C] hover:bg-[#FDF0EE] rounded-lg transition cursor-pointer"
                                                title="Hapus Laporan"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-[#EAF2ED] text-[#163828] flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-[#132219]">Tidak Ada Laporan Kerusakan</div>
                                    <p class="text-xs text-[#62756A]">Semua aset inventaris dalam kondisi terpantau baik atau belum ada laporan baru.</p>
                                    <a
                                        href="{{ route('laporan-kerusakan.create') }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] text-white rounded-lg text-xs font-semibold hover:bg-[#1E4834] transition shadow-2xs"
                                    >
                                        + Laporkan Kerusakan
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
