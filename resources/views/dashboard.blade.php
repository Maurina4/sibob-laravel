@extends('layouts.app')

@section('title', 'Beranda — SIBOB')
@section('page-title', 'Beranda')

@section('content')
<div class="space-y-6">

    {{-- GREETING & HEADER SECTION (EDITORIAL MINIMALIST) --}}
    <div class="bg-white border border-[#E3ECE6] rounded-xl p-6 lg:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-[#163828]"></span>
                <span class="text-xs font-semibold tracking-wider uppercase text-[#62756A]">Ringkasan Eksekutif</span>
            </div>
            <h2 class="mt-2 text-2xl lg:text-3xl font-bold tracking-tight text-[#132219]">
                Selamat Datang, {{ auth()->user()->name }}
            </h2>
            <p class="mt-1 text-sm text-[#62756A]">
                Sistem Informasi Pengelolaan & Inventarisasi Barang Milik Negara — BBPOM di Banjarmasin.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            @if(auth()->user()->role === 'Operator')
                <a
                    href="{{ route('bmns.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#163828] hover:bg-[#1E4834] text-white text-xs font-semibold tracking-wide transition shadow-2xs"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Aset</span>
                </a>
            @endif
            <a
                href="{{ route('laporan-kerusakan.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-[#E3ECE6] hover:bg-[#F7FAF8] text-[#132219] text-xs font-semibold tracking-wide transition"
            >
                <svg class="w-4 h-4 text-[#62756A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Lapor Kerusakan</span>
            </a>
        </div>
    </div>

    {{-- BENTO GRID LEVEL 1: METRIC STATS (4 CARDS) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- CARD 1: TOTAL ASET --}}
        <div class="bg-white border border-[#E3ECE6] rounded-xl p-6 transition hover:border-[#163828]/40 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#62756A]">Total Aset BMN</span>
                <span class="w-7 h-7 rounded-lg bg-[#EAF2ED] text-[#163828] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-[#132219]">
                    {{ $jumlahBmn + $jumlahBmnTik + $jumlahBmnLab }}
                </span>
                <span class="text-xs text-[#62756A]">unit</span>
            </div>
            <div class="mt-3 pt-3 border-t border-[#E3ECE6]/60 flex items-center justify-between text-xs text-[#62756A]">
                <span>Semua Kategori</span>
                <span class="font-medium text-[#163828]">Aktif</span>
            </div>
        </div>

        {{-- CARD 2: KONDISI BAIK --}}
        <div class="bg-white border border-[#E3ECE6] rounded-xl p-6 transition hover:border-[#163828]/40 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#62756A]">Kondisi Baik</span>
                <span class="w-7 h-7 rounded-lg bg-[#EAF5EE] text-[#1E5E38] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-[#1E5E38]">
                    {{ $jumlahKondisiBaik }}
                </span>
                <span class="text-xs text-[#62756A]">unit</span>
            </div>
            <div class="mt-3 pt-3 border-t border-[#E3ECE6]/60 flex items-center justify-between text-xs text-[#62756A]">
                <span>Siap Digunakan</span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#EAF5EE] text-[#1E5E38]">Prima</span>
            </div>
        </div>

        {{-- CARD 3: PERLU KALIBRASI --}}
        <div class="bg-white border border-[#E3ECE6] rounded-xl p-6 transition hover:border-[#8E5F13]/40 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#62756A]">Jadwal Kalibrasi</span>
                <span class="w-7 h-7 rounded-lg bg-[#FCF5E8] text-[#8E5F13] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-[#8E5F13]">
                    {{ $jumlahPerluKalibrasi }}
                </span>
                <span class="text-xs text-[#62756A]">instrumen lab</span>
            </div>
            <div class="mt-3 pt-3 border-t border-[#E3ECE6]/60 flex items-center justify-between text-xs text-[#62756A]">
                <span>Perlu Tindakan Segera</span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#FCF5E8] text-[#8E5F13]">Kalibrasi</span>
            </div>
        </div>

        {{-- CARD 4: LAPORAN KERUSAKAN --}}
        <div class="bg-white border border-[#E3ECE6] rounded-xl p-6 transition hover:border-[#9C322C]/40 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#62756A]">Laporan Kerusakan</span>
                <span class="w-7 h-7 rounded-lg bg-[#FDF0EE] text-[#9C322C] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-[#9C322C]">
                    {{ $jumlahKerusakan }}
                </span>
                <span class="text-xs text-[#62756A]">kasus</span>
            </div>
            <div class="mt-3 pt-3 border-t border-[#E3ECE6]/60 flex items-center justify-between text-xs text-[#62756A]">
                <span>Belum Selesai</span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#FDF0EE] text-[#9C322C]">Pending</span>
            </div>
        </div>

    </div>

    {{-- BENTO GRID LEVEL 2: BREAKDOWN & TRANSAKSI --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- PANEL KIRI: DISTRIBUSI KATEGORI ASET (2 COLS) --}}
        <div class="lg:col-span-2 bg-white border border-[#E3ECE6] rounded-xl p-6 shadow-2xs">
            <div class="flex items-center justify-between pb-4 border-b border-[#E3ECE6]">
                <div>
                    <h3 class="text-sm font-semibold text-[#132219]">Klasifikasi Inventaris</h3>
                    <p class="text-xs text-[#62756A]">Distribusi aset menurut rumpun unit kerja BPOM</p>
                </div>
                <span class="text-xs font-mono text-[#62756A]">3 Kategori</span>
            </div>

            <div class="mt-5 space-y-4">
                {{-- BMN UMUM --}}
                <div class="p-4 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white border border-[#E3ECE6] flex items-center justify-center text-[#163828]">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-[#132219]">BMN Umum & Perkantoran</div>
                            <div class="text-[11px] text-[#62756A]">Meja, kursi, lemari, fasilitas kerja kantor</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-bold text-[#132219]">{{ $jumlahBmn }}</div>
                        <div class="text-[10px] text-[#62756A]">Aset Terdata</div>
                    </div>
                </div>

                {{-- BMN LAB --}}
                <div class="p-4 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white border border-[#E3ECE6] flex items-center justify-center text-[#1E5E38]">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-[#132219]">BMN Laboratorium</div>
                            <div class="text-[11px] text-[#62756A]">Instrumen pengujian & perlengkapan lab terkalibrasi</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-bold text-[#1E5E38]">{{ $jumlahBmnLab }}</div>
                        <div class="text-[10px] text-[#62756A]">Instrumen</div>
                    </div>
                </div>

                {{-- BMN TIK --}}
                <div class="p-4 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white border border-[#E3ECE6] flex items-center justify-center text-[#284B66]">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-[#132219]">BMN Teknologi Informasi (TIK)</div>
                            <div class="text-[11px] text-[#62756A]">Server, komputer, monitor, perangkat jaringan</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-bold text-[#284B66]">{{ $jumlahBmnTik }}</div>
                        <div class="text-[10px] text-[#62756A]">Perangkat</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL KANAN: ANTRIAN PERSETUJUAN / TRANSAKSI --}}
        <div class="bg-white border border-[#E3ECE6] rounded-xl p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-[#E3ECE6]">
                    <h3 class="text-sm font-semibold text-[#132219]">Antrean Verifikasi</h3>
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                </div>

                <div class="mt-4 space-y-3">
                    {{-- BA PERPINDAHAN --}}
                    <div class="p-3.5 rounded-lg border border-[#E3ECE6] bg-white flex items-center justify-between">
                        <div>
                            <div class="text-xs font-medium text-[#132219]">BA Perpindahan</div>
                            <div class="text-[11px] text-[#62756A]">Menunggu persetujuan</div>
                        </div>
                        <span class="text-sm font-bold text-[#163828] px-2 py-0.5 rounded bg-[#EAF2ED]">
                            {{ $jumlahBaPerpindahan }}
                        </span>
                    </div>

                    {{-- DISTRIBUSI --}}
                    <div class="p-3.5 rounded-lg border border-[#E3ECE6] bg-white flex items-center justify-between">
                        <div>
                            <div class="text-xs font-medium text-[#132219]">Distribusi Barang</div>
                            <div class="text-[11px] text-[#62756A]">Permintaan diajukan</div>
                        </div>
                        <span class="text-sm font-bold text-[#163828] px-2 py-0.5 rounded bg-[#EAF2ED]">
                            {{ $jumlahDistribusi }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-[#E3ECE6]">
                <a
                    href="{{ route('ba-perpindahan.index') }}"
                    class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-[#F7FAF8] hover:bg-[#EAF2ED] text-[#163828] border border-[#E3ECE6] text-xs font-semibold transition"
                >
                    <span>Buka Daftar Verifikasi</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

    </div>

    {{-- BENTO GRID LEVEL 3: AKTIVITAS & HISTORI TERKINI --}}
    <div class="bg-white border border-[#E3ECE6] rounded-xl p-6 shadow-2xs">
        <div class="flex items-center justify-between pb-4 border-b border-[#E3ECE6]">
            <div>
                <h3 class="text-sm font-semibold text-[#132219]">Aktivitas & Riwayat Terkini</h3>
                <p class="text-xs text-[#62756A]">Pembaruan status pemeliharaan, kalibrasi, dan perpindahan aset</p>
            </div>
            <span class="text-xs text-[#62756A]">Log Sistem</span>
        </div>

        <div class="mt-4 divide-y divide-[#E3ECE6]">
            {{-- LOG ITEM 1 --}}
            <div class="py-3 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-[#FCF5E8] text-[#8E5F13] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <div>
                        <div class="text-xs font-medium text-[#132219]">Spectrophotometer UV-Vis dijadwalkan kalibrasi berkala</div>
                        <div class="text-[11px] text-[#62756A]">Laboratorium Pengujian Kimia • Banjarbaru</div>
                    </div>
                </div>
                <span class="text-[11px] font-mono text-[#8E5F13] px-2 py-0.5 rounded bg-[#FCF5E8] shrink-0">2 hari lagi</span>
            </div>

            {{-- LOG ITEM 2 --}}
            <div class="py-3 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-[#EAF2ED] text-[#1E5E38] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </span>
                    <div>
                        <div class="text-xs font-medium text-[#132219]">Distribusi UPS APC ES-525 ke Ruang Duduk diajukan</div>
                        <div class="text-[11px] text-[#62756A]">Menunggu persetujuan kepala bagian</div>
                    </div>
                </div>
                <span class="text-[11px] font-mono text-[#62756A] shrink-0">2 hari lalu</span>
            </div>

            {{-- LOG ITEM 3 --}}
            <div class="py-3 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-[#F7FAF8] text-[#163828] border border-[#E3ECE6] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <div>
                        <div class="text-xs font-medium text-[#132219]">Pengajuan Berita Acara Perpindahan PC Acer</div>
                        <div class="text-[11px] text-[#62756A]">Diajukan oleh Petugas Pengaju</div>
                    </div>
                </div>
                <span class="text-[11px] font-mono text-[#62756A] shrink-0">3 hari lalu</span>
            </div>
        </div>
    </div>

</div>
@endsection