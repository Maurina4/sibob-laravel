@extends('layouts.app')

@php
    $isOperator = auth()->user()->role === 'Operator';
@endphp

@section('title', ($isOperator ? 'Tanggapi Laporan — ' : 'Edit Laporan — ') . $laporanKerusakan->nomor)
@section('page-title', $isOperator ? 'Tanggapi Laporan Kerusakan' : 'Edit Laporan Kerusakan')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('laporan-kerusakan.index', session('laporan_kerusakan_edit_return_query.'.$laporanKerusakan->id, [])) }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Rusak Berat</span>
        </a>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">
                {{ $isOperator ? 'Tindak Lanjut Kerusakan' : 'Edit Laporan Kerusakan' }}
            </h1>
            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                {{ $laporanKerusakan->nomor }}
            </span>
        </div>
        <p class="text-xs text-[#62756A] mt-0.5">
            {{ $isOperator ? 'Berikan tanggapan teknis dan perbarui status perbaikan barang inventaris.' : 'Perbarui rincian aduan kerusakan barang inventaris sebelum laporan diproses.' }}
        </p>
    </div>

    {{-- ERROR VALIDASI --}}
    @if ($errors->any())
        <div class="p-4 rounded-xl border border-[#F8D4D0] bg-[#FDF0EE] text-[#9C322C] text-xs">
            <strong class="font-semibold block mb-1">Periksa kembali data yang dimasukkan:</strong>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($isOperator)
        {{-- RINGKASAN LAPORAN ASAL (UNTUK OPERATOR) --}}
        <div class="bg-white rounded-2xl border border-[#E3ECE6] p-5 shadow-2xs space-y-3">
            <div class="flex items-center justify-between pb-3 border-b border-[#E3ECE6]/70">
                <div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-[#62756A]">Barang Yang Dilaporkan</div>
                    <div class="text-sm font-bold text-[#132219]">{{ $laporanKerusakan->nama_barang }}</div>
                    @if($laporanKerusakan->bmn)
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="font-mono text-[10px] font-semibold text-[#163828] bg-[#EAF2ED] px-1.5 py-0.5 rounded border border-[#D5E5DA]" title="Kode Barang">
                                {{ $laporanKerusakan->bmn->kode_barang ?: '—' }}
                            </span>
                            <span class="font-mono text-[10px] font-semibold text-[#132219] bg-[#F4F8F6] px-1.5 py-0.5 rounded border border-[#E3ECE6]" title="NUP">
                                NUP.{{ $laporanKerusakan->bmn->nup ?? '—' }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-[10px] uppercase font-bold tracking-wider text-[#62756A]">Pegawai Pengaju / Pelapor</div>
                    <div class="text-xs font-bold text-[#132219]">
                        {{ $laporanKerusakan->pelaporPegawai?->nama ?? $laporanKerusakan->pelapor_nama }}
                    </div>
                    @if($laporanKerusakan->pelaporPegawai?->nip)
                        <div class="font-mono text-[10px] text-[#62756A]">
                            NIP. {{ $laporanKerusakan->pelaporPegawai->nip }}
                        </div>
                    @endif
                    @if($laporanKerusakan->pelaporPegawai?->unit_kerja || $laporanKerusakan->pelaporPegawai?->jabatan)
                        <div class="text-[10px] text-[#62756A]">
                            {{ $laporanKerusakan->pelaporPegawai->unit_kerja ?? $laporanKerusakan->pelaporPegawai->jabatan }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="text-xs text-[#4D6357] bg-[#F7FAF8] p-3 rounded-xl border border-[#E3ECE6]">
                <strong>Keluhan:</strong> {{ $laporanKerusakan->deskripsi }}
            </div>
        </div>

        {{-- FORM TANGGAPAN OPERATOR --}}
        <form method="POST" action="{{ route('laporan-kerusakan.update', $laporanKerusakan) }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Tanggapan & Solusi Teknisi / Operator</h2>
            </div>

            {{-- RELASI MASTER BMN (TERKUNCI / READ-ONLY UNTUK OPERATOR) --}}
            <div>
                <x-input-label value="Barang BMN Yang Dilaporkan" />

                @if ($laporanKerusakan->bmn)
                    <input type="hidden" name="bmn_id" value="{{ $laporanKerusakan->bmn_id }}" />
                    <div class="w-full flex items-center justify-between text-xs px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] text-[#132219]">
                        <div class="flex items-center gap-2 truncate pr-2">
                            <svg class="w-4 h-4 text-[#8D9D95] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span class="font-medium text-[#132219] truncate">
                                {{ $laporanKerusakan->bmn->kode_barang }}@if($laporanKerusakan->bmn->nup)-NUP.{{ $laporanKerusakan->bmn->nup }}@endif — {{ $laporanKerusakan->nama_barang }}
                                <span class="text-[#62756A] font-normal">(Kondisi saat ini: {{ $laporanKerusakan->bmn->kondisi ?: 'Baik' }} | Lokasi: {{ $laporanKerusakan->lokasi ?: $laporanKerusakan->bmn->lokasi ?: '—' }})</span>
                            </span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#62756A] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA] shrink-0">
                            <svg class="w-3 h-3 text-[#62756A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Terkunci</span>
                        </span>
                    </div>
                    <p class="text-[11px] text-[#62756A] mt-1">Barang kerusakan telah ditetapkan oleh pelapor dan tidak dapat diubah oleh operator.</p>
                @else
                    <div class="space-y-2">
                        <select
                            name="bmn_id"
                            id="bmn_id"
                            class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
                        >
                            <option value="">-- Hubungkan ke Master Barang BMN Terdaftar (Opsional) --</option>
                            @foreach($bmns as $b)
                                <option value="{{ $b->id }}" @selected(old('bmn_id', $laporanKerusakan->bmn_id) == $b->id)>
                                    {{ $b->kode_barang }}@if($b->nup)-NUP.{{ $b->nup }}@endif — {{ $b->nama_barang }} (Kondisi: {{ $b->kondisi ?: 'Baik' }} | Lokasi: {{ $b->lokasi ?: '—' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-[#62756A]">Laporan ini sebelumnya tidak terhubung ke item BMN spesifik. Anda dapat memilih barang di atas agar kondisi BMN otomatis tersinkronisasi.</p>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- TINGKAT KERUSAKAN --}}
                <div>
                    <x-input-label for="tingkat" value="Tingkat Kerusakan" />
                    <select
                        id="tingkat"
                        name="tingkat"
                        class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
                    >
                        <option value="Rusak Berat" @selected(in_array(old('tingkat', $laporanKerusakan->tingkat), ['Rusak Berat', 'Berat']))>Rusak Berat</option>
                    </select>
                </div>

                {{-- STATUS --}}
                <div>
                    <x-input-label for="status" value="Status Penanganan *" />
                    <select
                        id="status"
                        name="status"
                        required
                        class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
                    >
                        <option value="Dilaporkan" @selected(old('status', $laporanKerusakan->status) === 'Dilaporkan')>Dilaporkan (Menunggu antrean perbaikan)</option>
                        <option value="Diterima" @selected(old('status', $laporanKerusakan->status) === 'Diterima')>Diterima (Laporan diterima & masuk jadwal perbaikan)</option>
                        <option value="Diproses" @selected(old('status', $laporanKerusakan->status) === 'Diproses')>Diproses (Sedang dalam perbaikan teknisi)</option>
                        <option value="Selesai" @selected(old('status', $laporanKerusakan->status) === 'Selesai')>Selesai (Sudah diperbaiki / kondisi kembali Baik)</option>
                        <option value="Ditolak" @selected(old('status', $laporanKerusakan->status) === 'Ditolak')>Ditolak (Laporan tidak disetujui / tidak diproses)</option>
                    </select>
                </div>
            </div>

            {{-- NOTIFIKASI SINKRONISASI KONDISI OTOMATIS --}}
            <div class="p-3 rounded-xl bg-[#EBF3FE] border border-[#D2E3FC] text-xs text-[#1A73E8] flex items-start gap-2.5">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="leading-relaxed">
                    <strong class="font-semibold">Sinkronisasi Kondisi Otomatis:</strong> Saat status disimpan menjadi <strong>Diterima</strong> atau <strong>Diproses</strong>, kondisi barang BMN pada Master Data & modul terkait akan langsung otomatis diperbarui menjadi kondisi yang dilaporkan (<span class="font-semibold underline">{{ in_array($laporanKerusakan->tingkat, ['Rusak Berat', 'Berat']) ? 'Rusak Berat' : 'Rusak Ringan' }}</span>). Saat status <strong>Selesai</strong> atau <strong>Ditolak</strong>, kondisi BMN otomatis dipulihkan menjadi <span class="font-semibold underline">Baik</span>.
                </div>
            </div>

            {{-- TANGGAPAN --}}
            <div>
                <x-input-label for="tanggapan" value="Tanggapan / Catatan Hasil Penanganan" />
                <textarea
                    id="tanggapan"
                    name="tanggapan"
                    rows="4"
                    placeholder="Tuliskan tindakan perbaikan yang telah dilakukan, penggantian suku cadang, atau rekomendasi teknis..."
                    class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
                >{{ old('tanggapan', $laporanKerusakan->tanggapan) }}</textarea>
            </div>

            {{-- BUTTONS OPERATOR --}}
            <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
                <a
                    href="{{ route('laporan-kerusakan.index', session('laporan_kerusakan_edit_return_query.'.$laporanKerusakan->id, [])) }}"
                    class="px-4 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-xl text-xs font-semibold transition"
                >
                    Batalkan
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Tindak Lanjut</span>
                </button>
            </div>
        </form>

    @else
        {{-- FORM EDIT OLEH USER SEBELUM DITERIMA --}}
        @php
            $bmnsJson = $bmns->map(function ($b) {
                $nupStr = $b->nup ? "-NUP.{$b->nup}" : '';
                $label = "{$b->kode_barang}{$nupStr} — {$b->nama_barang} (Lokasi: " . ($b->lokasi ?: '—') . ")";
                return [
                    'id' => (string)$b->id,
                    'kode_barang' => (string)($b->kode_barang ?? ''),
                    'nup' => (string)($b->nup ?? ''),
                    'nama_barang' => $b->nama_barang ?? '',
                    'lokasi' => $b->lokasi ?? '',
                    'label' => $label,
                ];
            })->values();

            $initialBmnId = (string)old('bmn_id', $laporanKerusakan->bmn_id ?? '');
            $selectedBmn = $initialBmnId ? $bmns->firstWhere('id', $initialBmnId) : null;
            $initialBmnLabel = '';
            if ($selectedBmn) {
                $nupStr = $selectedBmn->nup ? "-NUP.{$selectedBmn->nup}" : '';
                $initialBmnLabel = "{$selectedBmn->kode_barang}{$nupStr} — {$selectedBmn->nama_barang} (Lokasi: " . ($selectedBmn->lokasi ?: '—') . ")";
            } elseif ($laporanKerusakan->bmn) {
                $nupStr = $laporanKerusakan->bmn->nup ? "-NUP.{$laporanKerusakan->bmn->nup}" : '';
                $initialBmnLabel = "{$laporanKerusakan->bmn->kode_barang}{$nupStr} — {$laporanKerusakan->bmn->nama_barang} (Lokasi: " . ($laporanKerusakan->bmn->lokasi ?: '—') . ")";
            }
        @endphp

        <form method="POST" action="{{ route('laporan-kerusakan.update', $laporanKerusakan) }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
                <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Rincian Kerusakan Barang</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- NOMOR LAPORAN (READONLY) --}}
                <div>
                    <x-input-label for="nomor" value="Nomor Laporan" />
                    <x-text-input
                        id="nomor"
                        type="text"
                        class="w-full font-mono text-xs bg-[#F7FAF8] cursor-not-allowed text-[#132219] focus:ring-0 focus:border-[#E3ECE6]"
                        :value="$laporanKerusakan->nomor"
                        readonly
                    />
                </div>

                {{-- TANGGAL --}}
                <div>
                    <x-input-label for="tanggal" value="Tanggal Temuan Kerusakan *" />
                    <x-text-input
                        id="tanggal"
                        name="tanggal"
                        type="date"
                        class="w-full text-xs"
                        :value="old('tanggal', $laporanKerusakan->tanggal?->format('Y-m-d'))"
                        max="{{ date('Y-m-d') }}"
                        required
                    />
                    <x-input-error :messages="$errors->get('tanggal')" class="mt-1" />
                </div>
            </div>

            @php
                $userMatch = $pegawais->firstWhere('nama', auth()->user()->name);
                $selectedPelaporId = old('pelapor_pegawai_id', $laporanKerusakan->pelapor_pegawai_id ?? $userMatch?->id);
                $selectedPelaporNama = old('pelapor_nama', $laporanKerusakan->pelapor_nama ?? ($userMatch?->nama ?? auth()->user()->name));
                $selectedPelaporEmail = old('pelapor_email', $laporanKerusakan->pelapor_email ?? auth()->user()->email);
            @endphp

            {{-- PEGAWAI PENGAJU / PELAPOR --}}
            <div>
                <x-input-label for="pelapor_pegawai_id" value="Pegawai Pengaju / Pelapor *" />
                <select
                    id="pelapor_pegawai_id"
                    name="pelapor_pegawai_id"
                    required
                    onchange="
                        const opt = this.options[this.selectedIndex];
                        document.getElementById('pelapor_nama').value = opt.getAttribute('data-nama') || '';
                    "
                    class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2.5 pl-3 pr-10 transition cursor-pointer"
                >
                    <option value="">-- Pilih Pegawai Pengaju dari Database Pegawai --</option>
                    @foreach ($pegawais as $p)
                        <option
                            value="{{ $p->id }}"
                            data-nama="{{ $p->nama }}"
                            @selected((string)$selectedPelaporId === (string)$p->id)
                        >
                            {{ $p->nama }}{{ $p->nip ? " (NIP. {$p->nip})" : '' }}{{ $p->unit_kerja ? " — {$p->unit_kerja}" : ($p->jabatan ? " — {$p->jabatan}" : '') }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="pelapor_nama" id="pelapor_nama" value="{{ $selectedPelaporNama }}" />
                <input type="hidden" name="pelapor_email" id="pelapor_email" value="{{ $selectedPelaporEmail }}" />
                <p class="text-[11px] text-[#62756A] mt-1">
                    Pegawai yang mengajukan laporan kerusakan (berelasi langsung ke database Pegawai agar Operator mengetahui siapa yang mengajukan aduan ini).
                </p>
                <x-input-error :messages="$errors->get('pelapor_pegawai_id')" />
            </div>

            {{-- KODE & NUP BMN (RELASI MASTER BMN) --}}
            <div>
                <x-input-label for="bmn_id" value="Kode & NUP BMN (Pilih Barang Terdaftar)" />
                
                <div
                    x-data="laporanBmnSearchableSelect({
                        initialId: '{{ $initialBmnId }}',
                        initialLabel: '{{ addslashes($initialBmnLabel) }}'
                    })"
                    class="relative w-full"
                >
                    <input
                        type="hidden"
                        name="bmn_id"
                        id="bmn_id"
                        :value="selectedId"
                    />

                    {{-- Trigger Button --}}
                    <button
                        type="button"
                        x-ref="triggerBtn"
                        @click="toggleOpen()"
                        class="w-full flex items-center justify-between text-left text-xs px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] bg-white hover:border-[#163828] focus:outline-none focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 transition cursor-pointer"
                        :class="open ? 'border-[#163828] ring-2 ring-[#163828]/20' : ''"
                    >
                        <div class="flex items-center gap-1.5 truncate pr-2">
                            <span x-show="selectedId" x-text="selectedLabel" class="font-medium text-[#132219] truncate"></span>
                            <span x-show="!selectedId" class="text-[#8D9D95]">-- Pilih Kode & NUP BMN --</span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <span
                                x-show="selectedId"
                                @click.stop="clearSelection()"
                                class="p-0.5 text-[#8D9D95] hover:text-[#9C322C] rounded transition cursor-pointer"
                                title="Kosongkan pilihan"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </span>
                            <svg class="w-4 h-4 text-[#8D9D95] transition-transform duration-200" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    {{-- Floating Dropdown Panel --}}
                    <div
                        x-show="open"
                        @click.outside="closeDropdown()"
                        @scroll.window="if (open) updatePosition()"
                        @resize.window="if (open) updatePosition()"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        style="display: none;"
                        class="fixed z-[9999] bg-white border border-[#D5E5DA] rounded-xl shadow-2xl overflow-hidden flex flex-col"
                        :style="panelStyle"
                    >
                        <div class="p-2.5 border-b border-[#E3ECE6] bg-[#F7FAF8] shrink-0">
                            <div class="relative">
                                <input
                                    x-ref="searchInput"
                                    x-model="search"
                                    type="text"
                                    placeholder="Ketik Kode, NUP, Nama Barang, atau Lokasi..."
                                    class="w-full text-xs pl-8 pr-7 py-2 rounded-lg border border-[#E3ECE6] text-[#132219] bg-white focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                    @keydown.escape="closeDropdown()"
                                    @keydown.enter.prevent="if (filteredItems.length > 0) selectItem(filteredItems[0])"
                                />
                                <svg class="w-3.5 h-3.5 text-[#8D9D95] absolute left-2.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <button
                                    type="button"
                                    x-show="search"
                                    @click="search = ''; $refs.searchInput.focus()"
                                    class="absolute right-2 top-2.5 text-[#8D9D95] hover:text-[#132219]"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex-1 max-h-64 overflow-y-auto divide-y divide-[#E3ECE6]/50 overscroll-contain">
                            <template x-for="item in filteredItems" :key="item.id">
                                <div
                                    @click="selectItem(item)"
                                    class="px-3.5 py-2.5 text-xs cursor-pointer hover:bg-[#EAF2ED] transition flex items-center justify-between gap-2"
                                    :class="String(selectedId) === String(item.id) ? 'bg-[#EAF2ED]/70 font-semibold text-[#163828]' : 'text-[#132219]'"
                                >
                                    <div class="min-w-0">
                                        <div class="font-medium text-[#132219] truncate">
                                            <span class="font-mono text-[#163828]" x-text="item.kode_barang + (item.nup ? '-NUP.' + item.nup : '')"></span>
                                            <span class="text-[#62756A] mx-1">—</span>
                                            <span x-text="item.nama_barang"></span>
                                        </div>
                                        <div class="text-[11px] text-[#62756A] flex items-center gap-2 flex-wrap mt-0.5">
                                            <span>Lokasi: <span class="text-[#132219]" x-text="item.lokasi || '—'"></span></span>
                                        </div>
                                    </div>
                                    <svg x-show="String(selectedId) === String(item.id)" class="w-4 h-4 text-[#163828] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </template>
                            <div x-show="filteredItems.length === 0" class="p-4 text-center text-xs text-[#62756A]">
                                Tidak ditemukan barang dengan kata kunci "<span class="font-semibold" x-text="search"></span>".
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-[11px] text-[#62756A] mt-1">Pilih Kode & NUP dari daftar BMN untuk mengisi Nama Barang dan Ruangan/Lokasi secara otomatis.</p>
            </div>

            {{-- NAMA BARANG (READONLY) --}}
            <div>
                <x-input-label for="nama_barang" value="Nama Barang BMN Yang Rusak *" />
                <x-text-input
                    id="nama_barang"
                    name="nama_barang"
                    type="text"
                    class="w-full text-xs font-medium bg-[#F7FAF8] cursor-not-allowed text-[#132219] focus:ring-0 focus:border-[#E3ECE6]"
                    :value="old('nama_barang', $laporanKerusakan->nama_barang)"
                    placeholder="Otomatis terisi saat memilih Kode & NUP BMN di atas"
                    readonly
                    required
                />
                <p class="text-[11px] text-[#62756A] mt-1">Nama barang terisi otomatis berdasarkan Kode & NUP BMN yang dipilih.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- LOKASI (READONLY) --}}
                <div>
                    <x-input-label for="lokasi" value="Lokasi / Ruangan Barang Berada *" />
                    <x-text-input
                        id="lokasi"
                        name="lokasi"
                        type="text"
                        class="w-full text-xs bg-[#F7FAF8] cursor-not-allowed text-[#132219] focus:ring-0 focus:border-[#E3ECE6]"
                        :value="old('lokasi', $laporanKerusakan->lokasi)"
                        placeholder="Otomatis terisi saat memilih Kode & NUP BMN di atas"
                        readonly
                        required
                    />
                    <p class="text-[11px] text-[#62756A] mt-1">Lokasi ruangan terisi otomatis sesuai data BMN.</p>
                </div>

                {{-- TINGKAT KERUSAKAN --}}
                <div>
                    <x-input-label for="tingkat" value="Tingkat Kerusakan *" />
                    <select
                        id="tingkat"
                        name="tingkat"
                        required
                        class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
                    >
                        <option value="Rusak Berat" {{ in_array(old('tingkat', $laporanKerusakan->tingkat), ['Rusak Berat', 'Berat']) ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                </div>
            </div>

            {{-- DESKRIPSI KERUSAKAN --}}
            <div>
                <x-input-label for="deskripsi" value="Deskripsi Kerusakan & Kronologi *" />
                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    rows="4"
                    placeholder="Jelaskan secara rinci gejala kerusakan yang dialami, indikator eror, atau kronologi kejadian..."
                    class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
                    required
                >{{ old('deskripsi', $laporanKerusakan->deskripsi) }}</textarea>
            </div>

            {{-- BUTTONS USER --}}
            <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-between gap-3">
                <button
                    type="button"
                    onclick="if(confirm('Batalkan laporan kerusakan ini? Data akan dihapus.')) { document.getElementById('batal-laporan-form').submit(); }"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#FDF0EE] hover:bg-[#FCE3DF] border border-[#F8D4D0] text-[#9C322C] rounded-xl text-xs font-semibold transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Batalkan Laporan</span>
                </button>

                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('laporan-kerusakan.show', $laporanKerusakan) }}"
                        class="px-4 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-xl text-xs font-semibold transition"
                    >
                        Kembali
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </form>

        <form id="batal-laporan-form" method="POST" action="{{ route('laporan-kerusakan.destroy', $laporanKerusakan) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

</div>
@endsection

@if (!$isOperator)
    @push('scripts')
    <script>
    window.laporanBmnList = @json($bmnsJson);

    function laporanBmnSearchableSelect(config) {
        return {
            open: false,
            search: '',
            selectedId: config.initialId || '',
            selectedLabel: config.initialLabel || '',
            items: window.laporanBmnList || [],
            panelStyle: '',

            init() {
                if (this.selectedId) {
                    const found = this.items.find(i => String(i.id) === String(this.selectedId));
                    if (found) {
                        if (!this.selectedLabel) {
                            this.selectedLabel = found.label;
                        }
                        const namaBarangInput = document.getElementById('nama_barang');
                        const lokasiInput = document.getElementById('lokasi');
                        if (namaBarangInput && !namaBarangInput.value) {
                            namaBarangInput.value = found.nama_barang || '';
                        }
                        if (lokasiInput && !lokasiInput.value) {
                            lokasiInput.value = found.lokasi || '';
                        }
                    }
                }
            },

            get filteredItems() {
                if (!this.search || !this.search.trim()) {
                    return this.items;
                }
                const q = this.search.toLowerCase().trim();
                return this.items.filter(item => {
                    const kode = (item.kode_barang || '').toLowerCase();
                    const nup = (item.nup || '').toLowerCase();
                    const nama = (item.nama_barang || '').toLowerCase();
                    const lokasi = (item.lokasi || '').toLowerCase();
                    return kode.includes(q) || nup.includes(q) || nama.includes(q) || lokasi.includes(q);
                });
            },

            toggleOpen() {
                this.open = !this.open;
                if (this.open) {
                    this.updatePosition();
                    this.$nextTick(() => {
                        this.updatePosition();
                        if (this.$refs.searchInput) {
                            this.$refs.searchInput.focus();
                        }
                    });
                } else {
                    this.search = '';
                }
            },

            closeDropdown() {
                this.open = false;
                this.search = '';
            },

            updatePosition() {
                const trigger = this.$refs.triggerBtn;
                if (!trigger) return;
                const rect = trigger.getBoundingClientRect();
                const panelHeight = 320;
                const spaceBelow = window.innerHeight - rect.bottom;
                const spaceAbove = rect.top;

                let top;
                if (spaceBelow < panelHeight && spaceAbove > spaceBelow) {
                    top = Math.max(10, rect.top - panelHeight - 6);
                } else {
                    top = rect.bottom + 6;
                }

                const width = rect.width;
                let left = rect.left;
                if (left + width > window.innerWidth - 16) {
                    left = window.innerWidth - width - 16;
                }
                if (left < 16) left = 16;

                this.panelStyle = `top: ${top}px; left: ${left}px; width: ${width}px; max-height: ${panelHeight}px;`;
            },

            selectItem(item) {
                this.selectedId = item.id;
                this.selectedLabel = item.label;
                this.open = false;
                this.search = '';

                const namaBarangInput = document.getElementById('nama_barang');
                const lokasiInput = document.getElementById('lokasi');

                if (namaBarangInput) {
                    namaBarangInput.value = item.nama_barang || '';
                }
                if (lokasiInput) {
                    lokasiInput.value = item.lokasi || '';
                }
            },

            clearSelection() {
                this.selectedId = '';
                this.selectedLabel = '';
                this.open = false;
                this.search = '';

                const namaBarangInput = document.getElementById('nama_barang');
                const lokasiInput = document.getElementById('lokasi');

                if (namaBarangInput) {
                    namaBarangInput.value = '';
                }
                if (lokasiInput) {
                    lokasiInput.value = '';
                }
            }
        };
    }
    </script>
    @endpush
@endif

