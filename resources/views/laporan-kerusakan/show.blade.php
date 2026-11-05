@extends('layouts.app')

@section('title', 'Detail Rusak Berat — ' . $laporanKerusakan->nomor)
@section('page-title', 'Detail Rusak Berat')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a
                href="{{ route('laporan-kerusakan.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar Rusak Berat</span>
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Detail Rusak Berat BMN</h1>
                <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                    {{ $laporanKerusakan->nomor }}
                </span>
            </div>
            <p class="text-xs text-[#62756A] mt-0.5">Rincian aduan teknis dan status penyelesaian perbaikan.</p>
        </div>

        @if(auth()->user()->role === 'Operator')
            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                @if($laporanKerusakan->status === 'Dilaporkan')
                    <form
                        method="POST"
                        action="{{ route('laporan-kerusakan.update', $laporanKerusakan) }}"
                        onsubmit="return confirm('Terima laporan kerusakan ini? Kondisi BMN terkait akan otomatis diubah menjadi {{ in_array($laporanKerusakan->tingkat, ['Rusak Berat', 'Berat']) ? 'Rusak Berat' : 'Rusak Ringan' }}.')"
                        class="inline"
                    >
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="Diterima" />
                        <input type="hidden" name="redirect_to" value="show" />
                        <input type="hidden" name="tanggapan" value="Laporan telah diverifikasi oleh Operator dan diterima untuk antrean perbaikan teknis." />
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#1A73E8] hover:bg-[#1557B0] text-white rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer"
                            title="Terima Laporan & Otomatis Ubah Kondisi BMN"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Terima Laporan</span>
                        </button>
                    </form>
                @elseif(in_array($laporanKerusakan->status, ['Diterima', 'Diproses']))
                    <form
                        method="POST"
                        action="{{ route('laporan-kerusakan.update', $laporanKerusakan) }}"
                        onsubmit="return confirm('Selesaikan perbaikan untuk laporan ini? Kondisi BMN akan otomatis dikembalikan menjadi Baik.')"
                        class="inline"
                    >
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="Selesai" />
                        <input type="hidden" name="redirect_to" value="show" />
                        <input type="hidden" name="tanggapan" value="Perbaikan telah selesai dilaksanakan dan barang telah dipastikan berfungsi normal kembali." />
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#137333] hover:bg-[#0D5224] text-white rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer"
                            title="Selesaikan Perbaikan & Pulihkan Kondisi BMN ke Baik"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Selesaikan Perbaikan</span>
                        </button>
                    </form>
                @endif

                <a
                    href="{{ route('laporan-kerusakan.edit', $laporanKerusakan) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#163828] rounded-xl text-xs font-semibold shadow-2xs transition"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Tindak Lanjut / Edit</span>
                </a>
            </div>
        @elseif($laporanKerusakan->status === 'Dilaporkan')
            <div class="flex items-center gap-2 shrink-0">
                <a
                    href="{{ route('laporan-kerusakan.edit', $laporanKerusakan) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#163828] rounded-xl text-xs font-semibold shadow-2xs transition"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Edit Aduan</span>
                </a>
                <form
                    method="POST"
                    action="{{ route('laporan-kerusakan.destroy', $laporanKerusakan) }}"
                    onsubmit="return confirm('Apakah Anda yakin ingin membatalkan laporan kerusakan ini?')"
                    class="inline"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#FDF0EE] hover:bg-[#FCE3DF] border border-[#F8D4D0] text-[#9C322C] rounded-xl text-xs font-semibold transition cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Batalkan Laporan</span>
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- BENTO DETAILS CARD --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- RINCIAN ADUAN --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Informasi Barang & Pelapor</h2>
            </div>

            <dl class="space-y-3 text-xs">
                @if($laporanKerusakan->bmn)
                    <div class="flex justify-between items-center py-1.5 border-b border-[#E3ECE6]/50">
                        <dt class="text-[#62756A]">Kode Barang</dt>
                        <dd>
                            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                {{ $laporanKerusakan->bmn->kode_barang ?: '—' }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-[#E3ECE6]/50">
                        <dt class="text-[#62756A]">NUP</dt>
                        <dd>
                            <span class="font-mono text-xs font-semibold text-[#132219] bg-[#F4F8F6] px-2 py-0.5 rounded border border-[#E3ECE6]">
                                {{ $laporanKerusakan->bmn->nup ?? '—' }}
                            </span>
                        </dd>
                    </div>
                @endif
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Nama Barang</dt>
                    <dd class="font-bold text-[#132219]">{{ $laporanKerusakan->nama_barang }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Lokasi Barang</dt>
                    <dd class="font-medium text-[#132219]">{{ $laporanKerusakan->lokasi }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Tanggal Laporan</dt>
                    <dd class="font-mono text-[#132219]">
                        {{ $laporanKerusakan->tanggal ? $laporanKerusakan->tanggal->format('d F Y') : '—' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Tingkat Kerusakan</dt>
                    <dd>
                        @php
                            $tingkat = match($laporanKerusakan->tingkat) {
                                'Rusak Berat', 'Berat' => 'Rusak Berat',
                                default => 'Rusak Berat',
                            };
                            $tingkatStyle = match($tingkat) {
                                'Rusak Berat' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                                default => 'bg-[#FFF4E5] text-[#B25E00] border-[#FFE2BF]',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $tingkatStyle }}">
                            {{ $tingkat }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Pegawai Pengaju / Pelapor</dt>
                    <dd class="text-right">
                        <div class="font-bold text-[#132219]">
                            {{ $laporanKerusakan->pelaporPegawai?->nama ?? $laporanKerusakan->pelapor_nama }}
                        </div>
                        @if($laporanKerusakan->pelaporPegawai?->nip)
                            <div class="font-mono text-[10px] text-[#62756A]">
                                NIP. {{ $laporanKerusakan->pelaporPegawai->nip }}
                            </div>
                        @endif
                    </dd>
                </div>
                @if($laporanKerusakan->pelaporPegawai?->unit_kerja || $laporanKerusakan->pelaporPegawai?->jabatan)
                    <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                        <dt class="text-[#62756A]">Unit Kerja / Jabatan</dt>
                        <dd class="text-right font-medium text-[#132219]">
                            {{ $laporanKerusakan->pelaporPegawai->unit_kerja ?? $laporanKerusakan->pelaporPegawai->jabatan }}
                        </dd>
                    </div>
                @endif
                @if($laporanKerusakan->pelapor_email)
                    <div class="flex justify-between py-1.5">
                        <dt class="text-[#62756A]">Email Pelapor</dt>
                        <dd class="text-right font-mono text-[#62756A]">{{ $laporanKerusakan->pelapor_email }}</dd>
                    </div>
                @endif
            </dl>

            <div class="pt-3 border-t border-[#E3ECE6]">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A] mb-1">Kronologi / Keluhan</div>
                <p class="text-xs text-[#3C4F44] whitespace-pre-line bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
                    {{ $laporanKerusakan->deskripsi }}
                </p>
            </div>
        </div>

        {{-- STATUS & RESOLUSI TEKNISI --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Status Penanganan & Solusi</h2>
            </div>

            @php
                $status = $laporanKerusakan->status ?? 'Dilaporkan';
                $statusStyle = match($status) {
                    'Selesai' => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                    'Diterima' => 'bg-[#EBF3FE] text-[#1A73E8] border-[#D2E3FC]',
                    'Diproses' => 'bg-[#FEF7E0] text-[#B06000] border-[#FEEFC3]',
                    'Ditolak' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                    default => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]',
                };
            @endphp

            <div class="p-4 rounded-xl border {{ $statusStyle }} space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider">Status Terkini</span>
                    <span class="font-bold text-sm">{{ $status }}</span>
                </div>
                @if($laporanKerusakan->bmn)
                    <div class="flex items-center justify-between pt-2 border-t border-[#E3ECE6]/60 text-xs">
                        <span class="text-[#62756A]">Kondisi BMN di Master Data:</span>
                        <span class="font-bold {{ in_array($laporanKerusakan->bmn->kondisi, ['Rusak Berat', 'Berat']) ? 'text-[#9C322C]' : (in_array($laporanKerusakan->bmn->kondisi, ['Rusak Ringan', 'Ringan', 'Rusak Sedang', 'Sedang']) ? 'text-[#B25E00]' : 'text-[#137333]') }}">
                            {{ $laporanKerusakan->bmn->kondisi ?: 'Baik' }}
                        </span>
                    </div>
                @endif
                @if($laporanKerusakan->tanggal_update)
                    <div class="text-[11px] opacity-80 pt-1">
                        Diperbarui: {{ $laporanKerusakan->tanggal_update->format('d M Y') }} oleh {{ $laporanKerusakan->ditanggapi_oleh }}
                    </div>
                @endif
            </div>

            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A] mb-1">Tanggapan / Catatan Teknisi</div>
                @if($laporanKerusakan->tanggapan)
                    <p class="text-xs text-[#3C4F44] whitespace-pre-line bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
                        {{ $laporanKerusakan->tanggapan }}
                    </p>
                @else
                    <div class="p-4 rounded-xl bg-[#F7FAF8] border border-dashed border-[#E3ECE6] text-center text-xs text-[#62756A]">
                        Belum ada tanggapan teknis dari operator/teknisi.
                    </div>
                @endif
            </div>

            <div class="p-4 rounded-xl bg-[#EAF2ED]/60 border border-[#D5E5DA] text-xs text-[#163828] space-y-1 mt-4">
                <div class="font-bold text-[11px] uppercase tracking-wider">Instruksi Penanganan</div>
                <p class="text-[11px] text-[#2C4A38] leading-relaxed">
                    Setiap kerusakan yang dilaporkan diverifikasi langsung oleh Sub Bagian Tata Usaha Balai Besar POM di Banjarbaru.
                </p>
            </div>
        </div>

    </div>

</div>
@endsection

