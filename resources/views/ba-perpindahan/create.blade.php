@extends('layouts.app')

@section('title', 'Ajukan BA Perpindahan — SIBOB')
@section('page-title', 'Ajukan BA Perpindahan')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- KEMBALI & HEADER --}}
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
        <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Pengajuan Berita Acara Perpindahan</h1>
        <p class="text-xs text-[#62756A] mt-0.5">Ajukan mutasi atau pemindahan barang BMN antar ruangan atau pemegang aset.</p>
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

    {{-- FORM CARD --}}
    <form method="POST" action="{{ route('ba-perpindahan.store') }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Rincian Pemindahan Barang</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- NOMOR BA --}}
            <div>
                <x-input-label for="nomor_ba" value="Nomor BA Perpindahan" />
                <x-text-input
                    id="nomor_ba"
                    name="nomor_ba"
                    type="text"
                    class="w-full font-mono text-xs"
                    :value="old('nomor_ba')"
                    placeholder="mis. BA-MUT/{{ date('Ymd') }}/001"
                />
            </div>

            {{-- TANGGAL --}}
            <div>
                <x-input-label for="tanggal" value="Tanggal Pemindahan *" />
                <x-text-input
                    id="tanggal"
                    name="tanggal"
                    type="date"
                    class="w-full text-xs"
                    :value="old('tanggal', date('Y-m-d'))"
                    max="{{ date('Y-m-d') }}"
                    required
                />
                <x-input-error :messages="$errors->get('tanggal')" class="mt-1" />
            </div>
        </div>

        {{-- KODE & NUP BMN (RELASI MASTER BMN) --}}
        @php
            $bmnsJson = $bmns->map(function ($b) {
                $nupStr = $b->nup ? "-NUP.{$b->nup}" : '';
                $label = "{$b->kode_barang}{$nupStr} — {$b->nama_barang} (Lokasi: " . ($b->lokasi ?: '—') . " | Penanggung Jawab: " . ($b->penanggungjawab ?: '—') . ")";
                return [
                    'id' => (string)$b->id,
                    'kode_barang' => (string)($b->kode_barang ?? ''),
                    'nup' => (string)($b->nup ?? ''),
                    'nama_barang' => $b->nama_barang ?? '',
                    'lokasi' => $b->lokasi ?? '',
                    'penanggungjawab' => $b->penanggungjawab ?? '',
                    'penanggung_jawab_id' => (string)($b->penanggung_jawab_id ?? ''),
                    'label' => $label,
                ];
            })->values();

            $pegawaisJson = $pegawais->map(function ($p) {
                return [
                    'id' => (string)$p->id,
                    'nama' => (string)$p->nama,
                    'nip' => (string)($p->nip ?? ''),
                    'label' => $p->nip ? "{$p->nama} ({$p->nip})" : (string)$p->nama,
                ];
            })->values();

            $initialBmnId = (string)old('bmn_id', '');
            $selectedBmn = $initialBmnId ? $bmns->firstWhere('id', $initialBmnId) : null;
            $initialBmnLabel = '';
            if ($selectedBmn) {
                $nupStr = $selectedBmn->nup ? "-NUP.{$selectedBmn->nup}" : '';
                $initialBmnLabel = "{$selectedBmn->kode_barang}{$nupStr} — {$selectedBmn->nama_barang} (Lokasi: " . ($selectedBmn->lokasi ?: '—') . " | Penanggung Jawab: " . ($selectedBmn->penanggungjawab ?: '—') . ")";
            }
        @endphp

        <div>
            <x-input-label for="bmn_id" value="Kode & NUP BMN (Pilih Barang Dari Daftar BMN)" />
            
            <div
                x-data="baBmnSearchableSelect({
                    initialId: '{{ $initialBmnId }}',
                    initialLabel: '{{ addslashes($initialBmnLabel) }}'
                })"
                class="relative w-full"
            >
                {{-- Hidden input for form submission --}}
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

                {{-- Floating Dropdown Panel with Search Input --}}
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
                    {{-- Search Input inside Dropdown --}}
                    <div class="p-2.5 border-b border-[#E3ECE6] bg-[#F7FAF8] shrink-0">
                        <div class="relative">
                            <input
                                x-ref="searchInput"
                                x-model="search"
                                type="text"
                                placeholder="Ketik Kode, NUP, Nama Barang, Lokasi, atau Penanggung Jawab..."
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

                    {{-- Options List --}}
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
                                        <span>•</span>
                                        <span>Penanggung Jawab: <span class="text-[#132219]" x-text="item.penanggungjawab || '—'"></span></span>
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

            <p class="text-[11px] text-[#62756A] mt-1">Pilih Kode & NUP dari daftar BMN untuk mengisi Nama Barang dan Lokasi Asal secara otomatis.</p>
        </div>

        {{-- NAMA BARANG --}}
        <div>
            <x-input-label for="nama_barang" value="Nama Barang BMN *" />
            <x-text-input
                id="nama_barang"
                name="nama_barang"
                type="text"
                class="w-full text-xs font-medium bg-[#F7FAF8] cursor-not-allowed text-[#132219] focus:ring-0 focus:border-[#E3ECE6]"
                :value="old('nama_barang')"
                placeholder="Otomatis terisi saat memilih Kode & NUP BMN di atas"
                readonly
                required
            />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- DARI --}}
            <div>
                <x-input-label for="dari" value="Lokasi Asal (Dari) *" />
                <x-text-input
                    id="dari"
                    name="dari"
                    type="text"
                    class="w-full text-xs bg-[#F7FAF8] cursor-not-allowed text-[#132219] focus:ring-0 focus:border-[#E3ECE6]"
                    :value="old('dari')"
                    placeholder="Otomatis terisi saat memilih Kode & NUP BMN di atas"
                    readonly
                    required
                />
            </div>

            {{-- KE --}}
            <div>
                <x-input-label for="ke" value="Lokasi Tujuan (Ke) *" />
                <select
                    id="ke"
                    name="ke"
                    required
                    class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
                >
                    <option value="" data-id="">-- Pilih Lokasi Tujuan dari Master Lokasi --</option>
                    @foreach ($lokasis as $lok)
                        <option
                            value="{{ $lok->nama }}"
                            data-id="{{ $lok->id }}"
                            @selected(old('ke') === $lok->nama || (string)old('lokasi_tujuan_id') === (string)$lok->id)
                        >
                            {{ $lok->format_kombinasi ?? ($lok->id . ' - ' . $lok->nama) }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="lokasi_tujuan_id" id="lokasi_tujuan_id" value="{{ old('lokasi_tujuan_id') }}" />
            </div>
        </div>

        @php
            $userMatch = $pegawais->firstWhere('email', auth()->user()->email) ?? $pegawais->firstWhere('nama', auth()->user()->name);
            $selectedPengajuId = old('pengaju_pegawai_id', old('pegawai_id', $userMatch?->id));
            $selectedPengajuNama = old('pengaju_nama', $userMatch?->nama ?? (auth()->user()->name));
        @endphp

        {{-- PEGAWAI PENGAJU --}}
        <div>
            <x-input-label for="pengaju_pegawai_id" value="Pegawai Pengaju (Pemohon Perpindahan) *" />
            <select
                id="pengaju_pegawai_id"
                name="pengaju_pegawai_id"
                required
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2.5 pl-3 pr-10 transition cursor-pointer"
            >
                <option value="">-- Pilih Pegawai Pengaju dari Database Pegawai --</option>
                @foreach ($pegawais as $p)
                    <option
                        value="{{ $p->id }}"
                        data-nama="{{ $p->nama }}"
                        @selected((string)$selectedPengajuId === (string)$p->id)
                    >
                        {{ $p->nama }}{{ $p->nip ? " (NIP. {$p->nip})" : '' }}{{ $p->unit_kerja ? " — {$p->unit_kerja}" : ($p->jabatan ? " — {$p->jabatan}" : '') }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="pengaju_nama" id="pengaju_nama" value="{{ $selectedPengajuNama }}" />
            <x-input-error :messages="$errors->get('pengaju_pegawai_id')" />
        </div>

        {{-- PIHAK PERTAMA & PIHAK KEDUA --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- PIHAK PERTAMA (PENYERAH / PENANGGUNG JAWAB SAAT INI) --}}
            <div>
                <x-input-label for="pihak_pertama" value="Pihak Pertama (Penanggung Jawab BMN Saat Ini / Yang Menyerahkan) *" />
                <input
                    id="pihak_pertama"
                    name="pihak_pertama"
                    type="text"
                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] bg-[#F7FAF8] cursor-not-allowed focus:outline-none focus:ring-0 focus:border-[#E3ECE6] transition"
                    value="{{ old('pihak_pertama') }}"
                    placeholder="Otomatis terisi saat memilih Kode & NUP BMN di atas"
                    readonly
                    required
                />
                <input type="hidden" name="pihak_pertama_id" id="pihak_pertama_id" value="{{ old('pihak_pertama_id') }}" />
                <input type="hidden" name="pegawai_id" id="pegawai_id" value="{{ old('pegawai_id') }}" />
                <p class="text-[11px] text-[#62756A] mt-1">
                    <span class="inline-flex items-center gap-1 font-semibold text-[#163828]">
                        <svg class="w-3.5 h-3.5 text-[#163828]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pihak Pertama:
                    </span> Sesuai penanggung jawab barang yang terdaftar pada BMN saat ini.
                </p>
                <x-input-error :messages="$errors->get('pihak_pertama')" />
            </div>

            {{-- PIHAK KEDUA (PEGAWAI PENERIMA BARU) --}}
            <div>
                @if(auth()->user()->role === 'Operator')
                    <x-input-label for="pihak_kedua" value="Pihak Kedua (Pegawai Penerima Baru) *" />
                    <input
                        id="pihak_kedua"
                        name="pihak_kedua"
                        type="text"
                        list="pegawai_pihak_kedua_list"
                        class="w-full text-xs font-medium px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition bg-white"
                        value="{{ old('pihak_kedua') }}"
                        placeholder="Pilih atau ketik: Nama (NIP Pegawai)"
                        required
                    />
                    <datalist id="pegawai_pihak_kedua_list">
                        @foreach($pegawais as $p)
                            @php
                                $isPengaju = ((string)$p->id === (string)$selectedPengajuId) || ($selectedPengajuNama && \App\Models\BaPerpindahan::isSamePerson($selectedPengajuNama, $p->nama, null, $p->id));
                                $isPenyerah = old('pihak_pertama_id') && (string)$p->id === (string)old('pihak_pertama_id');
                            @endphp
                            @if(!$isPengaju && !$isPenyerah)
                                <option value="{{ $p->nip ? "{$p->nama} ({$p->nip})" : $p->nama }}"></option>
                            @endif
                        @endforeach
                    </datalist>
                    <p class="text-[11px] text-[#62756A] mt-1">Ditentukan oleh Operator: Pegawai yang akan menerima aset.</p>
                    <p id="pihak_kedua_warning" class="text-[11px] text-[#9C322C] font-semibold mt-1 hidden items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Pegawai penyerah atau pengaju tidak dapat dipilih sebagai penerima barang.</span>
                    </p>
                @else
                    <x-input-label for="pihak_kedua_disabled" value="Pihak Kedua (Pegawai Penerima Baru)" />
                    <input
                        id="pihak_kedua_disabled"
                        type="text"
                        disabled
                        class="w-full text-xs font-medium px-3.5 py-2.5 rounded-lg border border-[#E3ECE6] text-[#62756A] bg-[#F7FAF8] cursor-not-allowed italic"
                        value="Ditentukan oleh Operator (saat verifikasi)"
                    />
                    <p class="text-[11px] text-[#62756A] mt-1 italic">Hanya Operator yang dapat menentukan pegawai penerima baru.</p>
                @endif
                <x-input-error :messages="$errors->get('pihak_kedua')" />
            </div>
        </div>

        {{-- ALASAN --}}
        <div>
            <x-input-label for="alasan" value="Alasan Perpindahan *" />
            <textarea
                id="alasan"
                name="alasan"
                rows="3"
                placeholder="Jelaskan alasan pemindahan (mis. reorganisasi ruangan, peremajaan alat, penugasan baru)..."
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] placeholder-[#94A39B] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs transition p-3"
                required
            >{{ old('alasan') }}</textarea>
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
            <a
                href="{{ route('ba-perpindahan.index') }}"
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
                <span>Kirim Pengajuan</span>
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
window.baBmnList = @json($bmnsJson);
window.allPegawaiPihakKedua = @json($pegawaisJson);

function isPersonMatch(pegawai, searchStr, searchId) {
    if (!searchStr && !searchId) return false;
    if (searchId && String(pegawai.id) === String(searchId)) return true;
    if (!searchStr) return false;

    const s = searchStr.trim().toLowerCase();
    const pLabel = (pegawai.label || '').trim().toLowerCase();
    const pNama = (pegawai.nama || '').trim().toLowerCase();

    if (s === pLabel || s === pNama) return true;

    if (pegawai.nip) {
        const pNip = pegawai.nip.trim().toLowerCase();
        if (s.includes(pNip)) return true;
    }

    const strippedS = s.replace(/\s*\(.*?\)\s*/g, '').trim();
    if (strippedS && strippedS === pNama) return true;

    return false;
}

function syncPihakKeduaList() {
    const pihakPertamaInput = document.getElementById('pihak_pertama');
    const pihakPertamaIdInput = document.getElementById('pihak_pertama_id');
    const pengajuSelect = document.getElementById('pengaju_pegawai_id');
    const pengajuNamaInput = document.getElementById('pengaju_nama');
    const pihakKeduaInput = document.getElementById('pihak_kedua');
    const datalist = document.getElementById('pegawai_pihak_kedua_list');
    const warningEl = document.getElementById('pihak_kedua_warning');
    const warningTextEl = warningEl ? warningEl.querySelector('span') : null;

    if (!datalist) return;

    const penyerahStr = pihakPertamaInput ? pihakPertamaInput.value.trim() : '';
    const penyerahId = pihakPertamaIdInput ? pihakPertamaIdInput.value.trim() : '';
    const pengajuId = pengajuSelect ? pengajuSelect.value.trim() : '';
    const pengajuOpt = pengajuSelect && pengajuSelect.selectedIndex >= 0 ? pengajuSelect.options[pengajuSelect.selectedIndex] : null;
    const pengajuStr = (pengajuOpt ? (optText => optText.getAttribute('data-nama') || optText.textContent)(pengajuOpt) : '') || (pengajuNamaInput ? pengajuNamaInput.value.trim() : '');

    // Rebuild options excluding BOTH the penyerah and pengaju
    datalist.innerHTML = '';
    window.allPegawaiPihakKedua.forEach(p => {
        const isPenyerah = isPersonMatch(p, penyerahStr, penyerahId);
        const isPengaju = isPersonMatch(p, pengajuStr, pengajuId);
        if (!isPenyerah && !isPengaju) {
            const opt = document.createElement('option');
            opt.value = p.label;
            datalist.appendChild(opt);
        }
    });

    // Check if current pihak_kedua matches penyerah or pengaju
    if (pihakKeduaInput && pihakKeduaInput.value.trim()) {
        const currentVal = pihakKeduaInput.value.trim();
        const matchesPenyerah = (penyerahStr || penyerahId) && (window.allPegawaiPihakKedua.some(p => {
            return isPersonMatch(p, penyerahStr, penyerahId) && isPersonMatch(p, currentVal, null);
        }) || (penyerahStr && currentVal.toLowerCase() === penyerahStr.toLowerCase()));

        const matchesPengaju = (pengajuStr || pengajuId) && (window.allPegawaiPihakKedua.some(p => {
            return isPersonMatch(p, pengajuStr, pengajuId) && isPersonMatch(p, currentVal, null);
        }) || (pengajuStr && currentVal.toLowerCase() === pengajuStr.toLowerCase()));

        if (matchesPenyerah || matchesPengaju) {
            pihakKeduaInput.value = '';
            const msg = matchesPengaju
                ? 'Pegawai pengaju tidak dapat dipilih sebagai penerima barang.'
                : 'Pegawai penyerah tidak dapat dipilih sebagai penerima barang.';
            if (warningEl) {
                if (warningTextEl) warningTextEl.textContent = msg;
                warningEl.classList.remove('hidden');
                warningEl.classList.add('flex');
            }
            pihakKeduaInput.setCustomValidity(msg);
        } else {
            if (warningEl) {
                warningEl.classList.add('hidden');
                warningEl.classList.remove('flex');
            }
            pihakKeduaInput.setCustomValidity('');
        }
    }
}

function baBmnSearchableSelect(config) {
    return {
        open: false,
        search: '',
        selectedId: config.initialId || '',
        selectedLabel: config.initialLabel || '',
        items: window.baBmnList || [],
        panelStyle: '',

        init() {
            if (this.selectedId) {
                const found = this.items.find(i => String(i.id) === String(this.selectedId));
                if (found) {
                    if (!this.selectedLabel) {
                        this.selectedLabel = found.label;
                    }
                    const namaBarangInput = document.getElementById('nama_barang');
                    const dariInput = document.getElementById('dari');
                    const pihakPertamaInput = document.getElementById('pihak_pertama');
                    const pihakPertamaIdInput = document.getElementById('pihak_pertama_id');
                    const pegawaiIdInput = document.getElementById('pegawai_id');

                    if (namaBarangInput && !namaBarangInput.value) {
                        namaBarangInput.value = found.nama_barang || '';
                    }
                    if (dariInput && !dariInput.value) {
                        dariInput.value = found.lokasi || '';
                    }
                    if (pihakPertamaInput && !pihakPertamaInput.value) {
                        pihakPertamaInput.value = (found.penanggungjawab || '').trim();
                    }
                    if (pihakPertamaIdInput && !pihakPertamaIdInput.value) {
                        pihakPertamaIdInput.value = found.penanggung_jawab_id || '';
                    }
                    if (pegawaiIdInput && !pegawaiIdInput.value) {
                        pegawaiIdInput.value = found.penanggung_jawab_id || '';
                    }
                }
            }
            syncPihakKeduaList();
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
                const pj = (item.penanggungjawab || '').toLowerCase();
                return kode.includes(q) || nup.includes(q) || nama.includes(q) || lokasi.includes(q) || pj.includes(q);
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
            const dariInput = document.getElementById('dari');
            const pihakPertamaInput = document.getElementById('pihak_pertama');
            const pihakPertamaIdInput = document.getElementById('pihak_pertama_id');
            const pegawaiIdInput = document.getElementById('pegawai_id');

            if (namaBarangInput) namaBarangInput.value = item.nama_barang || '';
            if (dariInput) dariInput.value = item.lokasi || '';
            if (pihakPertamaInput) {
                pihakPertamaInput.value = (item.penanggungjawab || '').trim();
                pihakPertamaInput.readOnly = true;
            }
            if (pihakPertamaIdInput) pihakPertamaIdInput.value = item.penanggung_jawab_id || '';
            if (pegawaiIdInput) pegawaiIdInput.value = item.penanggung_jawab_id || '';

            syncPihakKeduaList();
        },

        clearSelection() {
            this.selectedId = '';
            this.selectedLabel = '';
            this.open = false;
            this.search = '';

            const namaBarangInput = document.getElementById('nama_barang');
            const dariInput = document.getElementById('dari');
            const pihakPertamaInput = document.getElementById('pihak_pertama');
            const pihakPertamaIdInput = document.getElementById('pihak_pertama_id');
            const pegawaiIdInput = document.getElementById('pegawai_id');

            if (namaBarangInput) namaBarangInput.value = '';
            if (dariInput) dariInput.value = '';
            if (pihakPertamaInput) {
                pihakPertamaInput.value = '';
                pihakPertamaInput.readOnly = true;
            }
            if (pihakPertamaIdInput) pihakPertamaIdInput.value = '';
            if (pegawaiIdInput) pegawaiIdInput.value = '';

            syncPihakKeduaList();
        }
    };
}

document.addEventListener('DOMContentLoaded', function () {
    syncPihakKeduaList();

    const keSelect = document.getElementById('ke');
    const lokasiTujuanIdInput = document.getElementById('lokasi_tujuan_id');
    if (keSelect && lokasiTujuanIdInput) {
        keSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            lokasiTujuanIdInput.value = opt ? (opt.getAttribute('data-id') || '') : '';
        });
    }

    const pengajuSelect = document.getElementById('pengaju_pegawai_id');
    const pengajuNamaInput = document.getElementById('pengaju_nama');
    if (pengajuSelect && pengajuNamaInput) {
        pengajuSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            pengajuNamaInput.value = opt ? (opt.getAttribute('data-nama') || '') : '';
            syncPihakKeduaList();
        });
    }

    const pihakKeduaInput = document.getElementById('pihak_kedua');
    if (pihakKeduaInput) {
        function validatePihakKedua(clearOnMatch = false) {
            const pihakPertamaInput = document.getElementById('pihak_pertama');
            const pihakPertamaIdInput = document.getElementById('pihak_pertama_id');
            const penyerahStr = pihakPertamaInput ? pihakPertamaInput.value.trim() : '';
            const penyerahId = pihakPertamaIdInput ? pihakPertamaIdInput.value.trim() : '';

            const pengajuId = pengajuSelect ? pengajuSelect.value.trim() : '';
            const pengajuOpt = pengajuSelect && pengajuSelect.selectedIndex >= 0 ? pengajuSelect.options[pengajuSelect.selectedIndex] : null;
            const pengajuStr = (pengajuOpt ? (optText => optText.getAttribute('data-nama') || optText.textContent)(pengajuOpt) : '') || (pengajuNamaInput ? pengajuNamaInput.value.trim() : '');

            const warningEl = document.getElementById('pihak_kedua_warning');
            const warningTextEl = warningEl ? warningEl.querySelector('span') : null;

            if (!pihakKeduaInput.value.trim()) {
                if (warningEl) {
                    warningEl.classList.add('hidden');
                    warningEl.classList.remove('flex');
                }
                pihakKeduaInput.setCustomValidity('');
                return false;
            }

            const currentVal = pihakKeduaInput.value.trim();
            const matchesPenyerah = (penyerahStr || penyerahId) && (window.allPegawaiPihakKedua.some(p => {
                return isPersonMatch(p, penyerahStr, penyerahId) && isPersonMatch(p, currentVal, null);
            }) || (penyerahStr && currentVal.toLowerCase() === penyerahStr.toLowerCase()));

            const matchesPengaju = (pengajuStr || pengajuId) && (window.allPegawaiPihakKedua.some(p => {
                return isPersonMatch(p, pengajuStr, pengajuId) && isPersonMatch(p, currentVal, null);
            }) || (pengajuStr && currentVal.toLowerCase() === pengajuStr.toLowerCase()));

            if (matchesPenyerah || matchesPengaju) {
                if (clearOnMatch) {
                    pihakKeduaInput.value = '';
                }
                const msg = matchesPengaju
                    ? 'Pegawai pengaju tidak dapat dipilih sebagai penerima barang.'
                    : 'Pegawai penyerah tidak dapat dipilih sebagai penerima barang.';
                if (warningEl) {
                    if (warningTextEl) warningTextEl.textContent = msg;
                    warningEl.classList.remove('hidden');
                    warningEl.classList.add('flex');
                }
                pihakKeduaInput.setCustomValidity(msg);
                return true;
            } else {
                if (warningEl) {
                    warningEl.classList.add('hidden');
                    warningEl.classList.remove('flex');
                }
                pihakKeduaInput.setCustomValidity('');
                return false;
            }
        }

        pihakKeduaInput.addEventListener('input', function () {
            validatePihakKedua(false);
        });

        pihakKeduaInput.addEventListener('change', function () {
            validatePihakKedua(true);
        });
    }

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const pihakPertamaInput = document.getElementById('pihak_pertama');
            const pihakPertamaIdInput = document.getElementById('pihak_pertama_id');
            const pihakKeduaInput = document.getElementById('pihak_kedua');
            if (pihakKeduaInput && pihakKeduaInput.value.trim()) {
                const penyerahStr = pihakPertamaInput ? pihakPertamaInput.value.trim() : '';
                const penyerahId = pihakPertamaIdInput ? pihakPertamaIdInput.value.trim() : '';

                const pengajuId = pengajuSelect ? pengajuSelect.value.trim() : '';
                const pengajuOpt = pengajuSelect && pengajuSelect.selectedIndex >= 0 ? pengajuSelect.options[pengajuSelect.selectedIndex] : null;
                const pengajuStr = (pengajuOpt ? (optText => optText.getAttribute('data-nama') || optText.textContent)(pengajuOpt) : '') || (pengajuNamaInput ? pengajuNamaInput.value.trim() : '');

                const currentVal = pihakKeduaInput.value.trim();

                const matchesPenyerah = (penyerahStr || penyerahId) && (window.allPegawaiPihakKedua.some(p => {
                    return isPersonMatch(p, penyerahStr, penyerahId) && isPersonMatch(p, currentVal, null);
                }) || (penyerahStr && currentVal.toLowerCase() === penyerahStr.toLowerCase()));

                const matchesPengaju = (pengajuStr || pengajuId) && (window.allPegawaiPihakKedua.some(p => {
                    return isPersonMatch(p, pengajuStr, pengajuId) && isPersonMatch(p, currentVal, null);
                }) || (pengajuStr && currentVal.toLowerCase() === pengajuStr.toLowerCase()));

                if (matchesPenyerah || matchesPengaju) {
                    e.preventDefault();
                    const warningEl = document.getElementById('pihak_kedua_warning');
                    const warningTextEl = warningEl ? warningEl.querySelector('span') : null;
                    const alertMsg = matchesPengaju
                        ? 'Penerima (Pihak Kedua) tidak boleh sama dengan Pegawai Pengaju.'
                        : 'Penerima (Pihak Kedua) tidak boleh sama dengan Penyerah (Pihak Pertama).';
                    if (warningEl) {
                        if (warningTextEl) warningTextEl.textContent = alertMsg;
                        warningEl.classList.remove('hidden');
                        warningEl.classList.add('flex');
                    }
                    pihakKeduaInput.focus();
                    alert(alertMsg);
                    return false;
                }
            }
        });
    }
});
</script>
@endpush
