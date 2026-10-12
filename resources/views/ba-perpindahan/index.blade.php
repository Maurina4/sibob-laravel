@extends('layouts.app')

@section('title', 'Berita Acara Perpindahan — SIBOB')
@section('page-title', 'BA Perpindahan')

@section('content')
<div class="space-y-6">

    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">Mutasi Internal</div>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Berita Acara Perpindahan BMN</h1>
            <p class="text-xs text-[#62756A] mt-0.5">Pengajuan dan persetujuan perpindahan barang inventaris antar pegawai atau ruangan.</p>
        </div>

        <a
            href="{{ route('ba-perpindahan.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition shrink-0"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Ajukan BA Perpindahan</span>
        </a>
    </div>

    {{-- TABEL DATA BA PERPINDAHAN --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">No. Dokumen BA</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Nama Barang</th>
                        <th class="py-3.5 px-4">Rute (Dari &rarr; Ke)</th>
                        <th class="py-3.5 px-4">Pengaju / Pihak Ke-2</th>
                        <th class="py-3.5 px-4">Status Pengajuan</th>
                        <th class="py-3.5 px-4 text-center w-36">Aksi & Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6]/70 text-xs">
                    @forelse($items as $item)
                        @php
                            $status = $item->status ?? 'Diajukan';
                            $statusStyle = match($status) {
                                'Disetujui' => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                                'Ditolak' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                                default => 'bg-[#FEF7E0] text-[#B06000] border-[#FEEFC3]',
                            };
                        @endphp
                        <tr class="hover:bg-[#F9FCFA] transition">
                            {{-- NO --}}
                            <td class="py-3.5 px-4 text-center text-[#62756A] font-mono text-[11px]">
                                {{ $loop->iteration }}
                            </td>

                            {{-- NOMOR BA --}}
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                    {{ $item->nomor_ba ?: 'BA-MUT/' . $item->id }}
                                </span>
                            </td>

                            {{-- TANGGAL --}}
                            <td class="py-3.5 px-4 font-mono text-[11px] text-[#4D6357]">
                                {{ $item->tanggal?->format('d M Y') ?: '—' }}
                            </td>

                            {{-- NAMA BARANG --}}
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-[#132219]">
                                    {{ $item->nama_barang }}
                                </span>
                            </td>

                            {{-- DARI -> KE --}}
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 text-[#3C4F44]">
                                    <span>{{ $item->dari }}</span>
                                    <span class="text-[#8D9D95]">&rarr;</span>
                                    <span class="font-semibold text-[#163828]">{{ $item->ke ?: '—' }}</span>
                                </div>
                            </td>

                            {{-- PENGAJU & PIHAK KE-2 --}}
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-[#132219]">
                                    {{ $item->pengaju_nama ?: 'Pegawai' }}
                                </div>
                                @if($item->pihak_kedua)
                                    <div class="text-[11px] text-[#62756A]">
                                        Ke: {{ $item->pihak_kedua }}
                                    </div>
                                @endif
                            </td>

                            {{-- STATUS --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $statusStyle }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $status === 'Disetujui' ? 'bg-[#137333]' : ($status === 'Ditolak' ? 'bg-[#9C322C]' : 'bg-[#B06000]') }}"></span>
                                    <span>{{ $status }}</span>
                                </span>
                            </td>

                            {{-- AKSI & PERSETUJUAN --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1 justify-center">

                                    {{-- OPERATOR APPROVE / REJECT JIKA STATUS DIAJUKAN --}}
                                    @if(auth()->user()->role === 'Operator' && $status === 'Diajukan')
                                        <form method="POST" action="{{ route('ba-perpindahan.approve', $item) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                onclick="return confirm('Setujui pengajuan perpindahan barang ini?')"
                                                class="p-1.5 text-[#137333] hover:bg-[#E6F4EA] rounded-lg transition"
                                                title="Setujui Pengajuan"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('ba-perpindahan.reject', $item) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                onclick="return confirm('Tolak pengajuan perpindahan barang ini?')"
                                                class="p-1.5 text-[#9C322C] hover:bg-[#FDF0EE] rounded-lg transition"
                                                title="Tolak Pengajuan"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- DETAIL --}}
                                    <a
                                        href="{{ route('ba-perpindahan.show', $item) }}"
                                        class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                        title="Detail Berita Acara"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    {{-- PRINT --}}
                                    <a
                                        href="{{ route('ba-perpindahan.print', $item) }}"
                                        target="_blank"
                                        class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                        title="Cetak Berita Acara"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </a>

                                    {{-- EDIT (JIKA OPERATOR ATAU BELUM DISETUJUI) --}}
                                    @if($status === 'Diajukan' || auth()->user()->role === 'Operator')
                                        <a
                                            href="{{ route('ba-perpindahan.edit', $item) }}"
                                            class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                            title="Edit Berita Acara"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- HAPUS --}}
                                    @if(auth()->user()->role === 'Operator')
                                        <form
                                            method="POST"
                                            action="{{ route('ba-perpindahan.destroy', $item) }}"
                                            onsubmit="return confirm('Hapus data BA perpindahan ini?')"
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
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-[#132219]">Belum Ada Pengajuan Perpindahan</div>
                                    <p class="text-xs text-[#62756A]">Belum ada permohonan mutasi atau perpindahan barang BMN yang diajukan.</p>
                                    <a
                                        href="{{ route('ba-perpindahan.create') }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] text-white rounded-lg text-xs font-semibold hover:bg-[#1E4834] transition shadow-2xs"
                                    >
                                        + Ajukan BA Perpindahan
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
