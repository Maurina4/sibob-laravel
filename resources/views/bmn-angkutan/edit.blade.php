@extends('layouts.app')

@section('title', 'Ubah Alat Angkutan Bermotor — ' . $bmnAngkutan->nama_barang)
@section('page-title', 'Ubah Alat Angkutan Bermotor')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- KEMBALI & HEADER --}}
    <div>
        <a
            href="{{ route('bmns-angkutan.index', session('bmn_angkutan_edit_return_query.'.$bmnAngkutan->id, [])) }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#62756A] hover:text-[#163828] transition mb-2"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Alat Angkutan Bermotor</span>
        </a>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Ubah Data Alat Angkutan Bermotor</h1>
            <span class="font-mono text-xs font-semibold text-[#163828] bg-[#EAF2ED] px-2.5 py-1 rounded-lg border border-[#D5E5DA]">
                {{ $bmnAngkutan->kode_barang }}-NUP.{{ $bmnAngkutan->nup }}
            </span>
            @if($bmnAngkutan->no_polisi)
                <span class="font-mono text-xs font-bold text-[#B45309] bg-[#FEF6E9] px-2.5 py-1 rounded-lg border border-[#FDE68A]">
                    {{ $bmnAngkutan->no_polisi }}
                </span>
            @endif
        </div>
        <p class="text-xs text-[#62756A] mt-0.5">Perbarui rincian kendaraan: <span class="font-medium text-[#132219]">{{ $bmnAngkutan->nama_barang }}</span></p>
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
    <form method="POST" action="{{ route('bmns-angkutan.update', ['bmnAngkutan' => $bmnAngkutan->id]) }}" class="bg-white rounded-2xl border border-[#E3ECE6] p-6 lg:p-8 shadow-2xs space-y-6">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-2 pb-3 border-b border-[#E3ECE6]">
            <span class="w-2 h-2 rounded-full bg-[#B45309]"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#132219]">Rincian Kendaraan Dinas</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- KODE BARANG --}}
            <div>
                <x-input-label for="kode_barang" value="Kode Barang *" />
                <x-text-input
                    id="kode_barang"
                    name="kode_barang"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="10"
                    data-numeric="true"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                    class="w-full font-mono text-xs"
                    :value="old('kode_barang', $bmnAngkutan->kode_barang)"
                    placeholder="mis. 3020101001 (maksimal 10 angka)"
                    required
                />
                <x-input-error :messages="$errors->get('kode_barang')" class="mt-1" />
            </div>

            {{-- NUP --}}
            <div>
                <x-input-label for="nup" value="NUP *" />
                <x-text-input
                    id="nup"
                    name="nup"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="10"
                    data-numeric="true"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    class="w-full font-mono text-xs"
                    :value="old('nup', $bmnAngkutan->nup)"
                    placeholder="mis. 1"
                    required
                />
                <x-input-error :messages="$errors->get('nup')" class="mt-1" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- NAMA KENDARAAN --}}
            <div>
                <x-input-label for="nama_barang" value="Nama Kendaraan *" />
                <x-text-input
                    id="nama_barang"
                    name="nama_barang"
                    type="text"
                    class="w-full text-xs font-medium"
                    :value="old('nama_barang', $bmnAngkutan->nama_barang)"
                    placeholder="mis. Toyota Kijang Innova 2.0 V A/T / Honda Vario 160"
                    required
                />
                <x-input-error :messages="$errors->get('nama_barang')" class="mt-1" />
            </div>

            {{-- MEREK --}}
            <div>
                <x-input-label for="merek" value="Merek / Pabrikan" />
                <x-text-input
                    id="merek"
                    name="merek"
                    type="text"
                    class="w-full text-xs"
                    :value="old('merek', $bmnAngkutan->merek)"
                    placeholder="mis. Toyota, Honda, Mitsubishi, Suzuki"
                />
                <x-input-error :messages="$errors->get('merek')" class="mt-1" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- NO. POLISI (PLAT NOMOR) - KHUSUS FORM INI --}}
            <div>
                <x-input-label for="no_polisi" value="Nomor Polisi (Plat Nomor) *" />
                <x-text-input
                    id="no_polisi"
                    name="no_polisi"
                    type="text"
                    class="w-full text-xs font-mono uppercase font-bold tracking-wider"
                    :value="old('no_polisi', $bmnAngkutan->no_polisi)"
                    placeholder="mis. DA 1234 AB / DA 1024 BP"
                    oninput="this.value = this.value.toUpperCase()"
                    required
                />
                <p class="text-[11px] text-[#62756A] mt-1">Nomor registrasi kendaraan bermotor resmi.</p>
                <x-input-error :messages="$errors->get('no_polisi')" class="mt-1" />
            </div>

            {{-- TAHUN PEROLEHAN --}}
            <div>
                <x-input-label for="tahun_perolehan" value="Tahun Perolehan" />
                <x-text-input
                    id="tahun_perolehan"
                    name="tahun_perolehan"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="10"
                    data-numeric="true"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    class="w-full text-xs font-mono"
                    :value="old('tahun_perolehan', $bmnAngkutan->tahun_perolehan)"
                    placeholder="mis. {{ date('Y') }}"
                />
                <x-input-error :messages="$errors->get('tahun_perolehan')" class="mt-1" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- NILAI PEROLEHAN --}}
            <div>
                <x-input-label for="nilai_perolehan" value="Nilai Perolehan (Rp)" />
                <x-text-input
                    id="nilai_perolehan"
                    name="nilai_perolehan"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="10"
                    data-numeric="true"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    class="w-full text-xs font-mono"
                    :value="old('nilai_perolehan', $bmnAngkutan->nilai_perolehan ? (int) $bmnAngkutan->nilai_perolehan : '')"
                    placeholder="mis. 350000000"
                />
                <x-input-error :messages="$errors->get('nilai_perolehan')" class="mt-1" />
            </div>

            {{-- LOKASI (SEARCHABLE DROPDOWN) --}}
            @php
                $initLokasi = old('lokasi', $bmnAngkutan->lokasi ?: 'Garasi / Parkir BMN');
                $initKode = '';
                $initNama = $initLokasi;
                if ($initLokasi) {
                    $foundLok = ($lokasis ?? collect())->first(fn($l) =>
                        strcasecmp($l->nama, $initLokasi) === 0
                        || strcasecmp($l->format_kombinasi, $initLokasi) === 0
                        || ($l->kode_ruangan && strcasecmp("{$l->kode_ruangan} {$l->nama}", $initLokasi) === 0)
                        || (str_contains(strtolower($l->nama), 'garasi') || str_contains(strtolower($l->nama), 'parkir'))
                    );
                    if ($foundLok) {
                        $initKode = $foundLok->kode_ruangan ?? '';
                        $initNama = $foundLok->nama;
                        $initLokasi = $foundLok->format_kombinasi ?: $foundLok->nama;
                    }
                }
            @endphp
            <div
                x-data="{
                    open: false,
                    search: '',
                    selected: @js($initLokasi),
                    selectedKode: @js($initKode),
                    selectedNama: @js($initNama ?: 'Pilih Lokasi Ruangan / Garasi...'),
                    options: @js(($lokasis ?? collect())->map(fn($l) => [
                        'nama' => $l->nama,
                        'kode' => $l->kode_ruangan ?? '',
                        'label' => $l->format_kombinasi,
                    ])->values()),
                    get filtered() {
                        if (!this.search.trim()) return this.options;
                        const q = this.search.toLowerCase().trim();
                        return this.options.filter(o =>
                            (o.kode && o.kode.toLowerCase().includes(q)) ||
                            (o.nama && o.nama.toLowerCase().includes(q)) ||
                            (o.label && o.label.toLowerCase().includes(q))
                        );
                    },
                    select(opt) {
                        this.selected = opt.label;
                        this.selectedKode = opt.kode;
                        this.selectedNama = opt.nama;
                        this.open = false;
                        this.search = '';
                    },
                    reset() {
                        this.selected = '';
                        this.selectedKode = '';
                        this.selectedNama = 'Pilih Lokasi Ruangan / Garasi...';
                        this.open = false;
                        this.search = '';
                    }
                }"
                @click.outside="open = false"
                @keydown.escape="open = false"
                class="relative"
            >
                <x-input-label for="lokasi" value="Lokasi Ruangan / Garasi *" />
                <input type="hidden" id="lokasi" name="lokasi" :value="selected" required>

                {{-- Trigger Button --}}
                <div class="relative mt-1">
                    <button
                        type="button"
                        @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()); }"
                        class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg border border-[#E3ECE6] bg-white hover:border-[#163828] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 text-xs text-[#132219] transition cursor-pointer shadow-2xs font-medium text-left"
                        :class="open ? 'border-[#163828] ring-2 ring-[#163828]/20' : ''"
                    >
                        <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                            <svg class="w-3.5 h-3.5 text-[#8D9D95] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <template x-if="selectedKode">
                                <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#EAF2ED] text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="selectedKode"></span>
                            </template>
                            <span class="truncate" :class="selected ? 'text-[#132219]' : 'text-[#8D9D95]'" x-text="selectedNama"></span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <template x-if="selected">
                                <span
                                    @click.stop="reset()"
                                    class="p-0.5 text-[#8D9D95] hover:text-[#B22222] transition cursor-pointer"
                                    title="Hapus pilihan"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </span>
                            </template>
                            <svg class="w-3.5 h-3.5 text-[#8D9D95] transition-transform duration-150" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    {{-- Floating Searchable Panel --}}
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 top-full mt-1 w-full bg-white rounded-xl shadow-xl border border-[#D5E5DA] p-2 z-50 text-xs"
                        style="display: none;"
                    >
                        <div class="relative mb-1.5">
                            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#8D9D95]">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input
                                type="text"
                                x-ref="searchInput"
                                x-model="search"
                                placeholder="Ketik kode (A1, B1..) atau nama ruangan/garasi..."
                                class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                @keydown.escape="open = false"
                                @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                            >
                            <button
                                type="button"
                                x-show="search"
                                @click="search = ''; $refs.searchInput.focus()"
                                class="absolute inset-y-0 right-0 pr-2 flex items-center text-[#8D9D95] hover:text-[#132219] cursor-pointer"
                            >
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="max-h-56 overflow-y-auto space-y-0.5 divide-y divide-[#E3ECE6]/40" style="max-height: 220px; overflow-y: auto; overscroll-behavior: contain;">
                            <template x-for="opt in filtered" :key="opt.label">
                                <button
                                    type="button"
                                    @click="select(opt)"
                                    class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-left transition hover:bg-[#EAF2ED] cursor-pointer group"
                                    :class="(selectedNama === opt.nama || selected === opt.label) ? 'bg-[#EAF2ED] text-[#163828] font-semibold' : 'text-[#132219]'"
                                >
                                    <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                                        <template x-if="opt.kode">
                                            <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-white text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="opt.kode"></span>
                                        </template>
                                        <span class="truncate text-xs" x-text="opt.nama"></span>
                                    </div>
                                    <span x-show="selectedNama === opt.nama || selected === opt.label" class="text-[#163828] font-bold shrink-0 ml-1">✓</span>
                                </button>
                            </template>
                            <div x-show="filtered.length === 0" class="py-4 text-center text-[#8D9D95]">
                                <p class="text-xs">Tidak ada ruangan cocok</p>
                            </div>
                        </div>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('lokasi')" class="mt-1" />
            </div>
        </div>

        {{-- PENANGGUNG JAWAB --}}
        <div>
            <x-input-label for="penanggungjawab" value="Penanggung Jawab *" />
            <select
                id="penanggungjawab"
                name="penanggungjawab"
                required
                class="w-full border border-[#E3ECE6] bg-white text-[#132219] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs py-2 pl-3 pr-10 transition cursor-pointer"
            >
                <option value="">Pilih Penanggung Jawab</option>
                @foreach ($pegawais as $pegawai)
                    @php
                        $pjFormatted = $pegawai->nip ? "{$pegawai->nama} ({$pegawai->nip})" : $pegawai->nama;
                    @endphp
                    <option value="{{ $pjFormatted }}" @selected(old('penanggungjawab', $bmnAngkutan->penanggungjawab) === $pjFormatted || old('penanggungjawab', $bmnAngkutan->penanggungjawab) === $pegawai->nama)>
                        {{ $pjFormatted }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('penanggungjawab')" class="mt-1" />
        </div>

        {{-- KONDISI FISIK --}}
        <div>
            <x-input-label value="Kondisi Fisik *" />
            @php
                $curKondisi = old('kondisi', $bmnAngkutan->kondisi ?: 'Baik');
            @endphp
            <div class="grid grid-cols-3 gap-2 mt-1">
                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#E3ECE6] cursor-pointer hover:bg-[#F7FAF8] transition">
                    <input
                        type="radio"
                        name="kondisi"
                        value="Baik"
                        class="text-[#163828] focus:ring-[#163828]"
                        {{ $curKondisi === 'Baik' ? 'checked' : '' }}
                    >
                    <span class="text-xs font-semibold text-[#137333]">Baik</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#E3ECE6] cursor-pointer hover:bg-[#F7FAF8] transition">
                    <input
                        type="radio"
                        name="kondisi"
                        value="Rusak Ringan"
                        class="text-[#163828] focus:ring-[#163828]"
                        {{ $curKondisi === 'Rusak Ringan' ? 'checked' : '' }}
                    >
                    <span class="text-xs font-semibold text-[#B25E00]">Rusak Ringan</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-[#E3ECE6] cursor-pointer hover:bg-[#F7FAF8] transition">
                    <input
                        type="radio"
                        name="kondisi"
                        value="Rusak Berat"
                        class="text-[#163828] focus:ring-[#163828]"
                        {{ $curKondisi === 'Rusak Berat' ? 'checked' : '' }}
                    >
                    <span class="text-xs font-semibold text-[#9C322C]">Rusak Berat</span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('kondisi')" class="mt-1" />
        </div>

        {{-- BUTTONS --}}
        <div class="pt-4 border-t border-[#E3ECE6] flex items-center justify-end gap-3">
            <a
                href="{{ route('bmns-angkutan.index', session('bmn_angkutan_edit_return_query.'.$bmnAngkutan->id, [])) }}"
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
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>

</div>
@endsection
