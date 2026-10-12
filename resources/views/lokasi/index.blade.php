@extends('layouts.app')

@section('title', 'Master Data Lokasi — SIBOB')
@section('page-title', 'Master Lokasi')

@section('content')
<div class="space-y-6">

    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">Sistem & Master Data</div>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Daftar Lokasi & Ruangan</h1>
            <p class="text-xs text-[#62756A] mt-0.5">Kelola penamaan ruangan dan lokasi penempatan aset inventaris BBPOM di Banjarmasin.</p>
        </div>

        <a
            href="{{ route('lokasis.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition shrink-0"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Lokasi</span>
        </a>
    </div>

    {{-- SEARCH CARD --}}
    <div class="bg-white p-3.5 rounded-xl border border-[#E3ECE6] shadow-2xs">
        <form method="GET" action="{{ route('lokasis.index') }}" class="flex flex-wrap items-center gap-2.5">
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
                    placeholder="Cari nama lokasi atau ruangan..."
                    class="w-full pl-9 pr-3 py-2 bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] placeholder-[#8D9D95] transition"
                >
            </div>

            <button
                type="submit"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-lg text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
            >
                <span>Cari</span>
            </button>

            @if(request()->filled('search'))
                <a
                    href="{{ route('lokasis.index') }}"
                    class="inline-flex items-center px-3 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-lg text-xs font-medium transition"
                >
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- TABEL DATA LOKASI --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Nama Lokasi / Ruangan</th>
                        <th class="py-3.5 px-4 text-center w-36">Jumlah Aset Terdaftar</th>
                        <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6]/70 text-xs">
                    @forelse($lokasis as $index => $lokasi)
                        @php
                            $jumlahAset = \App\Models\Bmn::where('lokasi', $lokasi->nama)->where('dihapus', false)->count();
                        @endphp
                        <tr class="hover:bg-[#F9FCFA] transition">
                            {{-- NO --}}
                            <td class="py-3.5 px-4 text-center text-[#62756A] font-mono text-[11px]">
                                {{ $lokasis->firstItem() + $index }}
                            </td>

                            {{-- NAMA LOKASI (WITH INLINE EDIT) --}}
                            <td class="py-3.5 px-4">
                                <div id="lokasi-display-{{ $lokasi->id }}" class="flex items-center gap-2 group">
                                    <div class="flex items-center gap-2 font-semibold text-[#132219]">
                                        <svg class="w-4 h-4 text-[#163828]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>{{ $lokasi->nama }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        onclick="mulaiEditLokasi({{ $lokasi->id }})"
                                        class="opacity-0 group-hover:opacity-100 p-1 text-[#62756A] hover:text-[#163828] hover:bg-[#EAF2ED] rounded transition cursor-pointer"
                                        title="Ubah nama cepat"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </div>

                                {{-- FORM EDIT INLINE --}}
                                <form
                                    id="lokasi-edit-{{ $lokasi->id }}"
                                    method="POST"
                                    action="{{ route('lokasis.update', $lokasi) }}"
                                    class="hidden"
                                >
                                    @csrf
                                    @method('PUT')
                                    <div class="flex items-center gap-1.5">
                                        <input
                                            type="text"
                                            name="nama"
                                            value="{{ $lokasi->nama }}"
                                            class="lokasi-inline-input border border-[#E3ECE6] rounded-md text-xs py-1 px-2.5 bg-white text-[#132219] focus:border-[#163828]"
                                            maxlength="255"
                                            required
                                        >
                                        <button
                                            type="submit"
                                            class="px-2.5 py-1 bg-[#163828] text-white rounded text-xs font-semibold hover:bg-[#1E4834]"
                                        >
                                            Simpan
                                        </button>
                                        <button
                                            type="button"
                                            onclick="batalEditLokasi({{ $lokasi->id }})"
                                            class="px-2 py-1 bg-white border border-[#E3ECE6] text-[#62756A] rounded text-xs hover:bg-[#F2F6F3]"
                                        >
                                            Batal
                                        </button>
                                    </div>
                                </form>
                            </td>

                            {{-- JUMLAH ASET --}}
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#EAF2ED] text-[#163828] border border-[#D5E5DA]">
                                    {{ $jumlahAset }} aset
                                </span>
                            </td>

                            {{-- AKSI --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    {{-- EDIT BIASA --}}
                                    <a
                                        href="{{ route('lokasis.edit', $lokasi) }}"
                                        class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                        title="Edit Lokasi"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    {{-- HAPUS --}}
                                    <form
                                        method="POST"
                                        action="{{ route('lokasis.destroy', $lokasi) }}"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?')"
                                        class="inline"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 text-[#9C322C]/70 hover:text-[#9C322C] hover:bg-[#FDF0EE] rounded-lg transition cursor-pointer"
                                            title="Hapus Lokasi"
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
                            <td colspan="4" class="py-12 text-center">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-[#EAF2ED] text-[#163828] flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-[#132219]">Belum Ada Master Lokasi</div>
                                    <p class="text-xs text-[#62756A]">Tambahkan nama ruangan atau gedung baru ke dalam sistem.</p>
                                    <a
                                        href="{{ route('lokasis.create') }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] text-white rounded-lg text-xs font-semibold hover:bg-[#1E4834] transition shadow-2xs"
                                    >
                                        + Tambah Lokasi Baru
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
            <x-table-footer :paginator="$lokasis" />
        </div>
    </div>

</div>

<script>
function mulaiEditLokasi(id) {
    const display = document.getElementById('lokasi-display-' + id);
    const form = document.getElementById('lokasi-edit-' + id);
    if (!display || !form) return;

    display.classList.add('hidden');
    form.classList.remove('hidden');
    const input = form.querySelector('.lokasi-inline-input');
    if (input) {
        input.focus();
        input.select();
    }
}

function batalEditLokasi(id) {
    const display = document.getElementById('lokasi-display-' + id);
    const form = document.getElementById('lokasi-edit-' + id);
    if (!display || !form) return;

    form.classList.add('hidden');
    display.classList.remove('hidden');
}
</script>
@endsection
