@extends('layouts.app')

@section('title', 'Detail BA Perpindahan — ' . ($baPerpindahan->nomor_ba ?: $baPerpindahan->id))
@section('page-title', 'Detail BA Perpindahan')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a
                href="{{ route('ba-perpindahan.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar BA Perpindahan</span>
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Berita Acara Perpindahan</h1>
                <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                    {{ $baPerpindahan->nomor_ba ?: 'BA-MUT/' . $baPerpindahan->id }}
                </span>
            </div>
            <p class="text-xs text-[#62756A] mt-0.5">Rincian mutasi internal dan riwayat persetujuan dokumen.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if($baPerpindahan->canBePrinted())
                <div class="relative" x-data="{ openPrint: false }">
                    <button
                        type="button"
                        @click="openPrint = !openPrint"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#132219] rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5 text-[#163828]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak PDF Srikandi</span>
                        <svg class="w-3 h-3 text-[#62756A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div
                        x-show="openPrint"
                        @click.outside="openPrint = false"
                        x-transition
                        class="absolute right-0 mt-1 w-52 bg-white rounded-xl shadow-lg border border-[#E3ECE6] py-1.5 z-50 text-xs"
                        style="display: none;"
                    >
                        <div class="px-3 py-1 text-[10px] font-bold uppercase text-[#62756A] tracking-wider border-b border-[#E3ECE6]/60">
                            Format PDF (Srikandi TTE)
                        </div>
                        <a
                            href="{{ route('ba-perpindahan.print', [$baPerpindahan, 'size' => 'a4']) }}"
                            target="_blank"
                            class="flex items-center justify-between px-3 py-2 hover:bg-[#F2F6F3] text-[#132219] font-medium"
                        >
                            <span>Ukuran A4</span>
                            <span class="text-[10px] text-[#62756A]">210 &times; 297 mm</span>
                        </a>
                        <a
                            href="{{ route('ba-perpindahan.print', [$baPerpindahan, 'size' => 'f4']) }}"
                            target="_blank"
                            class="flex items-center justify-between px-3 py-2 hover:bg-[#F2F6F3] text-[#132219] font-medium"
                        >
                            <span>Ukuran F4 (Folio)</span>
                            <span class="text-[10px] text-[#62756A]">215 &times; 330 mm</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#F1F3F4] border border-[#DADCE0] text-[#7F8D85] rounded-xl text-xs font-semibold cursor-not-allowed opacity-80" title="Berita Acara belum dapat dicetak karena penerima (Pihak Kedua) belum ditentukan.">
                    <svg class="w-3.5 h-3.5 text-[#7F8D85]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak PDF Srikandi</span>
                    <span class="text-[10px] bg-[#E3ECE6] text-[#62756A] px-1.5 py-0.5 rounded font-normal">Penerima Belum Ada</span>
                </div>
            @endif
            @if($baPerpindahan->status === 'Diajukan' || auth()->user()->role === 'Operator')
                <a
                    href="{{ route('ba-perpindahan.edit', $baPerpindahan) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-xl text-xs font-semibold shadow-2xs transition"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Edit Data</span>
                </a>
            @endif
            @if($baPerpindahan->status !== 'Disetujui')
                <form
                    method="POST"
                    action="{{ route('ba-perpindahan.destroy', $baPerpindahan) }}"
                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data BA perpindahan ini?')"
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
                        <span>Hapus BA</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- BENTO DETAILS CARD --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- RINCIAN PERPINDAHAN --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Rincian Barang & Lokasi</h2>
            </div>

            <dl class="space-y-3 text-xs">
                @if($baPerpindahan->bmn)
                    <div class="flex justify-between items-center py-1.5 border-b border-[#E3ECE6]/50">
                        <dt class="text-[#62756A]">Kode Barang</dt>
                        <dd>
                            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                {{ $baPerpindahan->bmn->kode_barang ?: '—' }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-[#E3ECE6]/50">
                        <dt class="text-[#62756A]">NUP</dt>
                        <dd>
                            <span class="font-mono text-xs font-semibold text-[#132219] bg-[#F4F8F6] px-2 py-0.5 rounded border border-[#E3ECE6]">
                                {{ $baPerpindahan->bmn->nup ?? '—' }}
                            </span>
                        </dd>
                    </div>
                @endif
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Nama Barang</dt>
                    <dd class="font-bold text-[#132219]">{{ $baPerpindahan->nama_barang }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Tanggal Dokumen</dt>
                    <dd class="font-mono text-[#132219]">
                        {{ $baPerpindahan->tanggal ? $baPerpindahan->tanggal->format('d F Y') : '—' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Lokasi Asal (Dari)</dt>
                    <dd class="font-medium text-[#132219]">{{ $baPerpindahan->dari }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Lokasi Tujuan (Ke)</dt>
                    <dd class="font-bold text-[#163828]">{{ $baPerpindahan->ke ?: '—' }}</dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Pegawai Pengaju</dt>
                    <dd class="text-right">
                        <span class="font-bold text-[#163828]">
                            {{ $baPerpindahan->pengajuPegawai ? $baPerpindahan->pengajuPegawai->nama : ($baPerpindahan->pengaju_nama ?: 'Pegawai') }}
                        </span>
                        @if($baPerpindahan->pengajuPegawai?->nip)
                            <div class="text-[11px] font-mono text-[#62756A]">NIP. {{ $baPerpindahan->pengajuPegawai->nip }}</div>
                        @endif
                        @if($baPerpindahan->pengajuPegawai && ($baPerpindahan->pengajuPegawai->unit_kerja || $baPerpindahan->pengajuPegawai->jabatan))
                            <div class="text-[10px] text-[#8D9D95]">{{ $baPerpindahan->pengajuPegawai->unit_kerja ?: $baPerpindahan->pengajuPegawai->jabatan }}</div>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between py-1.5 border-b border-[#E3ECE6]/50">
                    <dt class="text-[#62756A]">Pihak Pertama (Penyerah / Pemegang Awal)</dt>
                    <dd class="text-right font-semibold text-[#132219]">
                        {{ $baPerpindahan->pihakPertamaPegawai ? $baPerpindahan->pihakPertamaPegawai->nama . ($baPerpindahan->pihakPertamaPegawai->nip ? " (NIP. {$baPerpindahan->pihakPertamaPegawai->nip})" : '') : ($baPerpindahan->pihak_pertama ?: ($baPerpindahan->bmn?->penanggungjawab ?: '—')) }}
                    </dd>
                </div>
                <div class="flex justify-between py-1.5">
                    <dt class="text-[#62756A]">Pihak Kedua (Pegawai Penerima Baru)</dt>
                    <dd class="font-semibold text-[#132219]">
                        @if($baPerpindahan->pihak_kedua)
                            <span class="text-[#163828]">{{ $baPerpindahan->pihak_kedua }}</span>
                        @else
                            <span class="text-[#B06000] italic font-normal text-xs bg-[#FEF7E0] px-2 py-0.5 rounded border border-[#FEEFC3]">Belum ditentukan</span>
                        @endif
                    </dd>
                </div>
            </dl>

            @if($baPerpindahan->alasan)
                <div class="pt-3 border-t border-[#E3ECE6]">
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A] mb-1">Alasan Pemindahan</div>
                    <p class="text-xs text-[#3C4F44] whitespace-pre-line bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
                        {{ $baPerpindahan->alasan }}
                    </p>
                </div>
            @endif
        </div>

        {{-- STATUS & KEPUTUSAN OPERATOR (FORM AKSI) --}}
        @php
            $status = $baPerpindahan->status ?? 'Diajukan';
            $statusStyle = match($status) {
                'Disetujui' => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                'Ditolak' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                'Dibatalkan' => 'bg-[#F1F3F4] text-[#5F6368] border-[#DADCE0]',
                default => 'bg-[#FEF7E0] text-[#B06000] border-[#FEEFC3]',
            };
        @endphp
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-6 shadow-2xs space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-[#E3ECE6]">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Status & Form Aksi Verifikasi</h2>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusStyle }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $status === 'Disetujui' ? 'bg-[#137333]' : ($status === 'Ditolak' ? 'bg-[#9C322C]' : ($status === 'Dibatalkan' ? 'bg-[#5F6368]' : 'bg-[#B06000]')) }}"></span>
                    <span>{{ $status }}</span>
                </span>
            </div>

            {{-- DETAIL STATUS TERAKHIR --}}
            <div class="p-4 rounded-xl border {{ $statusStyle }} space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider">Status Dokumen</span>
                    <span class="font-bold text-sm">{{ $status }}</span>
                </div>
                @if($baPerpindahan->tanggal_keputusan)
                    <div class="text-[11px] opacity-80">
                        Diputuskan pada: {{ $baPerpindahan->tanggal_keputusan->format('d M Y') }} oleh {{ $baPerpindahan->diputuskan_oleh }}
                    </div>
                @endif
            </div>

            {{-- KOTAK ALASAN PENOLAKAN (JIKA STATUS DITOLAK) --}}
            @if($status === 'Ditolak')
                <div class="p-4 rounded-xl bg-[#FDF0EE] border border-[#F8D4D0] space-y-2">
                    <div class="flex items-center gap-2 text-[#9C322C] font-bold text-xs uppercase tracking-wider">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Alasan Penolakan oleh Operator</span>
                    </div>
                    <p class="text-xs text-[#9C322C] font-medium whitespace-pre-line bg-white/80 p-3 rounded-lg border border-[#F8D4D0]">
                        {{ $baPerpindahan->catatan_operator ?: 'Tidak ada alasan tertulis yang dicantumkan.' }}
                    </p>
                </div>
            @elseif($status === 'Dibatalkan' && $baPerpindahan->catatan_operator)
                <div class="p-3.5 rounded-xl bg-[#F1F3F4] border border-[#DADCE0] space-y-1">
                    <div class="text-[11px] font-bold text-[#5F6368] uppercase tracking-wider">Riwayat Pembatalan:</div>
                    <p class="text-xs text-[#3C4043] whitespace-pre-line">
                        {{ $baPerpindahan->catatan_operator }}
                    </p>
                </div>
            @elseif($baPerpindahan->catatan_operator)
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A] mb-1">Catatan Operator</div>
                    <p class="text-xs text-[#3C4F44] whitespace-pre-line bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
                        {{ $baPerpindahan->catatan_operator }}
                    </p>
                </div>
            @endif

            {{-- FORM AKSI OPERATOR (AKTIF SAAT DIAJUKAN MAUPUN DIBATALKAN) --}}
            @if(auth()->user()->role === 'Operator')
                @if(in_array($status, ['Diajukan', 'Dibatalkan']))
                    <div id="form-aksi" class="pt-4 border-t border-[#E3ECE6] space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Form Aksi Keputusan</h3>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[#EAF2ED] text-[#163828] border border-[#D5E5DA]">
                                {{ $status === 'Dibatalkan' ? 'Dapat Diputuskan Ulang' : 'Perlu Diverifikasi' }}
                            </span>
                        </div>

                        @php
                            $penyerahNama = $baPerpindahan->pihak_pertama ?: ($baPerpindahan->bmn?->penanggungjawab ?? '');
                            $penyerahId = $baPerpindahan->pihak_pertama_id ?: ($baPerpindahan->bmn?->penanggung_jawab_id ?? null);
                            $pengajuNama = $baPerpindahan->pengajuPegawai?->nama ?: ($baPerpindahan->pengaju_nama ?? '');
                            $pengajuId = $baPerpindahan->pengaju_pegawai_id ?: ($baPerpindahan->pegawai_id ?? null);

                            $filteredPenerimaPegawais = $pegawais->reject(function ($p) use ($penyerahNama, $penyerahId, $pengajuNama, $pengajuId) {
                                if ($penyerahId && (int)$p->id === (int)$penyerahId) {
                                    return true;
                                }
                                if ($pengajuId && (int)$p->id === (int)$pengajuId) {
                                    return true;
                                }
                                $formatted = $p->nip ? "{$p->nama} ({$p->nip})" : $p->nama;
                                if (\App\Models\BaPerpindahan::isSamePerson($penyerahNama, $formatted, $penyerahId, $p->id)) {
                                    return true;
                                }
                                if (\App\Models\BaPerpindahan::isSamePerson($pengajuNama, $formatted, $pengajuId, $p->id)) {
                                    return true;
                                }
                                return false;
                            })->values();

                            $initialSelected = old('pihak_kedua', $baPerpindahan->pihak_kedua ?? '');
                            if ($initialSelected && (\App\Models\BaPerpindahan::isSamePerson($penyerahNama, $initialSelected, $penyerahId) || \App\Models\BaPerpindahan::isSamePerson($pengajuNama, $initialSelected, $pengajuId))) {
                                $initialSelected = '';
                            }
                        @endphp

                        <form
                            method="POST"
                            action="{{ route('ba-perpindahan.process-decision', $baPerpindahan) }}"
                            x-data="{
                                open: false,
                                search: '',
                                selected: @js($initialSelected),
                                pegawais: @js($filteredPenerimaPegawais->map(fn($p) => [
                                    'id' => $p->id,
                                    'label' => $p->nip ? "{$p->nama} ({$p->nip})" : $p->nama,
                                    'nama' => $p->nama,
                                    'nip' => $p->nip ?? '',
                                    'sub' => trim(($p->jabatan ? $p->jabatan : '') . ($p->unit_kerja ? ' • ' . $p->unit_kerja : ''))
                                ])),
                                keputusan: @js(old('keputusan', '')),
                                alasan: @js(old('alasan_penolakan', '')),
                                catatan: @js(old('catatan', '')),
                                get filteredPegawais() {
                                    if (!this.search.trim()) return this.pegawais;
                                    const q = this.search.toLowerCase();
                                    return this.pegawais.filter(p =>
                                        p.nama.toLowerCase().includes(q) ||
                                        (p.nip && p.nip.includes(q)) ||
                                        (p.sub && p.sub.toLowerCase().includes(q))
                                    );
                                },
                                selectPegawai(p) {
                                    this.selected = p.label;
                                    this.open = false;
                                    this.search = '';
                                }
                            }"
                            class="space-y-4"
                        >
                            @csrf
                            @method('PATCH')

                            {{-- INPUT 1: PENANGGUNG JAWAB KEDUA (SEARCHABLE SELECT2-STYLE) --}}
                            <div>
                                <label class="block text-xs font-bold text-[#132219] mb-1.5">
                                    1. Penanggung Jawab Kedua (Penerima Baru) <span class="text-[#9C322C]">*</span>
                                </label>
                                
                                {{-- Alpine.js Searchable Combobox --}}
                                <div class="relative">
                                    {{-- Combobox Trigger Button --}}
                                    <button
                                        type="button"
                                        @click="open = !open; if(open){ $nextTick(() => $refs.searchInput.focus()); }"
                                        class="w-full flex items-center justify-between text-left text-xs px-3.5 py-2.5 rounded-xl border border-[#E3ECE6] bg-white hover:border-[#163828] focus:outline-none focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/15 transition cursor-pointer shadow-2xs"
                                        :class="open ? 'border-[#163828] ring-2 ring-[#163828]/15' : ''"
                                    >
                                        <div class="flex items-center gap-2 truncate pr-2">
                                            <svg class="w-4 h-4 text-[#62756A] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span x-show="selected" x-text="selected" class="font-semibold text-[#132219] truncate"></span>
                                            <span x-show="!selected" class="text-[#8D9D95]">-- Cari & Pilih Pegawai Penerima --</span>
                                        </div>
                                        <svg class="w-4 h-4 text-[#8D9D95] shrink-0 transition-transform duration-200" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    {{-- Hidden Input for Form Submission --}}
                                    <input type="hidden" name="pihak_kedua" :value="selected" required />

                                    {{-- Floating Dropdown Panel --}}
                                    <div
                                        x-show="open"
                                        @click.outside="open = false"
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-1"
                                        style="display: none;"
                                        class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-[#D5E5DA] rounded-xl shadow-lg overflow-hidden"
                                    >
                                        {{-- Search Input inside Dropdown (Select2 style) --}}
                                        <div class="p-2 border-b border-[#E3ECE6] bg-[#F7FAF8]">
                                            <div class="relative">
                                                <input
                                                    x-ref="searchInput"
                                                    x-model="search"
                                                    type="text"
                                                    placeholder="Ketik nama atau NIP pegawai..."
                                                    class="w-full text-xs pl-8 pr-3 py-1.5 rounded-lg border border-[#E3ECE6] text-[#132219] bg-white focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                                    @keydown.escape="open = false"
                                                />
                                                <svg class="w-3.5 h-3.5 text-[#8D9D95] absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </div>
                                        </div>

                                        {{-- Options List --}}
                                        <div class="max-h-56 overflow-y-auto divide-y divide-[#E3ECE6]/50">
                                            <template x-for="p in filteredPegawais" :key="p.id">
                                                <div
                                                    @click="selectPegawai(p)"
                                                    class="px-3.5 py-2 text-xs cursor-pointer hover:bg-[#EAF2ED] transition flex items-center justify-between gap-2"
                                                    :class="selected === p.label ? 'bg-[#EAF2ED]/70 font-semibold text-[#163828]' : 'text-[#132219]'"
                                                >
                                                    <div>
                                                        <div class="font-medium" x-text="p.nama"></div>
                                                        <div class="text-[11px] text-[#62756A] flex items-center gap-1.5">
                                                            <span x-show="p.nip" class="font-mono text-[#163828]" x-text="'NIP. ' + p.nip"></span>
                                                            <span x-show="p.nip && p.sub">•</span>
                                                            <span x-show="p.sub" x-text="p.sub"></span>
                                                        </div>
                                                    </div>
                                                    <svg x-show="selected === p.label" class="w-4 h-4 text-[#163828] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </template>
                                            <div x-show="filteredPegawais.length === 0" class="p-4 text-center text-xs text-[#62756A]">
                                                Tidak ditemukan pegawai dengan nama/NIP tersebut.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-[11px] text-[#62756A] mt-1">Pilih pegawai yang akan menjadi pemegang/penanggung jawab baru barang.</p>
                                <x-input-error :messages="$errors->get('pihak_kedua')" class="mt-1" />
                            </div>

                            {{-- INPUT 2: TOGGLE DUA TOMBOL (SETUJU / DITOLAK) --}}
                            <div>
                                <label class="block text-xs font-bold text-[#132219] mb-1.5">
                                    2. Keputusan Operator <span class="text-[#9C322C]">*</span>
                                </label>
                                <input type="hidden" name="keputusan" :value="keputusan" required />

                                <div class="grid grid-cols-2 gap-3">
                                    {{-- TOMBOL SETUJU --}}
                                    <button
                                        type="button"
                                        @click="keputusan = 'Setuju'"
                                        class="flex items-center justify-center gap-2 p-3 rounded-xl border text-xs font-bold transition cursor-pointer"
                                        :class="keputusan === 'Setuju' ? 'bg-[#E6F4EA] border-[#137333] text-[#137333] ring-2 ring-[#137333]/20 shadow-xs' : 'bg-white border-[#E3ECE6] text-[#62756A] hover:bg-[#F9FCFA] hover:text-[#132219]'"
                                    >
                                        <div class="w-5 h-5 rounded-full flex items-center justify-center" :class="keputusan === 'Setuju' ? 'bg-[#137333] text-white' : 'bg-[#E3ECE6] text-[#62756A]'">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span>Setujui</span>
                                    </button>

                                    {{-- TOMBOL TOLAK --}}
                                    <button
                                        type="button"
                                        @click="keputusan = 'Ditolak'"
                                        class="flex items-center justify-center gap-2 p-3 rounded-xl border text-xs font-bold transition cursor-pointer"
                                        :class="keputusan === 'Ditolak' ? 'bg-[#FDF0EE] border-[#9C322C] text-[#9C322C] ring-2 ring-[#9C322C]/20 shadow-xs' : 'bg-white border-[#E3ECE6] text-[#62756A] hover:bg-[#FDF0EE]/30 hover:text-[#9C322C]'"
                                    >
                                        <div class="w-5 h-5 rounded-full flex items-center justify-center" :class="keputusan === 'Ditolak' ? 'bg-[#9C322C] text-white' : 'bg-[#E3ECE6] text-[#62756A]'">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                        <span>Tolak</span>
                                    </button>
                                </div>
                            </div>

                            {{-- INPUT DINAMIS JIKA DITOLAK (KOLOM ALASAN PENOLAKAN WAJIB) --}}
                            <div x-show="keputusan === 'Ditolak'" x-transition class="space-y-1.5 p-3.5 bg-[#FDF0EE]/60 border border-[#F8D4D0] rounded-xl">
                                <label for="alasan_penolakan" class="block text-xs font-bold text-[#9C322C]">
                                    Alasan Penolakan <span class="text-[#9C322C]">*</span>
                                </label>
                                <textarea
                                    id="alasan_penolakan"
                                    name="alasan_penolakan"
                                    x-model="alasan"
                                    :required="keputusan === 'Ditolak'"
                                    rows="3"
                                    class="w-full text-xs p-3 rounded-lg border border-[#F8D4D0] text-[#132219] bg-white focus:outline-none focus:border-[#9C322C] focus:ring-1 focus:ring-[#9C322C] transition placeholder:text-[#62756A]/60"
                                    placeholder="Tuliskan alasan penolakan secara jelas agar pemohon dapat mengetahuinya..."
                                ></textarea>
                                <p class="text-[11px] text-[#9C322C]">
                                    Pesan ini akan ditampilkan secara transparan kepada pegawai yang mengajukan permohonan.
                                </p>
                            </div>

                            {{-- INPUT DINAMIS JIKA SETUJU (CATATAN OPSIONAL) --}}
                            <div x-show="keputusan === 'Setuju'" x-transition class="space-y-1.5 p-3.5 bg-[#EAF5EE]/60 border border-[#D5E5DA] rounded-xl">
                                <label for="catatan_setuju" class="block text-xs font-semibold text-[#137333]">
                                    Catatan Persetujuan (Opsional)
                                </label>
                                <input
                                    id="catatan_setuju"
                                    name="catatan"
                                    x-model="catatan"
                                    type="text"
                                    class="w-full text-xs px-3 py-2 rounded-lg border border-[#D5E5DA] text-[#132219] bg-white focus:outline-none focus:border-[#137333] transition"
                                    placeholder="Catatan mutasi aset atau serah terima barang..."
                                />
                            </div>

                            {{-- TOMBOL SUBMIT KEPUTUSAN --}}
                            <div class="pt-2">
                                <template x-if="keputusan === 'Setuju'">
                                    <button
                                        type="submit"
                                        :disabled="!selected"
                                        onclick="return confirm('Apakah Anda yakin ingin menyetujui mutasi barang ini? Master BMN akan otomatis diperbarui ke penanggung jawab baru.')"
                                        class="w-full py-2.5 px-4 bg-[#137333] hover:bg-[#0E5C28] disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center justify-center gap-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Konfirmasi & Setujui Perpindahan</span>
                                    </button>
                                </template>

                                <template x-if="keputusan === 'Ditolak'">
                                    <button
                                        type="submit"
                                        :disabled="!selected || !alasan.trim()"
                                        onclick="return confirm('Apakah Anda yakin ingin menolak pengajuan perpindahan ini?')"
                                        class="w-full py-2.5 px-4 bg-[#9C322C] hover:bg-[#83261F] disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center justify-center gap-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span>Konfirmasi & Tolak Pengajuan</span>
                                    </button>
                                </template>

                                <template x-if="!keputusan">
                                    <button
                                        type="button"
                                        disabled
                                        class="w-full py-2.5 px-4 bg-[#E3ECE6] text-[#62756A] rounded-xl text-xs font-semibold cursor-not-allowed text-center"
                                    >
                                        Pilih Penanggung Jawab & Keputusan (Setuju/Tolak) di Atas
                                    </button>
                                </template>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- PEMBATALAN KEPUTUSAN JIKA SUDAH DISETUJUI ATAU DITOLAK --}}
                @if(in_array($status, ['Disetujui', 'Ditolak']))
                    <div class="pt-4 border-t border-[#E3ECE6] space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-[#132219]">Pembatalan Keputusan:</span>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $status === 'Disetujui' ? 'bg-[#E6F4EA] text-[#137333] border border-[#CEEAD6]' : 'bg-[#FDF0EE] text-[#9C322C] border border-[#F8D4D0]' }}">
                                Dokumen {{ $status }}
                            </span>
                        </div>

                        <p class="text-xs text-[#62756A]">
                            @if($status === 'Disetujui')
                                Jika dibatalkan, status akan diubah menjadi <strong>Dibatalkan</strong> dan master BMN dipulihkan ke lokasi semula. Anda dapat memutuskan ulang kembali melalui Form Aksi.
                            @else
                                Jika dibatalkan, penolakan akan ditarik kembali dan Anda dapat memutuskan ulang (Setuju atau Ditolak lagi) melalui Form Aksi.
                            @endif
                        </p>

                        <form method="POST" action="{{ route('ba-perpindahan.cancel-decision', $baPerpindahan) }}" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="catatan_batal" class="block text-xs font-medium text-[#62756A] mb-1">
                                    Catatan Pembatalan (Opsional)
                                </label>
                                <input
                                    id="catatan_batal"
                                    name="catatan"
                                    type="text"
                                    class="w-full text-xs px-3 py-2 rounded-lg border border-[#E3ECE6] text-[#132219] focus:outline-none focus:border-[#163828] transition bg-white"
                                    placeholder="Alasan pembatalan keputusan..."
                                />
                            </div>
                            <button
                                type="submit"
                                onclick="return confirm('Apakah Anda yakin ingin membatalkan keputusan {{ $status }} ini? Anda dapat memutuskan ulang setelah status dibatalkan.')"
                                class="w-full py-2.5 px-3 bg-[#5F6368] hover:bg-[#3C4043] text-white rounded-xl text-xs font-bold transition cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                </svg>
                                <span>Batalkan Keputusan & Buka Kembali Form Aksi</span>
                            </button>
                        </form>
                    </div>
                @endif
            @endif
        </div>

    </div>

</div>
@endsection
