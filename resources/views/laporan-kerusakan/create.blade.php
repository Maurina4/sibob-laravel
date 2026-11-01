@extends('layouts.app')

@section('title', 'Laporkan Kerusakan — SIBOB')
@section('page-title', 'Buat Laporan Kerusakan')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('laporan-kerusakan.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Laporan</span>
        </a>
        <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Form Pelaporan Kerusakan BMN</h1>
        <p class="text-xs text-[#62756A] mt-0.5">Sampaikan keluhan kendala teknis atau kerusakan barang inventaris kepada tim pengelola BMN.</p>
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
    <form method="POST" action="{{ route('laporan-kerusakan.store') }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#163828]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Rincian Kerusakan Barang</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- NOMOR LAPORAN --}}
            <div>
                <x-input-label for="nomor" value="Nomor Laporan" />
                <x-text-input
                    id="nomor"
                    name="nomor"
                    type="text"
                    class="w-full font-mono text-xs"
                    :value="old('nomor')"
                    placeholder="mis. LK/{{ date('Ymd') }}/001"
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
                    :value="old('tanggal', date('Y-m-d'))"
                    max="{{ date('Y-m-d') }}"
                    required
                />
                <x-input-error :messages="$errors->get('tanggal')" class="mt-1" />
            </div>
        </div>

        @php
            $userMatch = $pegawais->firstWhere('nama', auth()->user()->name);
            $selectedPelaporId = old('pelapor_pegawai_id', $userMatch?->id);
            $selectedPelaporNama = old('pelapor_nama', $userMatch?->nama ?? auth()->user()->name);
            $selectedPelaporEmail = old('pelapor_email', auth()->user()->email);
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

            $initialBmnId = (string)old('bmn_id', '');
            $selectedBmn = $initialBmnId ? $bmns->firstWhere('id', $initialBmnId) : null;
            $initialBmnLabel = '';
            if ($selectedBmn) {
                $nupStr = $selectedBmn->nup ? "-NUP.{$selectedBmn->nup}" : '';
                $initialBmnLabel = "{$selectedBmn->kode_barang}{$nupStr} — {$selectedBmn->nama_barang} (Lokasi: " . ($selectedBmn->lokasi ?: '—') . ")";
            }
        @endphp

        <div>
            <x-input-label for="bmn_id" value="Kode & NUP BMN (Pilih Barang Terdaftar)" />
            
            <div
                x-data="laporanBmnSearchableSelect({
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

        {{-- NAMA BARANG --}}
        <div>
            <x-input-label for="nama_barang" value="Nama Barang BMN Yang Rusak *" />
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
            <p class="text-[11px] text-[#62756A] mt-1">Nama barang terisi otomatis berdasarkan Kode & NUP BMN yang dipilih.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- LOKASI --}}
            <div>
                <x-input-label for="lokasi" value="Lokasi / Ruangan Barang Berada *" />
                <x-text-input
                    id="lokasi"
                    name="lokasi"
                    type="text"
                    class="w-full text-xs bg-[#F7FAF8] cursor-not-allowed text-[#132219] focus:ring-0 focus:border-[#E3ECE6]"
                    :value="old('lokasi')"
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
                    <option value="Rusak Ringan" {{ in_array(old('tingkat'), ['Rusak Ringan', 'Ringan']) ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="Rusak Berat" {{ in_array(old('tingkat'), ['Rusak Berat', 'Berat']) ? 'selected' : '' }}>Rusak Berat</option>
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
            >{{ old('deskripsi') }}</textarea>
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
            <a
                href="{{ route('laporan-kerusakan.index') }}"
                class="px-4 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-xl text-xs font-semibold transition"
            >
                Batalkan
            </a>
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Kirim Laporan Kerusakan</span>
            </button>
        </div>
    </form>

</div>
@endsection

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
