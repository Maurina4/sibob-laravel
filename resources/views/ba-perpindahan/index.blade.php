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

    {{-- FILTER BAR --}}
    <div class="bg-white p-3.5 rounded-xl border border-[#E3ECE6] shadow-2xs">
        <form method="GET" action="{{ route('ba-perpindahan.index') }}" class="flex flex-wrap items-center gap-2.5">
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
                href="{{ route('ba-perpindahan.index') }}"
                class="inline-flex items-center px-3 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-lg text-xs font-medium transition"
            >
                Reset
            </a>
        </form>
    </div>

    {{-- TABEL DATA BA PERPINDAHAN --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Nomor (SIPANDA)</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Kode Barang</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">NUP</th>
                        <th class="py-3.5 px-4">Nama Barang</th>
                        <th class="py-3.5 px-4">Rute (Dari &rarr; Ke)</th>
                        <th class="py-3.5 px-4">Pengaju</th>
                        <th class="py-3.5 px-4">Pihak I (Penyerah)</th>
                        <th class="py-3.5 px-4">Pihak II (Penerima)</th>
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
                                'Dibatalkan' => 'bg-[#F1F3F4] text-[#5F6368] border-[#DADCE0]',
                                default => 'bg-[#FEF7E0] text-[#B06000] border-[#FEEFC3]',
                            };
                            $dotColor = match($status) {
                                'Disetujui' => 'bg-[#137333]',
                                'Ditolak' => 'bg-[#9C322C]',
                                'Dibatalkan' => 'bg-[#5F6368]',
                                default => 'bg-[#B06000]',
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

                            {{-- DARI -> KE --}}
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 text-[#3C4F44]">
                                    <span>{{ $item->dari }}</span>
                                    <span class="text-[#8D9D95]">&rarr;</span>
                                    <span class="font-semibold text-[#163828]">{{ $item->ke ?: '—' }}</span>
                                </div>
                            </td>

                            {{-- PENGAJU --}}
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-[#132219]">
                                    {{ $item->pengajuPegawai ? $item->pengajuPegawai->nama . ($item->pengajuPegawai->nip ? " ({$item->pengajuPegawai->nip})" : '') : ($item->pengaju_nama ?: '—') }}
                                </div>
                                @if($item->pengajuPegawai && ($item->pengajuPegawai->unit_kerja || $item->pengajuPegawai->jabatan))
                                    <div class="text-[10px] text-[#62756A] mt-0.5">
                                        {{ $item->pengajuPegawai->unit_kerja ?: $item->pengajuPegawai->jabatan }}
                                    </div>
                                @endif
                            </td>

                            {{-- PIHAK I (PENYERAH) --}}
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-[#132219]">
                                    {{ $item->pihakPertamaPegawai ? $item->pihakPertamaPegawai->nama . ($item->pihakPertamaPegawai->nip ? " ({$item->pihakPertamaPegawai->nip})" : '') : ($item->pihak_pertama ?: ($item->bmn ? $item->bmn->penanggungjawab : '—')) }}
                                </div>
                            </td>

                            {{-- PIHAK II (PENERIMA) --}}
                            <td class="py-3.5 px-4">
                                @if($item->pihak_kedua)
                                    <div class="font-medium text-[#163828]">
                                        {{ $item->pihak_kedua }}
                                    </div>
                                @else
                                    <span class="inline-flex items-center text-[11px] text-[#B06000] italic bg-[#FEF7E0] px-2 py-0.5 rounded border border-[#FEEFC3]">
                                        Belum ditentukan
                                    </span>
                                @endif
                            </td>

                            {{-- STATUS --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $statusStyle }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                    <span>{{ $status }}</span>
                                </span>
                                @if($status === 'Ditolak' && $item->catatan_operator)
                                    <div class="mt-1 text-[10px] text-[#9C322C] max-w-[160px] truncate" title="{{ $item->catatan_operator }}">
                                        Alasan: {{ $item->catatan_operator }}
                                    </div>
                                @endif
                            </td>

                            {{-- AKSI & PERSETUJUAN --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1 justify-center">

                                    {{-- OPERATOR BUKA FORM AKSI (SAAT DIAJUKAN / DIBATALKAN) --}}
                                    @if(auth()->user()->role === 'Operator' && in_array($status, ['Diajukan', 'Dibatalkan']))
                                        <a
                                            href="{{ route('ba-perpindahan.show', $item) }}#form-aksi"
                                            class="p-1.5 text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                            title="Buka Form Aksi (Tentukan Penerima & Keputusan)"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- OPERATOR BATALKAN KEPUTUSAN JIKA SUDAH DISETUJUI / DITOLAK --}}
                                    @if(auth()->user()->role === 'Operator' && in_array($status, ['Disetujui', 'Ditolak']))
                                        <form method="POST" action="{{ route('ba-perpindahan.cancel-decision', $item) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                onclick="return confirm('Batalkan keputusan {{ $status }} ini? Status akan diubah menjadi Dibatalkan dan Anda dapat memutuskan ulang kembali.')"
                                                class="p-1.5 text-[#5F6368] hover:text-[#D93025] hover:bg-[#F1F3F4] rounded-lg transition"
                                                title="Batalkan Keputusan {{ $status }} (Putuskan Ulang)"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
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
                                    @if($item->canBePrinted())
                                        <a
                                            href="{{ route('ba-perpindahan.print', $item) }}"
                                            target="_blank"
                                            class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                            title="Cetak Berita Acara (Srikandi PDF - A4/F4)"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </a>
                                    @else
                                        <button
                                            type="button"
                                            disabled
                                            class="p-1.5 text-[#A0B0A7] cursor-not-allowed opacity-40 rounded-lg"
                                            title="Belum dapat dicetak: Pegawai Penerima (Pihak Kedua) belum ditentukan"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </button>
                                    @endif

                                    {{-- EDIT (JIKA OPERATOR ATAU BELUM DISETUJUI) --}}
                                    @if($status === 'Diajukan' || auth()->user()->role === 'Operator')
                                        <a
                                            href="{{ route('ba-perpindahan.edit', array_merge(['baPerpindahan' => $item->id], request()->query())) }}"
                                            class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                            title="Edit Berita Acara"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- HAPUS (DAPAT DIHAPUS JIKA BELUM DISETUJUI) --}}
                                    @if($status !== 'Disetujui')
                                        <form
                                            method="POST"
                                            action="{{ route('ba-perpindahan.destroy', $item) }}"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data BA perpindahan ini?')"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="p-1.5 text-[#9C322C]/70 hover:text-[#9C322C] hover:bg-[#FDF0EE] rounded-lg transition cursor-pointer"
                                                title="Hapus BA Perpindahan"
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
                            <td colspan="12" class="py-12 text-center">
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

