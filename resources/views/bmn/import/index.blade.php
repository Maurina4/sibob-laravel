@extends('layouts.app')

@section('title', 'Import Data BMN — SIBOB')
@section('page-title', 'Import Data BMN')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('bmns.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar BMN</span>
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Import Barang Masuk via Excel</h1>
        <p class="text-xs text-[#62756A] mt-0.5">Daftarkan inventaris aset barang baru secara massal ke dalam sistem SIBOB dengan berkas spreadsheet Microsoft Excel (.xlsx).</p>
    </div>

    {{-- ALERT ERROR --}}
    @if (session('error'))
        <div class="p-4 rounded-xl border border-[#F8D4D0] bg-[#FDF0EE] text-[#9C322C] text-xs space-y-2">
            <div class="flex items-center gap-2 font-semibold">
                <svg class="w-4 h-4 shrink-0 text-[#9C322C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            @if (session('header_errors'))
                <ul class="list-disc list-inside space-y-0.5 pl-6 text-[#9C322C]">
                    @foreach (session('header_errors') as $herr)
                        <li>{{ $herr }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    {{-- GRID PANDUAN & FORMULIR UPLOAD --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KOLOM UTAMA: AREA UPLOAD (2/3) --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-[#E3ECE6]">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Unggah Berkas Excel (.xlsx)</h2>
                    </div>
                    <span class="text-[11px] font-medium text-[#163828] bg-[#EAF2ED] px-2.5 py-0.5 rounded-full border border-[#D5E5DA]">
                        Maks. 5 MB / 5.000 baris
                    </span>
                </div>

                <form
                    method="POST"
                    action="{{ route('bmns.import.preview') }}"
                    enctype="multipart/form-data"
                    class="space-y-4"
                    x-data="{
                        fileName: '',
                        fileSize: '',
                        isDragging: false,
                        isSubmitting: false,
                        handleDrop(e) {
                            this.isDragging = false;
                            const files = e.dataTransfer.files;
                            if (files.length > 0) {
                                this.$refs.fileInput.files = files;
                                this.updateFileInfo(files[0]);
                            }
                        },
                        updateFileInfo(file) {
                            if (file) {
                                this.fileName = file.name;
                                const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
                                this.fileSize = sizeInMb > 0 ? sizeInMb + ' MB' : (file.size / 1024).toFixed(0) + ' KB';
                            } else {
                                this.fileName = '';
                                this.fileSize = '';
                            }
                        }
                    }"
                    @submit="isSubmitting = true"
                >
                    @csrf

                    {{-- DRAG AND DROP ZONE --}}
                    <label
                        for="excel_file"
                        class="flex flex-col items-center justify-center p-8 border-2 border-dashed rounded-2xl transition cursor-pointer text-center group"
                        :class="isDragging ? 'border-[#163828] bg-[#EAF2ED]/50' : 'border-[#D3DDDA] hover:border-[#163828] bg-[#FBFDFB] hover:bg-[#F7FAF8]'"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop($event)"
                    >
                        <div class="w-12 h-12 rounded-xl bg-[#EAF2ED] group-hover:bg-[#D5E5DA] text-[#163828] flex items-center justify-center mb-3 transition shadow-2xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>

                        <div class="text-xs font-semibold text-[#132219]">
                            <span class="text-[#107C41] hover:underline">Pilih berkas Excel</span> atau tarik dan lepas di sini
                        </div>
                        <p class="text-[11px] text-[#62756A] mt-1">
                            Format berkas harus <strong>.xlsx</strong> sesuai template resmi SIBOB.
                        </p>

                        {{-- FILE INFO PREVIEW --}}
                        <template x-if="fileName">
                            <div class="mt-4 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-[#EAF2ED] border border-[#CDE1D4] text-xs font-semibold text-[#163828]">
                                <svg class="w-4 h-4 text-[#107C41] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span x-text="fileName"></span>
                                <span class="text-[10px] text-[#4A5D52] font-normal" x-text="'(' + fileSize + ')'"></span>
                            </div>
                        </template>

                        <input
                            type="file"
                            id="excel_file"
                            name="file"
                            x-ref="fileInput"
                            accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            class="hidden"
                            @change="updateFileInfo($event.target.files[0])"
                            required
                        >
                    </label>

                    {{-- TOMBOL SUBMIT PREVIEW --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button
                            type="submit"
                            :disabled="!fileName || isSubmitting"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] disabled:bg-[#94A39B] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer disabled:cursor-not-allowed"
                        >
                            <template x-if="isSubmitting">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <template x-if="!isSubmitting">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </template>
                            <span x-text="isSubmitting ? 'Memproses Berkas...' : 'Unggah & Tinjau Preview'"></span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- HISTORI RIWAYAT IMPORT --}}
            <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#E3ECE6]">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Riwayat Import Terakhir</h2>
                    </div>
                    <span class="text-[11px] text-[#62756A]">Menampilkan 10 riwayat terbaru</span>
                </div>

                @if($recentLogs->isEmpty())
                    <div class="py-8 text-center text-xs text-[#8D9D95]">
                        Belum ada riwayat aktivitas impor data BMN.
                    </div>
                @else
                    <div class="overflow-x-auto rounded-xl border border-[#E3ECE6]">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-[#F7FAF8] text-[#4A5D52] font-semibold border-b border-[#E3ECE6]">
                                <tr>
                                    <th class="py-2.5 px-3">Waktu</th>
                                    <th class="py-2.5 px-3">Nama Berkas</th>
                                    <th class="py-2.5 px-3">Operator</th>
                                    <th class="py-2.5 px-3 text-center">Total Baris</th>
                                    <th class="py-2.5 px-3 text-center">Status</th>
                                    <th class="py-2.5 px-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E3ECE6] text-[#132219]">
                                @foreach($recentLogs as $log)
                                    <tr class="hover:bg-[#FBFDFB]">
                                        <td class="py-2.5 px-3 font-mono text-[11px] text-[#62756A] whitespace-nowrap">
                                            {{ $log->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-2.5 px-3 font-medium max-w-[180px] truncate" title="{{ $log->nama_file_asli }}">
                                            {{ $log->nama_file_asli }}
                                        </td>
                                        <td class="py-2.5 px-3 text-[#4A5D52]">
                                            {{ $log->user?->name ?? 'Sistem' }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center font-mono font-semibold">
                                            {{ number_format($log->total_baris) }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                            @if($log->status === 'berhasil')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EAF2ED] text-[#137333] border border-[#CDE1D4]">
                                                    Berhasil
                                                </span>
                                            @elseif($log->status === 'preview')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FEF7E0] text-[#B06000] border border-[#FEEFC3]">
                                                    Preview
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FDF0EE] text-[#9C322C] border border-[#F8D4D0]">
                                                    Gagal
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                            @if($log->status === 'gagal' || ($log->error_summary['has_errors'] ?? false))
                                                <a
                                                    href="{{ route('bmns.import.error-report', $log) }}"
                                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#9C322C] hover:underline"
                                                    title="Unduh file Excel rincian baris error"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 10v6m0 0l-3-3m3 3l3-3m2-8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span>Laporan Error</span>
                                                </a>
                                            @else
                                                <span class="text-[#8D9D95] text-[11px]">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

        {{-- KOLOM SAMPING: UNDUH TEMPLATE & ATURAN (1/3) --}}
        <div class="space-y-6">

            {{-- CARD UNDUH TEMPLATE RESMI --}}
            <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                    <span class="w-2 h-2 rounded-full bg-[#107C41]"></span>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Template Excel Resmi</h2>
                </div>

                <p class="text-xs text-[#62756A] leading-relaxed">
                    Gunakan berkas template resmi yang telah dikonfigurasi dengan tipe sel yang benar (menjaga angka 0 di depan kode barang) serta dropdown pilihan otomatis.
                </p>

                <div class="space-y-2 text-[11px] text-[#4A5D52] bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
                    <div class="flex items-start gap-1.5 font-medium">
                        <span class="font-bold text-[#163828]">✓</span>
                        <span><strong>Sheet "Data"</strong>: Tempat pengisian aset barang inventaris.</span>
                    </div>
                    <div class="flex items-start gap-1.5 font-medium">
                        <span class="font-bold text-[#163828]">✓</span>
                        <span><strong>Sheet "Petunjuk"</strong>: Rincian format kolom, contoh, dan batasan validasi.</span>
                    </div>
                </div>

                <a
                    href="{{ route('bmns.import.template') }}"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#107C41] hover:bg-[#0E6B37] active:bg-[#0A4D27] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Unduh Template (.xlsx)</span>
                </a>
            </div>

            {{-- CARD KETENTUAN NILAI DEFAULT --}}
            <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                    <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Ketentuan & Kebijakan</h2>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="p-3 rounded-xl bg-[#EAF2ED] border border-[#CDE1D4] text-[#163828] space-y-1">
                        <div class="font-bold text-[11px] uppercase tracking-wider">Nilai Otomatis Sistem</div>
                        <ul class="text-[11px] space-y-1 text-[#2C4A38] list-disc list-inside">
                            <li>Lokasi: <strong>Gudang BMN</strong></li>
                            <li>Penanggung Jawab: <strong>Pengelola BMN</strong></li>
                            <li>Kondisi: <strong>Baik</strong></li>
                            <li>Otomatisasi draf <strong>Berita Acara Distribusi Gudang</strong></li>
                        </ul>
                    </div>

                    <div class="p-3 rounded-xl bg-[#FFF8E7] border border-[#FFE8A3] text-[#7A4B00] space-y-1">
                        <div class="font-bold text-[11px] uppercase tracking-wider">Kebijakan All-or-Nothing</div>
                        <p class="text-[11px] leading-relaxed">
                            Jika ditemukan 1 baris yang salah atau duplikat, seluruh file tidak akan disimpan. Anda dapat mengunduh laporan error, memperbaikinya, dan mengunggah ulang.
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
