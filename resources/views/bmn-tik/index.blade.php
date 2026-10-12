@extends('layouts.app')

@section('title', 'Daftar BMN TIK — SIBOB')
@section('page-title', 'Daftar BMN TIK')

@section('content')
<div class="space-y-6">

    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">Sub-Kategori Inventaris</div>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Daftar BMN TIK</h1>
            <p class="text-xs text-[#62756A] mt-0.5">Pengelolaan perangkat teknologi informasi dan komunikasi BBPOM di Banjarmasin.</p>
        </div>

        <a
            href="{{ route('bmns-tik.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition shrink-0"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah BMN TIK</span>
        </a>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white p-3.5 rounded-xl border border-[#E3ECE6] shadow-2xs">
        <form method="GET" action="{{ route('bmns-tik.index') }}" class="flex flex-wrap items-center gap-2.5">
            {{-- SEARCH --}}
            <div class="relative flex-1 min-w-[220px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#8D9D95]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari kode atau nama perangkat TIK..."
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
                    <option value="">Semua Jenis Perangkat</option>
                    @foreach($jenisFilters ?? [] as $jenis)
                        <option value="{{ $jenis }}" @selected(request('jenis') === $jenis)>
                            {{ $jenis }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- FILTER LOKASI --}}
            <div>
                <select
                    name="lokasi"
                    onchange="this.form.submit()"
                    class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 px-3 transition cursor-pointer"
                >
                    <option value="">Semua Lokasi</option>
                    @foreach($lokasiFilters ?? [] as $lokasi)
                        <option value="{{ $lokasi }}" @selected(request('lokasi') === $lokasi)>
                            {{ $lokasi }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- FILTER PENANGGUNG JAWAB --}}
            <div>
                <select
                    name="penanggungjawab"
                    onchange="this.form.submit()"
                    class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 px-3 transition cursor-pointer"
                >
                    <option value="">Semua PJ</option>
                    @foreach($penanggungjawabFilters ?? [] as $penanggungjawab)
                        <option value="{{ $penanggungjawab }}" @selected(request('penanggungjawab') === $penanggungjawab)>
                            {{ $penanggungjawab }}
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
            @if(request()->filled('q') || request()->filled('jenis') || request()->filled('lokasi') || request()->filled('penanggungjawab'))
                <a
                    href="{{ route('bmns-tik.index') }}"
                    class="inline-flex items-center px-3 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-lg text-xs font-medium transition"
                >
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- TABEL DATA TIK --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Kode & NUP</th>
                        <th class="py-3.5 px-4">Nama Perangkat TIK</th>
                        <th class="py-3.5 px-4">Kategori TIK</th>
                        <th class="py-3.5 px-4">Lokasi Ruangan</th>
                        <th class="py-3.5 px-4">Penanggung Jawab</th>
                        <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6]/70 text-xs">
                    @forelse($bmnsTik as $index => $item)
                        <tr class="hover:bg-[#F9FCFA] transition">
                            {{-- NO --}}
                            <td class="py-3.5 px-4 text-center text-[#62756A] font-mono text-[11px]">
                                {{ $bmnsTik->firstItem() + $index }}
                            </td>

                            {{-- KODE BARANG --}}
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                    {{ $item->kode_nup }}
                                </span>
                            </td>

                            {{-- NAMA BARANG --}}
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-[#132219] flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#163828] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ $item->nama_barang }}</span>
                                </div>
                            </td>

                            {{-- JENIS --}}
                            <td class="py-3.5 px-4">
                                @if($item->jenis)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-[#EAF2ED] text-[#163828] border border-[#D5E5DA]">
                                        {{ $item->jenis }}
                                    </span>
                                @else
                                    <span class="text-[#8D9D95] font-mono">—</span>
                                @endif
                            </td>

                            {{-- LOKASI (WITH INLINE EDIT) --}}
                            <td class="py-3.5 px-4">
                                <div id="location-display-{{ $item->id }}" class="flex items-center gap-2 group">
                                    <span class="text-[#3C4F44]">{{ $item->lokasi ?: '—' }}</span>
                                    <button
                                        type="button"
                                        onclick="editBmnTikLocation({{ $item->id }})"
                                        class="opacity-0 group-hover:opacity-100 p-1 text-[#62756A] hover:text-[#163828] hover:bg-[#EAF2ED] rounded transition"
                                        title="Ubah lokasi cepat"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </div>

                                {{-- FORM EDIT LOKASI INLINE --}}
                                <form
                                    method="POST"
                                    action="{{ route('bmns-tik.update', ['bmnTik' => $item->id]) }}"
                                    id="location-form-{{ $item->id }}"
                                    class="hidden"
                                >
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="kode_barang" value="{{ $item->kode_barang }}">
                                    <input type="hidden" name="nup" value="{{ $item->nup }}">
                                    <input type="hidden" name="nama_barang" value="{{ $item->nama_barang }}">
                                    <input type="hidden" name="jenis" value="{{ $item->jenis }}">
                                    <input type="hidden" name="penanggungjawab" value="{{ $item->penanggungjawab }}">

                                    <div class="flex items-center gap-1.5">
                                        <select
                                            name="lokasi"
                                            class="location-edit-select border border-[#E3ECE6] rounded-md text-[11px] py-1 px-2 bg-white text-[#132219] focus:border-[#163828]"
                                            required
                                        >
                                            <option value="">Pilih Lokasi</option>
                                            @foreach($lokasis ?? [] as $lokasi)
                                                <option value="{{ $lokasi->nama }}" @selected($item->lokasi === $lokasi->nama)>
                                                    {{ $lokasi->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button
                                            type="submit"
                                            class="px-2 py-1 bg-[#163828] text-white rounded text-[11px] font-semibold hover:bg-[#1E4834]"
                                        >
                                            OK
                                        </button>
                                        <button
                                            type="button"
                                            onclick="cancelBmnTikLocation({{ $item->id }})"
                                            class="px-2 py-1 bg-white border border-[#E3ECE6] text-[#62756A] rounded text-[11px] hover:bg-[#F2F6F3]"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </form>
                            </td>

                            {{-- PENANGGUNG JAWAB --}}
                            <td class="py-3.5 px-4 font-medium text-[#132219]">
                                {{ $item->penanggungjawab ?: '—' }}
                            </td>

                            {{-- AKSI --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('bmns-tik.edit', ['bmnTik' => $item->id]) }}"
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
                                        action="{{ route('bmns-tik.destroy', ['bmnTik' => $item->id]) }}"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data BMN TIK ini?')"
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
                            <td colspan="7" class="py-12 text-center">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-[#EAF2ED] text-[#163828] flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-[#132219]">Belum Ada Data BMN TIK</div>
                                    <p class="text-xs text-[#62756A]">Belum ada perangkat teknologi informasi yang terdaftar.</p>
                                    <a
                                        href="{{ route('bmns-tik.create') }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] text-white rounded-lg text-xs font-semibold hover:bg-[#1E4834] transition shadow-2xs"
                                    >
                                        + Tambah BMN TIK Sekarang
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
            <x-table-footer :paginator="$bmnsTik" />
        </div>
    </div>

</div>

<script>
function editBmnTikLocation(id) {
    const display = document.getElementById('location-display-' + id);
    const form = document.getElementById('location-form-' + id);
    if (!display || !form) return;

    display.classList.add('hidden');
    form.classList.remove('hidden');
    const select = form.querySelector('.location-edit-select');
    if (select) select.focus();
}

function cancelBmnTikLocation(id) {
    const display = document.getElementById('location-display-' + id);
    const form = document.getElementById('location-form-' + id);
    if (!display || !form) return;

    form.classList.add('hidden');
    display.classList.remove('hidden');
}
</script>
@endsection
