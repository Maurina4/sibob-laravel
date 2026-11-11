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
                Sistem Informasi Pengelolaan & Inventarisasi Barang Milik Negara — BBPOM di Banjarbaru.
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
                <a
                    href="{{ route('laporan-kerusakan.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-[#E3ECE6] hover:bg-[#F7FAF8] text-[#132219] text-xs font-semibold tracking-wide transition"
                >
                    <svg class="w-4 h-4 text-[#62756A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Daftar Rusak Berat</span>
                </a>
            @else
                <a
                    href="{{ route('pengajuan-pengembalian.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#163828] hover:bg-[#1E4834] text-white text-xs font-semibold tracking-wide transition shadow-2xs"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Ajukan Pengembalian</span>
                </a>
            @endif
        </div>
    </div>

    {{-- BENTO GRID LEVEL 1: METRIC STATS (3 CARDS) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-3 gap-4">

        {{-- CARD 1: TOTAL ASET --}}
        <a
            href="{{ route('bmns.index') }}"
            class="block bg-white border border-[#E3ECE6] rounded-xl p-6 transition hover:border-[#163828] hover:shadow-xs cursor-pointer group shadow-2xs"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#62756A] group-hover:text-[#163828] transition">Total Aset BMN</span>
                <span class="w-7 h-7 rounded-lg bg-[#EAF2ED] text-[#163828] flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-bold tracking-tight text-[#132219]">
                    {{ $jumlahBmn }}
                </span>
                <span class="text-xs text-[#62756A]">unit</span>
            </div>
            <div class="mt-3 pt-3 border-t border-[#E3ECE6]/60 flex items-center justify-between text-xs text-[#62756A]">
                <span>Semua Kategori</span>
                <span class="font-medium text-[#163828]">Aktif</span>
            </div>
        </a>

        {{-- CARD 2: KONDISI BAIK --}}
        <a
            href="{{ route('bmns.index', ['kondisi' => 'Baik']) }}"
            class="block bg-white border border-[#E3ECE6] rounded-xl p-6 transition hover:border-[#1E5E38] hover:shadow-xs cursor-pointer group shadow-2xs"
        >
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#62756A] group-hover:text-[#1E5E38] transition">Kondisi Baik</span>
                <span class="w-7 h-7 rounded-lg bg-[#EAF5EE] text-[#1E5E38] flex items-center justify-center group-hover:scale-110 transition">
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
        </a>

        {{-- CARD 3: DAFTAR RUSAK BERAT (OPERATOR) / PENGAJUAN PENGEMBALIAN (USER) --}}
        @if(auth()->user()->role === 'Operator')
            <a
                href="{{ route('laporan-kerusakan.index') }}"
                class="block bg-white border border-[#E3ECE6] rounded-xl p-6 transition hover:border-[#9C322C] hover:shadow-xs cursor-pointer group shadow-2xs"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-[#62756A] group-hover:text-[#9C322C] transition">Daftar Rusak Berat</span>
                    <span class="w-7 h-7 rounded-lg bg-[#FDF0EE] text-[#9C322C] flex items-center justify-center group-hover:scale-110 transition">
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
            </a>
        @else
            @php
                $userPengajuanCount = \App\Models\PengajuanPengembalian::where('diajukan_oleh', auth()->id())->count();
            @endphp
            <a
                href="{{ route('pengajuan-pengembalian.index') }}"
                class="block bg-white border border-[#E3ECE6] rounded-xl p-6 transition hover:border-[#163828] hover:shadow-xs cursor-pointer group shadow-2xs"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-[#62756A] group-hover:text-[#163828] transition">Pengajuan Saya</span>
                    <span class="w-7 h-7 rounded-lg bg-[#EAF2ED] text-[#163828] flex items-center justify-center group-hover:scale-110 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </span>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-bold tracking-tight text-[#163828]">
                        {{ $userPengajuanCount }}
                    </span>
                    <span class="text-xs text-[#62756A]">pengajuan</span>
                </div>
                <div class="mt-3 pt-3 border-t border-[#E3ECE6]/60 flex items-center justify-between text-xs text-[#62756A]">
                    <span>Riwayat Pengembalian</span>
                    <span class="font-medium text-[#163828]">Lihat Semua →</span>
                </div>
            </a>
        @endif

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
                <span class="text-xs font-mono text-[#62756A]">4 Kategori</span>
            </div>

            <div class="mt-5 space-y-4">
                {{-- BMN UMUM --}}
                <a
                    href="{{ route('bmns.index') }}"
                    class="p-4 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] hover:bg-[#EAF2ED]/60 hover:border-[#163828]/40 flex items-center justify-between transition cursor-pointer group shadow-2xs"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white border border-[#E3ECE6] flex items-center justify-center text-[#163828] group-hover:bg-[#EAF2ED] transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-[#132219] group-hover:text-[#163828] transition">BMN Umum & Perkantoran</div>
                            <div class="text-[11px] text-[#62756A]">Meja, kursi, lemari, fasilitas kerja kantor</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-bold text-[#132219]">{{ $jumlahBmnUmum ?? $jumlahBmn }}</div>
                        <div class="text-[10px] text-[#62756A]">Aset Terdata</div>
                    </div>
                </a>

                {{-- BMN LAB --}}
                <a
                    href="{{ route('bmns-lab.index') }}"
                    class="p-4 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] hover:bg-[#EAF5EE] hover:border-[#1E5E38]/40 flex items-center justify-between transition cursor-pointer group shadow-2xs"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white border border-[#E3ECE6] flex items-center justify-center text-[#1E5E38] group-hover:bg-[#EAF5EE] transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-[#132219] group-hover:text-[#1E5E38] transition">BMN Laboratorium</div>
                            <div class="text-[11px] text-[#62756A]">Instrumen pengujian & perlengkapan lab terkalibrasi</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-bold text-[#1E5E38]">{{ $jumlahBmnLab }}</div>
                        <div class="text-[10px] text-[#62756A]">Instrumen</div>
                    </div>
                </a>

                {{-- BMN TIK --}}
                <a
                    href="{{ route('bmns-tik.index') }}"
                    class="p-4 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] hover:bg-[#EBF3FE] hover:border-[#284B66]/40 flex items-center justify-between transition cursor-pointer group shadow-2xs"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white border border-[#E3ECE6] flex items-center justify-center text-[#284B66] group-hover:bg-[#EBF3FE] transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-[#132219] group-hover:text-[#284B66] transition">BMN Teknologi Informasi (TIK)</div>
                            <div class="text-[11px] text-[#62756A]">Server, komputer, monitor, perangkat jaringan</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-bold text-[#284B66]">{{ $jumlahBmnTik }}</div>
                        <div class="text-[10px] text-[#62756A]">Perangkat</div>
                    </div>
                </a>

                {{-- ALAT ANGKUTAN BERMOTOR --}}
                <a
                    href="{{ route('bmns-angkutan.index') }}"
                    class="p-4 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] hover:bg-[#FEF6E9] hover:border-[#B45309]/40 flex items-center justify-between transition cursor-pointer group shadow-2xs"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white border border-[#E3ECE6] flex items-center justify-center text-[#B45309] group-hover:bg-[#FEF6E9] transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-[#132219] group-hover:text-[#B45309] transition">Alat Angkutan Bermotor</div>
                            <div class="text-[11px] text-[#62756A]">Mobil dinas operasional, bus, dan sepeda motor</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-bold text-[#B45309]">{{ $jumlahBmnAngkutan ?? 0 }}</div>
                        <div class="text-[10px] text-[#62756A]">Kendaraan</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- PANEL KANAN: ANTREAN VERIFIKASI / STATUS PENGAJUAN --}}
        <div class="bg-white border border-[#E3ECE6] rounded-xl p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-[#E3ECE6]">
                    <h3 class="text-sm font-semibold text-[#132219]">{{ auth()->user()->role === 'Operator' ? 'Antrean Verifikasi' : 'Status Pengajuan' }}</h3>
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                </div>

                <div class="mt-4 space-y-3">
                    {{-- PENGAJUAN PENGEMBALIAN --}}
                    @php
                        $pengembalianCount = auth()->user()->role === 'Operator'
                            ? \App\Models\PengajuanPengembalian::where('status', 'diajukan')->count()
                            : \App\Models\PengajuanPengembalian::where('diajukan_oleh', auth()->id())->count();
                    @endphp
                    <a
                        href="{{ route('pengajuan-pengembalian.index') }}"
                        class="p-3.5 rounded-lg border border-[#E3ECE6] bg-white hover:bg-[#F7FAF8] hover:border-[#163828]/40 flex items-center justify-between transition cursor-pointer group shadow-2xs"
                    >
                        <div>
                            <div class="text-xs font-medium text-[#132219] group-hover:text-[#163828] transition">Pengajuan Pengembalian</div>
                            <div class="text-[11px] text-[#62756A]">{{ auth()->user()->role === 'Operator' ? 'Menunggu verifikasi' : 'Permohonan saya' }}</div>
                        </div>
                        <span class="text-sm font-bold text-[#163828] px-2 py-0.5 rounded bg-[#EAF2ED] group-hover:bg-[#163828] group-hover:text-white transition">
                            {{ $pengembalianCount }}
                        </span>
                    </a>

                    {{-- BA PERPINDAHAN / PERPINDAHAN --}}
                    <a
                        href="{{ route('ba-perpindahan.index') }}"
                        class="p-3.5 rounded-lg border border-[#E3ECE6] bg-white hover:bg-[#F7FAF8] hover:border-[#163828]/40 flex items-center justify-between transition cursor-pointer group shadow-2xs"
                    >
                        <div>
                            <div class="text-xs font-medium text-[#132219] group-hover:text-[#163828] transition">{{ auth()->user()->role === 'Operator' ? 'BA Perpindahan' : 'Perpindahan' }}</div>
                            <div class="text-[11px] text-[#62756A]">{{ auth()->user()->role === 'Operator' ? 'Menunggu persetujuan' : 'Status pengajuan' }}</div>
                        </div>
                        <span class="text-sm font-bold text-[#163828] px-2 py-0.5 rounded bg-[#EAF2ED] group-hover:bg-[#163828] group-hover:text-white transition">
                            {{ $jumlahBaPerpindahan }}
                        </span>
                    </a>

                    {{-- DISTRIBUSI (HANYA OPERATOR) --}}
                    @if(auth()->user()->role === 'Operator')
                        <a
                            href="{{ route('distribusi.index') }}"
                            class="p-3.5 rounded-lg border border-[#E3ECE6] bg-white hover:bg-[#F7FAF8] hover:border-[#163828]/40 flex items-center justify-between transition cursor-pointer group shadow-2xs"
                        >
                            <div>
                                <div class="text-xs font-medium text-[#132219] group-hover:text-[#163828] transition">Distribusi Barang</div>
                                <div class="text-[11px] text-[#62756A]">Permintaan diajukan</div>
                            </div>
                            <span class="text-sm font-bold text-[#163828] px-2 py-0.5 rounded bg-[#EAF2ED] group-hover:bg-[#163828] group-hover:text-white transition">
                                {{ $jumlahDistribusi }}
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- BENTO GRID LEVEL 3: AKTIVITAS & HISTORI TERKINI --}}
    <div class="bg-white border border-[#E3ECE6] rounded-xl p-6 shadow-2xs">
        <div class="flex items-center justify-between pb-4 border-b border-[#E3ECE6]">
            <div>
                <h3 class="text-sm font-semibold text-[#132219]">Aktivitas & Riwayat Terkini</h3>
                <p class="text-xs text-[#62756A]">Pembaruan status perpindahan, distribusi, dan pelaporan kerusakan aset</p>
            </div>
            <span class="text-xs text-[#62756A]">Log Sistem</span>
        </div>

        <div class="mt-4 divide-y divide-[#E3ECE6]">
            @forelse ($aktivitasTerkini ?? [] as $aktivitas)
                <a
                    href="{{ $aktivitas['url'] }}"
                    class="py-3 flex items-center justify-between gap-4 hover:bg-[#F7FAF8] -mx-2 px-2 rounded-lg transition group"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-8 h-8 rounded-lg {{ $aktivitas['icon_bg'] }} flex items-center justify-center shrink-0">
                            @if ($aktivitas['icon_type'] === 'calendar')
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @elseif ($aktivitas['icon_type'] === 'exchange')
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            @elseif ($aktivitas['icon_type'] === 'alert')
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            @elseif ($aktivitas['icon_type'] === 'return')
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-[#132219] group-hover:text-[#163828] truncate">{{ $aktivitas['judul'] }}</div>
                            <div class="text-[11px] text-[#62756A] truncate">{{ $aktivitas['subjudul'] }}</div>
                        </div>
                    </div>
                    <span class="{{ $aktivitas['badge_class'] }}">{{ $aktivitas['badge'] }}</span>
                </a>
            @empty
                <div class="py-8 text-center text-xs text-[#62756A]">
                    Belum ada aktivitas atau riwayat pembaruan aset terkini.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection


