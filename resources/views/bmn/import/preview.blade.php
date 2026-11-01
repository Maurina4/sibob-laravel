@extends('layouts.app')

@section('title', 'Preview Import BMN — SIBOB')
@section('page-title', 'Preview Import BMN')

@section('content')
<div class="space-y-6" x-data="{
    filterTab: 'all', // 'all', 'valid', 'error'
    searchQuery: '',
    rows: @js($rows),
    get filteredRows() {
        return this.rows.filter(r => {
            if (this.filterTab === 'valid' && !r.is_valid) return false;
            if (this.filterTab === 'error' && r.is_valid) return false;
            if (!this.searchQuery.trim()) return true;
            const q = this.searchQuery.toLowerCase();
            return (r.kode_barang && r.kode_barang.toLowerCase().includes(q)) ||
                   (r.nama_barang && r.nama_barang.toLowerCase().includes(q)) ||
                   (r.merek && r.merek.toLowerCase().includes(q)) ||
                   (r.nup_raw && String(r.nup_raw).includes(q));
        });
    }
}">

    {{-- KEMBALI & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a
                href="{{ route('bmns.import.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-1"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Batal & Upload Ulang</span>
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Tinjau Preview Validasi Import</h1>
            <p class="text-xs text-[#62756A] mt-0.5">
                Berkas: <strong class="text-[#132219]">{{ $summary['nama_file'] }}</strong> — Periksa hasil validasi sebelum data dimasukkan ke sistem.
            </p>
        </div>

        {{-- TOMBOL AKSI UTAMA --}}
        <div class="flex items-center gap-2.5 flex-wrap">
            @if(! $summary['is_valid'])
                <a
                    href="{{ route('bmns.import.error-report', $logId) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#9C322C] hover:bg-[#80241F] active:bg-[#661C18] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition shrink-0"
                    title="Unduh laporan baris error dalam format Excel"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Unduh Laporan Error (.xlsx)</span>
                </a>

                <button
                    type="button"
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-300 text-gray-500 rounded-xl text-xs font-semibold tracking-wide cursor-not-allowed shrink-0"
                    title="Konfirmasi dinonaktifkan karena terdapat baris bermasalah (Kebijakan All-or-Nothing)"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m0 0v2m0-2h2m-2 0H10m12-5a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Konfirmasi Import (Terkunci)</span>
                </button>
            @else
                <form method="POST" action="{{ route('bmns.import.confirm') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
                        onclick="return confirm('Apakah Anda yakin ingin menyimpan seluruh {{ $summary['total_rows'] }} barang ini ke Gudang BMN?')"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Konfirmasi & Simpan ({{ number_format($summary['total_rows']) }} Barang)</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- KARTU RINGKASAN METRIK --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- TOTAL BARIS --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-4 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-[#F7FAF8] text-[#163828] flex items-center justify-center shrink-0 border border-[#E3ECE6]">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">Total Baris Data</div>
                <div class="text-xl font-bold text-[#132219] font-mono">{{ number_format($summary['total_rows']) }}</div>
            </div>
        </div>

        {{-- BARIS VALID --}}
        <div class="bg-white rounded-2xl border border-[#CDE1D4] p-4 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-[#EAF2ED] text-[#137333] flex items-center justify-center shrink-0 border border-[#CDE1D4]">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#137333]">Baris Valid (Lolos)</div>
                <div class="text-xl font-bold text-[#137333] font-mono">{{ number_format($summary['valid_count']) }}</div>
            </div>
        </div>

        {{-- BARIS BERMASALAH --}}
        <div class="bg-white rounded-2xl border {{ $summary['error_count'] > 0 ? 'border-[#F8D4D0] bg-[#FFFBFB]' : 'border-[#E3ECE6]' }} p-4 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl {{ $summary['error_count'] > 0 ? 'bg-[#FDF0EE] text-[#9C322C] border border-[#F8D4D0]' : 'bg-[#F7FAF8] text-[#8D9D95] border border-[#E3ECE6]' }} flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider {{ $summary['error_count'] > 0 ? 'text-[#9C322C]' : 'text-[#62756A]' }}">
                    Baris Error / Duplikat
                </div>
                <div class="text-xl font-bold {{ $summary['error_count'] > 0 ? 'text-[#9C322C]' : 'text-[#132219]' }} font-mono">
                    {{ number_format($summary['error_count']) }}
                </div>
            </div>
        </div>

        {{-- STATUS KEBIJAKAN --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-4 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl {{ $summary['is_valid'] ? 'bg-[#EAF2ED] text-[#137333]' : 'bg-[#FFF8E7] text-[#B06000]' }} flex items-center justify-center shrink-0 border border-[#E3ECE6]">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">Status Validasi</div>
                <div class="text-xs font-bold {{ $summary['is_valid'] ? 'text-[#137333]' : 'text-[#B06000]' }}">
                    {{ $summary['is_valid'] ? 'Siap Disimpan' : 'All-or-Nothing Aktif' }}
                </div>
            </div>
        </div>
    </div>

    {{-- ALERT PESAN STATUS --}}
    @if(! $summary['is_valid'])
        <div class="p-4 rounded-xl border border-[#F8D4D0] bg-[#FDF0EE] text-[#9C322C] text-xs flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 text-[#9C322C] mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="space-y-1">
                <div class="font-bold">Ditemukan {{ $summary['error_count'] }} baris yang bermasalah pada berkas ini.</div>
                <p class="leading-relaxed">
                    Sesuai kebijakan <strong>All-or-Nothing</strong>, tidak ada data yang disimpan ke database selama masih ada baris yang tidak valid atau duplikat.
                    Silakan klik tombol <strong>"Unduh Laporan Error (.xlsx)"</strong> di atas untuk melihat rincian baris dan alasan kesalahan, perbaiki di file Excel Anda, lalu unggah ulang.
                </p>
            </div>
        </div>
    @else
        <div class="p-4 rounded-xl border border-[#CDE1D4] bg-[#EAF2ED] text-[#163828] text-xs flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0 text-[#137333]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                Seluruh <strong>{{ $summary['total_rows'] }}</strong> baris data berhasil lolos validasi. Silakan klik tombol <strong>"Konfirmasi & Simpan"</strong> untuk mendaftarkan aset baru ini ke Gudang BMN.
            </div>
        </div>
    @endif

    {{-- TABEL DATA PREVIEW --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
        {{-- FILTER TAB & SEARCH --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#E3ECE6]">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="filterTab = 'all'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer"
                    :class="filterTab === 'all' ? 'bg-[#163828] text-white shadow-2xs' : 'bg-[#F7FAF8] text-[#62756A] hover:bg-[#EAF2ED] hover:text-[#163828]'"
                >
                    Semua (<span x-text="rows.length"></span>)
                </button>
                <button
                    type="button"
                    @click="filterTab = 'valid'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer"
                    :class="filterTab === 'valid' ? 'bg-[#137333] text-white shadow-2xs' : 'bg-[#F7FAF8] text-[#137333] hover:bg-[#EAF2ED]'"
                >
                    Valid (<span x-text="rows.filter(r => r.is_valid).length"></span>)
                </button>
                <button
                    type="button"
                    @click="filterTab = 'error'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer"
                    :class="filterTab === 'error' ? 'bg-[#9C322C] text-white shadow-2xs' : 'bg-[#FDF0EE] text-[#9C322C] hover:bg-[#F8D4D0]'"
                >
                    Error (<span x-text="rows.filter(r => !r.is_valid).length"></span>)
                </button>
            </div>

            <div class="relative w-full sm:w-64">
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="Cari kode, nama, atau NUP..."
                    class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828]"
                >
                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#8D9D95]">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
            </div>
        </div>

        {{-- TABEL HORIZONTAL DENGAN SCROLL --}}
        <div class="overflow-x-auto rounded-xl border border-[#E3ECE6]">
            <table class="w-full text-xs text-left">
                <thead class="bg-[#F7FAF8] text-[#4A5D52] font-semibold border-b border-[#E3ECE6] whitespace-nowrap">
                    <tr>
                        <th class="py-2.5 px-3 text-center">Baris</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3">Kode Barang</th>
                        <th class="py-2.5 px-3 text-center">NUP</th>
                        <th class="py-2.5 px-3">Nama Barang</th>
                        <th class="py-2.5 px-3">Merek / Type</th>
                        <th class="py-2.5 px-3 text-center">Tgl Terima</th>
                        <th class="py-2.5 px-3 text-center">Tahun</th>
                        <th class="py-2.5 px-3 text-right">Nilai (Rp)</th>
                        <th class="py-2.5 px-3 text-center">Jenis</th>
                        <th class="py-2.5 px-3 text-center">Satuan</th>
                        <th class="py-2.5 px-3">Catatan / Alasan Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6] text-[#132219]">
                    <template x-for="row in filteredRows" :key="row.excel_row">
                        <tr :class="row.is_valid ? 'hover:bg-[#FBFDFB]' : 'bg-[#FDF0EE]/40 hover:bg-[#FDF0EE]/70'">
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-[#62756A]" x-text="row.excel_row"></td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <template x-if="row.is_valid">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EAF2ED] text-[#137333] border border-[#CDE1D4]">
                                        Valid
                                    </span>
                                </template>
                                <template x-if="!row.is_valid">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FDF0EE] text-[#9C322C] border border-[#F8D4D0]">
                                        Error
                                    </span>
                                </template>
                            </td>
                            <td class="py-2.5 px-3 font-mono font-semibold text-[#132219] whitespace-nowrap" x-text="row.kode_barang || '-'"></td>
                            <td class="py-2.5 px-3 text-center font-mono" x-text="row.nup_raw || '-'"></td>
                            <td class="py-2.5 px-3 font-medium min-w-[160px]" x-text="row.nama_barang || '-'"></td>
                            <td class="py-2.5 px-3 text-[#4A5D52]" x-text="row.merek || '-'"></td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap font-mono" x-text="row.tanggal_terima_raw || '-'"></td>
                            <td class="py-2.5 px-3 text-center font-mono" x-text="row.tahun_perolehan_raw || '-'"></td>
                            <td class="py-2.5 px-3 text-right font-mono whitespace-nowrap" x-text="row.nilai_perolehan ? Number(row.nilai_perolehan).toLocaleString('id-ID') : (row.nilai_perolehan_raw || '-')"></td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap font-semibold" x-text="row.jenis || '-'"></td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap" x-text="row.satuan || '-'"></td>
                            <td class="py-2.5 px-3 min-w-[220px]">
                                <template x-if="row.is_valid">
                                    <span class="text-[11px] text-[#62756A] italic">Siap diimpor</span>
                                </template>
                                <template x-if="!row.is_valid">
                                    <div class="space-y-0.5">
                                        <template x-for="(err, key) in row.errors" :key="key">
                                            <div class="text-[11px] font-medium text-[#9C322C] flex items-start gap-1">
                                                <span>•</span>
                                                <span x-text="err"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredRows.length === 0">
                        <td colspan="12" class="py-8 text-center text-xs text-[#8D9D95]">
                            Tidak ada data baris yang cocok dengan filter atau pencarian.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
