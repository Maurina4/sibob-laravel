@extends('layouts.app')

@section('title', 'Daftar BMN TIK — SIBOB')
@section('page-title', 'Daftar BMN TIK')

@section('content')
<div class="space-y-6" x-data="bmnTableBatchManager()" @bmn-field-changed="handleFieldChanged($event)">

    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-[#62756A]">Sub-Kategori Inventaris</div>
            <h1 class="text-2xl font-bold tracking-tight text-[#132219]">Daftar BMN TIK</h1>
            <p class="text-xs text-[#62756A] mt-0.5">Pengelolaan perangkat teknologi informasi dan komunikasi BBPOM di Banjarbaru.</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            {{-- TOMBOL SIMPAN SEMUA PERUBAHAN (BATCH) --}}
            <template x-if="dirtyCount > 0">
                <div class="inline-flex items-center gap-1.5 animate-fadeIn">
                    <button
                        type="button"
                        @click="submitBatch()"
                        class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-[#0E5954] hover:bg-[#163828] active:bg-[#093734] text-white rounded-xl text-xs font-semibold tracking-wide transition shadow-sm cursor-pointer"
                        title="Simpan semua perubahan lokasi dan penanggung jawab di halaman ini"
                    >
                        <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <span>Simpan Semua Perubahan</span>
                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-400 text-emerald-950 font-mono" x-text="dirtyCount"></span>
                    </button>
                    <button
                        type="button"
                        @click="cancelAll()"
                        class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-white hover:bg-rose-50 text-rose-700 hover:text-rose-800 border border-rose-200 rounded-xl text-xs font-semibold transition shadow-2xs cursor-pointer"
                        title="Batalkan semua perubahan di halaman ini"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Batal</span>
                    </button>
                </div>
            </template>

            <a
                href="{{ route('bmns-tik.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#163828] hover:bg-[#1E4834] active:bg-[#0E281C] text-white rounded-xl text-xs font-semibold tracking-wide shadow-2xs transition shrink-0"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah BMN TIK</span>
            </a>
        </div>
    </div>

    {{-- MASTER LOKASI & PEGAWAI MEMORY CACHE UNTUK SEARCHABLE DROPDOWNS --}}
    @php
        $masterLokasiOptions = ($lokasis ?? collect())->map(function($l) {
            return [
                'id' => $l->id,
                'kode' => $l->kode_ruangan ?? '',
                'nama' => $l->nama,
                'label' => $l->format_kombinasi,
            ];
        })->values();

        if (!empty($lokasiFilters)) {
            $existingLabels = $masterLokasiOptions->pluck('label')->map(fn($n) => strtolower(trim($n)))->toArray();
            $existingNames = $masterLokasiOptions->pluck('nama')->map(fn($n) => strtolower(trim($n)))->toArray();
            $existingCodes = $masterLokasiOptions->pluck('kode')->filter()->map(fn($n) => strtolower(trim($n)))->toArray();

            foreach ($lokasiFilters as $extraLok) {
                $extraTrim = strtolower(trim((string)$extraLok));
                if ($extraTrim !== '' && !in_array($extraTrim, $existingLabels) && !in_array($extraTrim, $existingNames) && !in_array($extraTrim, $existingCodes)) {
                    $masterLokasiOptions->push([
                        'id' => null,
                        'kode' => '',
                        'nama' => (string)$extraLok,
                        'label' => (string)$extraLok,
                    ]);
                }
            }
        }

        $masterPegawaiOptions = ($penanggungjawabs ?? collect())->map(function($p) {
            return [
                'id' => $p->id,
                'nama' => $p->nama,
                'nip' => $p->nip ?? '',
                'jabatan' => $p->jabatan ?? '',
                'label' => $p->nip ? "{$p->nama} ({$p->nip})" : $p->nama,
            ];
        })->values();

        $existingPjNames = $masterPegawaiOptions->pluck('nama')->map(fn($n) => strtolower(trim($n)))->toArray();
        foreach ($bmnsTik as $rowBmn) {
            $rawPj = trim((string)($rowBmn->penanggungjawab ?? ''));
            if ($rawPj !== '' && $rawPj !== '—' && !in_array(strtolower($rawPj), $existingPjNames)) {
                $masterPegawaiOptions->push([
                    'id' => null,
                    'nama' => $rawPj,
                    'nip' => '',
                    'jabatan' => '',
                    'label' => $rawPj,
                ]);
                $existingPjNames[] = strtolower($rawPj);
            }
        }
    @endphp
    <script>
        window.__MASTER_LOKASIS__ = @json($masterLokasiOptions);
        window.__MASTER_PEGAWAIS__ = @json($masterPegawaiOptions);

        window.bmnLocationCell = function(config) {
            return {
                bmnId: config.bmnId,
                open: false,
                openUpward: false,
                search: '',
                selected: config.selected || '',
                selectedLabel: config.selectedLabel || '',
                selectedKode: config.selectedKode || '',
                selectedNama: config.selectedNama || 'Pilih Lokasi...',
                initialSelected: config.selected || '',
                initialLabel: config.selectedLabel || '',
                initialKode: config.selectedKode || '',
                initialNama: config.selectedNama || 'Pilih Lokasi...',
                itemName: config.itemName || '',
                init() {
                    window.addEventListener('bmn-batch-reset', () => {
                        if (this.isChanged) {
                            this.resetToInitial();
                        }
                    });
                },
                get isChanged() {
                    return this.selected !== this.initialSelected;
                },
                toggle(triggerEl) {
                    this.open = !this.open;
                    if (this.open) {
                        if (triggerEl) {
                            const rect = triggerEl.getBoundingClientRect();
                            const spaceBelow = window.innerHeight - rect.bottom;
                            this.openUpward = (spaceBelow < 250 && rect.top > spaceBelow);
                        }
                        this.$nextTick(() => {
                            if (this.$refs.rowSearchInput) {
                                this.$refs.rowSearchInput.focus();
                            }
                        });
                    }
                },
                get filtered() {
                    if (!this.search.trim()) return window.__MASTER_LOKASIS__ || [];
                    const q = this.search.toLowerCase().trim();
                    return (window.__MASTER_LOKASIS__ || []).filter(l =>
                        (l.kode && l.kode.toLowerCase().includes(q)) ||
                        (l.nama && l.nama.toLowerCase().includes(q)) ||
                        (l.label && l.label.toLowerCase().includes(q))
                    );
                },
                select(opt) {
                    if (!opt) return;
                    this.selected = opt.label;
                    this.selectedLabel = opt.label;
                    this.selectedKode = opt.kode;
                    this.selectedNama = opt.nama;
                    this.open = false;
                    if (this.$refs.hiddenInput) {
                        this.$refs.hiddenInput.value = opt.label;
                    }
                    this.notifyBatch();
                },
                cancel() {
                    this.resetToInitial();
                    this.notifyBatch();
                },
                resetToInitial() {
                    this.selected = this.initialSelected;
                    this.selectedLabel = this.initialLabel;
                    this.selectedKode = this.initialKode;
                    this.selectedNama = this.initialNama;
                    this.open = false;
                    this.search = '';
                    if (this.$refs.hiddenInput) {
                        this.$refs.hiddenInput.value = this.initialSelected;
                    }
                },
                notifyBatch() {
                    this.$dispatch('bmn-field-changed', {
                        id: this.bmnId,
                        field: 'lokasi',
                        isChanged: this.isChanged,
                        data: this.isChanged ? { lokasi: this.selected } : null
                    });
                },
                confirmSave() {
                    if (!this.isChanged) return;
                    const msg = 'Apakah Anda yakin ingin mengubah lokasi barang "' + this.itemName + '" ke "' + this.selectedLabel + '"?';
                    if (!confirm(msg)) {
                        return;
                    }
                    if (this.$refs.hiddenInput) {
                        this.$refs.hiddenInput.value = this.selected;
                    }
                    if (this.$refs.form) {
                        this.$refs.form.submit();
                    }
                }
            };
        };

        window.bmnPjCell = function(config) {
            return {
                bmnId: config.bmnId,
                open: false,
                openUpward: false,
                search: '',
                selected: config.selected || '',
                selectedId: config.selectedId || null,
                selectedNip: config.selectedNip || '',
                selectedNama: config.selectedNama || 'Pilih PJ...',
                selectedJabatan: config.selectedJabatan || '',
                initialSelected: config.selected || '',
                initialId: config.selectedId || null,
                initialNip: config.selectedNip || '',
                initialNama: config.selectedNama || 'Pilih PJ...',
                initialJabatan: config.selectedJabatan || '',
                itemName: config.itemName || '',
                init() {
                    window.addEventListener('bmn-batch-reset', () => {
                        if (this.isChanged) {
                            this.resetToInitial();
                        }
                    });
                },
                get isChanged() {
                    return (this.selected || '') !== (this.initialSelected || '');
                },
                toggle(triggerEl) {
                    this.open = !this.open;
                    if (this.open) {
                        if (triggerEl) {
                            const rect = triggerEl.getBoundingClientRect();
                            const spaceBelow = window.innerHeight - rect.bottom;
                            this.openUpward = (spaceBelow < 250 && rect.top > spaceBelow);
                        }
                        this.$nextTick(() => {
                            if (this.$refs.rowPjSearchInput) {
                                this.$refs.rowPjSearchInput.focus();
                            }
                        });
                    }
                },
                get filtered() {
                    if (!this.search.trim()) return window.__MASTER_PEGAWAIS__ || [];
                    const q = this.search.toLowerCase().trim();
                    return (window.__MASTER_PEGAWAIS__ || []).filter(p =>
                        (p.nama && p.nama.toLowerCase().includes(q)) ||
                        (p.nip && p.nip.toLowerCase().includes(q)) ||
                        (p.jabatan && p.jabatan.toLowerCase().includes(q))
                    );
                },
                select(opt) {
                    if (!opt) return;
                    this.selected = opt.nama;
                    this.selectedId = opt.id;
                    this.selectedNip = opt.nip || '';
                    this.selectedNama = opt.nama;
                    this.selectedJabatan = opt.jabatan || '';
                    this.open = false;
                    this.notifyBatch();
                },
                cancel() {
                    this.resetToInitial();
                    this.notifyBatch();
                },
                resetToInitial() {
                    this.selected = this.initialSelected;
                    this.selectedId = this.initialId;
                    this.selectedNip = this.initialNip;
                    this.selectedNama = this.initialNama;
                    this.selectedJabatan = this.initialJabatan;
                    this.open = false;
                    this.search = '';
                },
                notifyBatch() {
                    this.$dispatch('bmn-field-changed', {
                        id: this.bmnId,
                        field: 'pj',
                        isChanged: this.isChanged,
                        data: this.isChanged ? {
                            penanggungjawab: this.selected,
                            penanggung_jawab_id: this.selectedId,
                            penanggungjawab_nip: this.selectedNip
                        } : null
                    });
                },
                confirmSave() {
                    if (!this.isChanged) return;
                    const msg = 'Apakah Anda yakin ingin mengubah penanggung jawab barang "' + this.itemName + '" ke "' + this.selectedNama + '"?';
                    if (!confirm(msg)) {
                        return;
                    }
                    if (this.$refs.form) {
                        this.$refs.form.submit();
                    }
                }
            };
        };

        window.bmnTableBatchManager = function() {
            return {
                rowChanges: {},
                isSubmitting: false,
                get dirtyMap() {
                    const map = {};
                    for (const [id, fields] of Object.entries(this.rowChanges)) {
                        const combined = { id: parseInt(id) };
                        let hasChange = false;
                        if (fields.lokasi) {
                            combined.lokasi = fields.lokasi.lokasi;
                            hasChange = true;
                        }
                        if (fields.pj) {
                            combined.penanggungjawab = fields.pj.penanggungjawab;
                            combined.penanggung_jawab_id = fields.pj.penanggung_jawab_id;
                            combined.penanggungjawab_nip = fields.pj.penanggungjawab_nip;
                            hasChange = true;
                        }
                        if (hasChange) {
                            map[id] = combined;
                        }
                    }
                    return map;
                },
                get dirtyCount() {
                    return Object.keys(this.dirtyMap).length;
                },
                handleFieldChanged(e) {
                    const { id, field, isChanged, data } = e.detail;
                    if (!this.rowChanges[id]) {
                        this.rowChanges[id] = {};
                    }
                    if (isChanged && data) {
                        this.rowChanges[id][field] = data;
                    } else {
                        delete this.rowChanges[id][field];
                        if (Object.keys(this.rowChanges[id]).length === 0) {
                            delete this.rowChanges[id];
                        }
                    }
                },
                cancelAll() {
                    if (!confirm('Apakah Anda yakin ingin membatalkan semua perubahan pada halaman ini?')) return;
                    this.rowChanges = {};
                    window.dispatchEvent(new CustomEvent('bmn-batch-reset'));
                },
                submitBatch() {
                    const count = this.dirtyCount;
                    if (count === 0) return;
                    if (!confirm(`Simpan ${count} perubahan BMN TIK pada halaman ini?`)) return;

                    const items = Object.values(this.dirtyMap);
                    this.$refs.batchItemsInput.value = JSON.stringify(items);
                    this.isSubmitting = true;
                    this.$refs.batchForm.submit();
                },
                init() {
                    window.addEventListener('beforeunload', (e) => {
                        if (this.dirtyCount > 0 && !this.isSubmitting) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });

                    document.addEventListener('click', (e) => {
                        if (this.dirtyCount > 0 && !this.isSubmitting) {
                            const link = e.target.closest('a');
                            if (link && link.href && !link.href.startsWith('#') && !link.href.startsWith('javascript:') && !link.target && !link.hasAttribute('download')) {
                                if (!confirm(`Ada ${this.dirtyCount} perubahan yang belum disimpan di halaman ini. Apakah Anda yakin ingin meninggalkan halaman? Perubahan yang belum disimpan akan hilang.`)) {
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                }
                            }
                        }
                    }, true);
                }
            };
        };
    </script>

    {{-- FILTER BAR --}}
    <div class="bg-white p-3.5 rounded-xl border border-[#E3ECE6] shadow-2xs">
        <form method="GET" action="{{ route('bmns-tik.index') }}" class="flex flex-wrap items-center gap-2.5">
            {{-- SEARCH --}}
            <div class="relative flex-1 min-w-[220px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#8D9D95]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari kode atau nama perangkat TIK..."
                    class="w-full pl-9 pr-3 py-2 bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] placeholder-[#8D9D95] transition"
                >
            </div>

            {{-- FILTER JENIS --}}
            <div>
                <select
                    name="jenis"
                    onchange="this.form.submit()"
                    class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 pl-3 pr-9 transition cursor-pointer"
                >
                    <option value="">Jenis Perangkat</option>
                    @foreach($jenisFilters ?? [] as $jenis)
                        <option value="{{ $jenis }}" @selected(request('jenis') === $jenis)>
                            {{ $jenis }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- FILTER LOKASI (SEARCHABLE DROPDOWN BERDASARKAN KODE RUANGAN) --}}
            @php
                $currentLokasiFilter = (string) request('lokasi', '');
                $selectedFilterLabel = 'Semua Lokasi';
                $selectedFilterKode = '';
                if ($currentLokasiFilter !== '') {
                    $matchedFilter = ($lokasis ?? collect())->first(function($l) use ($currentLokasiFilter) {
                        return strcasecmp($l->format_kombinasi, $currentLokasiFilter) === 0
                            || strcasecmp($l->nama, $currentLokasiFilter) === 0
                            || ($l->kode_ruangan && strcasecmp($l->kode_ruangan, $currentLokasiFilter) === 0)
                            || ($l->kode_ruangan && strcasecmp("{$l->kode_ruangan} {$l->nama}", $currentLokasiFilter) === 0);
                    });
                    if ($matchedFilter) {
                        $selectedFilterLabel = $matchedFilter->nama;
                        $selectedFilterKode = $matchedFilter->kode_ruangan ?? '';
                    } else {
                        $selectedFilterLabel = $currentLokasiFilter;
                    }
                }
            @endphp
            <div
                class="relative"
                x-data="{
                    open: false,
                    search: '',
                    selected: @js($currentLokasiFilter),
                    selectedLabel: @js($selectedFilterLabel),
                    selectedKode: @js($selectedFilterKode),
                    get filtered() {
                        if (!this.search.trim()) return window.__MASTER_LOKASIS__ || [];
                        const q = this.search.toLowerCase().trim();
                        return (window.__MASTER_LOKASIS__ || []).filter(l =>
                            (l.kode && l.kode.toLowerCase().includes(q)) ||
                            (l.nama && l.nama.toLowerCase().includes(q)) ||
                            (l.label && l.label.toLowerCase().includes(q))
                        );
                    },
                    select(opt) {
                        this.selected = opt.label;
                        this.selectedLabel = opt.nama;
                        this.selectedKode = opt.kode;
                        this.open = false;
                        this.$nextTick(() => {
                            this.$refs.hiddenLokasiFilter.closest('form').submit();
                        });
                    },
                    clear() {
                        this.selected = '';
                        this.selectedLabel = 'Semua Lokasi';
                        this.selectedKode = '';
                        this.open = false;
                        this.$nextTick(() => {
                            this.$refs.hiddenLokasiFilter.closest('form').submit();
                        });
                    }
                }"
                @click.outside="open = false"
                @keydown.escape="open = false"
            >
                <input type="hidden" name="lokasi" x-ref="hiddenLokasiFilter" :value="selected">

                {{-- Filter Trigger Button --}}
                <button
                    type="button"
                    @click="open = !open; if(open) { $nextTick(() => $refs.filterSearchInput.focus()); }"
                    class="inline-flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs transition cursor-pointer border shadow-2xs font-medium"
                    :class="selected
                        ? 'bg-[#EAF2ED] border-[#163828] text-[#163828] ring-1 ring-[#163828]/10'
                        : 'bg-[#F7FAF8] hover:bg-white text-[#132219] border-[#E3ECE6] hover:border-[#163828]'"
                    title="Filter Lokasi berdasarkan Kode / Nama Ruangan"
                >
                    <div class="flex items-center gap-1.5 min-w-0 max-w-[210px] truncate">
                        <svg class="w-3.5 h-3.5 shrink-0" :class="selected ? 'text-[#163828]' : 'text-[#8D9D95]'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <template x-if="selected && selectedKode">
                            <span class="font-mono text-[10px] font-bold px-1.5 py-0.2 rounded bg-white text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="selectedKode"></span>
                        </template>
                        <span class="truncate" x-text="selectedLabel"></span>
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        <template x-if="selected">
                            <span
                                @click.stop="clear()"
                                class="p-0.5 rounded text-[#163828] hover:text-[#9C322C] hover:bg-[#FDF0EE] transition cursor-pointer"
                                title="Hapus filter lokasi"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </span>
                        </template>
                        <svg class="w-3.5 h-3.5 text-[#8D9D95] transition-transform duration-200" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>

                {{-- Floating Dropdown Panel --}}
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute left-0 top-full mt-1.5 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-[#D5E5DA] p-2.5 z-50 text-xs"
                    style="display: none;"
                >
                    {{-- Search Box --}}
                    <div class="relative mb-2">
                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#8D9D95]">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            x-ref="filterSearchInput"
                            x-model="search"
                            placeholder="Cari kode (A1, B1..) atau nama ruangan..."
                            class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                            @keydown.escape="open = false"
                            @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                        >
                        <button
                            type="button"
                            x-show="search"
                            @click="search = ''; $refs.filterSearchInput.focus()"
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-[#8D9D95] hover:text-[#132219] cursor-pointer"
                        >
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Option List --}}
                    <div class="max-h-60 overflow-y-auto space-y-0.5 divide-y divide-[#E3ECE6]/40" style="max-height: 220px; overflow-y: auto; overscroll-behavior: contain;">
                        {{-- Opsi Semua Lokasi (Reset) --}}
                        <button
                            type="button"
                            @click="clear()"
                            class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-left transition hover:bg-[#F2F6F3] cursor-pointer"
                            :class="!selected ? 'bg-[#EAF2ED] font-semibold text-[#163828]' : 'text-[#62756A]'"
                        >
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full border border-current"></span>
                                <span>Semua Lokasi (Reset)</span>
                            </span>
                            <span x-show="!selected" class="text-[#163828] font-bold">✓</span>
                        </button>

                        <template x-for="opt in filtered" :key="opt.label">
                            <button
                                type="button"
                                @click="select(opt)"
                                class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition hover:bg-[#F2F6F3] cursor-pointer group"
                                :class="(selected === opt.label || selected === opt.nama || selected === opt.kode) ? 'bg-[#EAF2ED] text-[#163828] font-semibold' : 'text-[#132219]'"
                            >
                                <div class="flex items-center gap-2 min-w-0 pr-2">
                                    <template x-if="opt.kode">
                                        <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-white text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="opt.kode"></span>
                                    </template>
                                    <span class="truncate text-xs" x-text="opt.nama"></span>
                                </div>
                                <span x-show="selected === opt.label || selected === opt.nama || selected === opt.kode" class="text-[#163828] font-bold shrink-0 ml-1">✓</span>
                            </button>
                        </template>

                        <div x-show="filtered.length === 0" class="py-6 text-center text-[#8D9D95]">
                            <p class="text-xs">Tidak ada lokasi yang cocok</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILTER PENANGGUNG JAWAB (SEARCHABLE DROPDOWN) --}}
            @php
                $currentPjFilter = request('penanggungjawab', '');
                $pjFilterOptions = collect();
                foreach ($penanggungjawabFilters ?? [] as $pj) {
                    $pjStr = (string)$pj;
                    $pjName = $pjStr;
                    $pjNip = '';
                    if (preg_match('/^(.*?)\s*\(([\d\s-]+)\)$/', $pjStr, $matches)) {
                        $pjName = trim($matches[1]);
                        $pjNip = trim($matches[2]);
                    }
                    $pjFilterOptions->push([
                        'value' => $pjStr,
                        'nama' => $pjName,
                        'nip' => $pjNip,
                        'label' => $pjStr,
                    ]);
                }
                $selectedPjFilterLabel = 'Penanggung Jawab';
                $selectedPjFilterNip = '';
                if ($currentPjFilter) {
                    $matchedPj = $pjFilterOptions->first(fn($o) =>
                        strcasecmp($o['value'], $currentPjFilter) === 0
                        || strcasecmp($o['nama'], $currentPjFilter) === 0
                        || ($o['nip'] && strcasecmp($o['nip'], $currentPjFilter) === 0)
                    );
                    if ($matchedPj) {
                        $selectedPjFilterLabel = $matchedPj['nama'];
                        $selectedPjFilterNip = $matchedPj['nip'];
                    } else {
                        $selectedPjFilterLabel = $currentPjFilter;
                    }
                }
            @endphp
            <div
                class="relative"
                x-data="{
                    open: false,
                    search: '',
                    selected: @js($currentPjFilter),
                    selectedLabel: @js($selectedPjFilterLabel),
                    selectedNip: @js($selectedPjFilterNip),
                    options: @js($pjFilterOptions->values()),
                    get filtered() {
                        if (!this.search.trim()) return this.options;
                        const q = this.search.toLowerCase().trim();
                        return this.options.filter(o =>
                            (o.nip && o.nip.toLowerCase().includes(q)) ||
                            (o.nama && o.nama.toLowerCase().includes(q)) ||
                            (o.label && o.label.toLowerCase().includes(q))
                        );
                    },
                    select(opt) {
                        this.selected = opt.value;
                        this.selectedLabel = opt.nama;
                        this.selectedNip = opt.nip;
                        this.open = false;
                        this.$nextTick(() => {
                            this.$refs.hiddenPjFilter.closest('form').submit();
                        });
                    },
                    clear() {
                        this.selected = '';
                        this.selectedLabel = 'Penanggung Jawab';
                        this.selectedNip = '';
                        this.open = false;
                        this.$nextTick(() => {
                            this.$refs.hiddenPjFilter.closest('form').submit();
                        });
                    }
                }"
                @click.outside="open = false"
                @keydown.escape="open = false"
            >
                <input type="hidden" name="penanggungjawab" x-ref="hiddenPjFilter" :value="selected">

                {{-- Filter Trigger Button --}}
                <button
                    type="button"
                    @click="open = !open; if(open) { $nextTick(() => $refs.pjFilterSearchInput.focus()); }"
                    class="inline-flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs transition cursor-pointer border shadow-2xs font-medium"
                    :class="selected
                        ? 'bg-[#EAF2ED] border-[#163828] text-[#163828] ring-1 ring-[#163828]/10'
                        : 'bg-[#F7FAF8] hover:bg-white text-[#132219] border-[#E3ECE6] hover:border-[#163828]'"
                    title="Filter Penanggung Jawab berdasarkan Nama / NIP"
                >
                    <div class="flex items-center gap-1.5 min-w-0 max-w-[210px] truncate">
                        <svg class="w-3.5 h-3.5 shrink-0" :class="selected ? 'text-[#163828]' : 'text-[#8D9D95]'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <template x-if="selected && selectedNip && selectedNip !== '-'">
                            <span class="font-mono text-[10px] font-bold px-1.5 py-0.2 rounded bg-white text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="selectedNip"></span>
                        </template>
                        <span class="truncate" x-text="selectedLabel"></span>
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        <template x-if="selected">
                            <span
                                @click.stop="clear()"
                                class="p-0.5 rounded text-[#163828] hover:text-[#9C322C] hover:bg-[#FDF0EE] transition cursor-pointer"
                                title="Hapus filter penanggung jawab"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </span>
                        </template>
                        <svg class="w-3.5 h-3.5 text-[#8D9D95] transition-transform duration-200" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>

                {{-- Floating Dropdown Panel --}}
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute left-0 top-full mt-1.5 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-[#D5E5DA] p-2.5 z-50 text-xs"
                    style="display: none;"
                >
                    {{-- Search Box --}}
                    <div class="relative mb-2">
                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#8D9D95]">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            x-ref="pjFilterSearchInput"
                            x-model="search"
                            placeholder="Cari nama atau NIP penanggung jawab..."
                            class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                            @keydown.escape="open = false"
                            @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                        >
                        <button
                            type="button"
                            x-show="search"
                            @click="search = ''; $refs.pjFilterSearchInput.focus()"
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-[#8D9D95] hover:text-[#132219] cursor-pointer"
                        >
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Option List --}}
                    <div class="max-h-60 overflow-y-auto space-y-0.5 divide-y divide-[#E3ECE6]/40" style="max-height: 220px; overflow-y: auto; overscroll-behavior: contain;">
                        {{-- Opsi Semua PJ (Reset) --}}
                        <button
                            type="button"
                            @click="clear()"
                            class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-left transition hover:bg-[#F2F6F3] cursor-pointer"
                            :class="!selected ? 'bg-[#EAF2ED] font-semibold text-[#163828]' : 'text-[#62756A]'"
                        >
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full border border-current"></span>
                                <span>Semua Penanggung Jawab (Reset)</span>
                            </span>
                            <span x-show="!selected" class="text-[#163828] font-bold">✓</span>
                        </button>

                        <template x-for="opt in filtered" :key="opt.value">
                            <button
                                type="button"
                                @click="select(opt)"
                                class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition hover:bg-[#F2F6F3] cursor-pointer group"
                                :class="(selected === opt.value || selected === opt.nama) ? 'bg-[#EAF2ED] text-[#163828] font-semibold' : 'text-[#132219]'"
                            >
                                <div class="flex items-center gap-2 min-w-0 pr-2">
                                    <template x-if="opt.nip && opt.nip !== '-'">
                                        <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-white text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="opt.nip"></span>
                                    </template>
                                    <span class="truncate text-xs" x-text="opt.label"></span>
                                </div>
                                <span x-show="selected === opt.value || selected === opt.nama" class="text-[#163828] font-bold shrink-0 ml-1">✓</span>
                            </button>
                        </template>

                        <div x-show="filtered.length === 0" class="py-6 text-center text-[#8D9D95]">
                            <p class="text-xs">Tidak ada penanggung jawab yang cocok</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILTER KONDISI --}}
            <div>
                <select
                    name="kondisi"
                    onchange="this.form.submit()"
                    class="bg-[#F7FAF8] hover:bg-white focus:bg-white border border-[#E3ECE6] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/20 rounded-lg text-xs text-[#132219] py-2 pl-3 pr-9 transition cursor-pointer"
                >
                    <option value="">Kondisi</option>
                    @foreach($kondisiFilters ?? ['Baik', 'Rusak Berat'] as $kondisiOpt)
                        <option value="{{ $kondisiOpt }}" @selected(request('kondisi') === $kondisiOpt)>
                            {{ $kondisiOpt }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- CARI BUTTON --}}
            <button
                type="submit"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] hover:bg-[#1E4834] text-white rounded-lg text-xs font-semibold tracking-wide shadow-2xs transition cursor-pointer"
            >
                <span>Cari</span>
            </button>

            {{-- RESET --}}
            <a
                href="{{ route('bmns-tik.index') }}"
                class="inline-flex items-center px-3 py-2 bg-white hover:bg-[#F2F6F3] border border-[#E3ECE6] text-[#62756A] hover:text-[#132219] rounded-lg text-xs font-medium transition"
            >
                Reset
            </a>
        </form>
    </div>

    {{-- TABEL DATA TIK --}}
    <div class="bg-white rounded-2xl border border-[#E3ECE6] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F7FAF8] border-b border-[#E3ECE6] text-[#4D6357] text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Kode Barang</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">NUP</th>
                        <th class="py-3.5 px-4">Nama Perangkat TIK</th>
                        <th class="py-3.5 px-4">Kategori TIK</th>
                        <th class="py-3.5 px-4 text-center">Tahun</th>
                        <th class="py-3.5 px-4 text-right">Nilai Perolehan</th>
                        <th class="py-3.5 px-4">Lokasi</th>
                        <th class="py-3.5 px-4 text-center w-14">QR</th>
                        <th class="py-3.5 px-4">Penanggung Jawab</th>
                        <th class="py-3.5 px-4">Kondisi</th>
                        <th class="py-3.5 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3ECE6]/70 text-xs">
                    @forelse($bmnsTik as $index => $item)
                        <tr class="hover:bg-[#F9FCFA] transition">
                            {{-- NO --}}
                            <td class="py-3.5 px-4 text-center text-[#62756A] font-mono text-[11px]">
                                {{ $bmnsTik->firstItem() + $index }}
                            </td>

                            {{-- KODE BARANG --}}
                            <td class="py-3.5 px-4 font-mono text-[11px] whitespace-nowrap">
                                <span class="font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                    {{ $item->kode_barang ?: '—' }}
                                </span>
                            </td>

                            {{-- NUP --}}
                            <td class="py-3.5 px-4 text-center font-mono text-[11px] whitespace-nowrap">
                                <span class="font-semibold text-[#163828] bg-[#EAF2ED] px-2 py-0.5 rounded border border-[#D5E5DA]">
                                    {{ $item->nup ?? '—' }}
                                </span>
                            </td>

                            {{-- NAMA BARANG --}}
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-[#132219] flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#163828] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ $item->nama_barang }}</span>
                                </div>
                                @if($item->is_dikembalikan)
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#FFF4E5] text-[#B25E00] border border-[#FFE2BF]" title="Aset telah dikembalikan ke Pengelola BMN">
                                            <svg class="w-3 h-3 text-[#B25E00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                            <span>Barang Pengembalian</span>
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- JENIS --}}
                            <td class="py-3.5 px-4">
                                @if($item->jenis)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-[#EAF2ED] text-[#163828] border border-[#D5E5DA]">
                                        {{ $item->jenis }}
                                    </span>
                                @else
                                    <span class="text-[#8D9D95] font-mono">—</span>
                                @endif
                            </td>

                            {{-- TAHUN PEROLEHAN --}}
                            <td class="py-3.5 px-4 text-center font-mono text-[11px] text-[#4D6357]">
                                {{ $item->tahun_perolehan ?: '—' }}
                            </td>

                            {{-- NILAI PEROLEHAN --}}
                            <td class="py-3.5 px-4 text-right font-mono text-[11px] font-medium text-[#132219]">
                                {{ $item->nilai_perolehan !== null ? 'Rp ' . number_format($item->nilai_perolehan, 0, ',', '.') : '—' }}
                            </td>

                            {{-- LOKASI (SEARCHABLE DROPDOWN LANGSUNG DI TABEL) --}}
                            @php
                                $rowKode = '';
                                $rowNama = $item->lokasi;
                                if ($item->lokasi) {
                                    $matchedLok = ($lokasis ?? collect())->first(fn ($l) => $l->nama === $item->lokasi || $l->format_kombinasi === $item->lokasi || ($l->kode_ruangan && strcasecmp("{$l->kode_ruangan} {$l->nama}", $item->lokasi) === 0));
                                    if ($matchedLok) {
                                        $rowKode = $matchedLok->kode_ruangan ?? '';
                                        $rowNama = $matchedLok->nama;
                                    }
                                }
                                $rowLabel = $rowKode ? "{$rowKode} - {$rowNama}" : ($rowNama ?: 'Pilih Lokasi...');
                            @endphp
                            <td class="py-2.5 px-3">
                                <form
                                    x-ref="form"
                                    method="POST"
                                    action="{{ route('bmns-tik.update-lokasi', $item) }}"
                                    class="inline-block w-full min-w-[210px] max-w-[320px]"
                                    x-data="bmnLocationCell({
                                        bmnId: {{ $item->id }},
                                        selected: @js($item->lokasi ?: ''),
                                        selectedLabel: @js($rowLabel),
                                        selectedKode: @js($rowKode),
                                        selectedNama: @js($rowNama ?: 'Pilih Lokasi...'),
                                        itemName: @js($item->nama_barang)
                                    })"
                                    onclick="event.stopPropagation()"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="lokasi" x-ref="hiddenInput" :value="selected">

                                    <div class="flex items-center gap-1.5 w-full">
                                        <div class="relative flex-1 min-w-0" @click.outside="open = false" @keydown.escape="open = false">
                                            {{-- Trigger Button --}}
                                            <button
                                                type="button"
                                                @click="toggle($el)"
                                                class="w-full flex items-center justify-between gap-1.5 px-2.5 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] hover:bg-white hover:border-[#163828] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/15 text-xs text-[#132219] transition cursor-pointer shadow-2xs font-medium text-left"
                                                :class="[
                                                    open ? 'border-[#163828] ring-2 ring-[#163828]/15 bg-white' : '',
                                                    isChanged ? 'border-amber-400 bg-amber-50/40 text-amber-950 ring-1 ring-amber-400/50' : ''
                                                ]"
                                                title="Ubah Lokasi Ruangan (Klik untuk cari & pilih)"
                                            >
                                                <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                                                    <svg class="w-3.5 h-3.5 text-[#8D9D95] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    </svg>
                                                    <template x-if="selectedKode">
                                                        <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#EAF2ED] text-[#163828] border border-[#D5E5DA] shadow-2xs shrink-0" x-text="selectedKode"></span>
                                                    </template>
                                                    <span class="truncate text-xs text-[#132219]" x-text="selectedNama"></span>
                                                </div>
                                                <svg class="w-3.5 h-3.5 text-[#8D9D95] shrink-0 transition-transform duration-150" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
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
                                                class="absolute left-0 w-72 sm:w-80 bg-white rounded-xl shadow-xl border border-[#D5E5DA] p-2 z-50 text-xs"
                                                :class="openUpward ? 'bottom-full mb-1' : 'top-full mt-1'"
                                                style="display: none;"
                                            >
                                                {{-- Search Input --}}
                                                <div class="relative mb-1.5">
                                                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#8D9D95]">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                        </svg>
                                                    </span>
                                                    <input
                                                        type="text"
                                                        x-ref="rowSearchInput"
                                                        x-model="search"
                                                        placeholder="Ketik kode (A1, B1..) atau nama ruangan..."
                                                        class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                                        @keydown.escape="open = false"
                                                        @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                                                    >
                                                    <button
                                                        type="button"
                                                        x-show="search"
                                                        @click="search = ''; $refs.rowSearchInput.focus()"
                                                        class="absolute inset-y-0 right-0 pr-2 flex items-center text-[#8D9D95] hover:text-[#132219] cursor-pointer"
                                                    >
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                {{-- Option List --}}
                                                <div class="max-h-36 overflow-y-auto space-y-0.5 divide-y divide-[#E3ECE6]/40" style="max-height: 180px; overflow-y: auto; overscroll-behavior: contain;">
                                                    <template x-for="opt in filtered" :key="opt.label">
                                                        <button
                                                            type="button"
                                                            @click="select(opt)"
                                                            class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition hover:bg-[#EAF2ED] cursor-pointer group"
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

                                        {{-- Tombol Batal (Hanya muncul jika lokasi diubah) --}}
                                        <template x-if="isChanged">
                                            <div class="flex items-center shrink-0">
                                                <button
                                                    type="button"
                                                    @click="cancel()"
                                                    class="w-7 h-7 rounded-lg bg-[#F7FAF8] hover:bg-[#FDF0EE] text-[#62756A] hover:text-[#9C322C] border border-[#D5E5DA] hover:border-[#F8D4D0] flex items-center justify-center shadow-2xs transition cursor-pointer"
                                                    title="Batal Ubah Lokasi"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </form>
                            </td>

                            {{-- QR --}}
                            <td class="py-3.5 px-4 text-center">
                                <button
                                    type="button"
                                    onclick="openQrModal({
                                        title: 'QR Code BMN TIK',
                                        code: '{{ $item->kode_nup }}',
                                        name: '{{ addslashes($item->nama_barang) }}',
                                        lokasi: '{{ addslashes($item->lokasi ?: '—') }}',
                                        pj: '{{ addslashes($item->penanggungjawab ?: '—') }}'
                                    })"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#EAF2ED] text-[#163828] hover:bg-[#163828] hover:text-white transition shadow-2xs cursor-pointer group"
                                    title="Lihat & Cetak QR Code"
                                >
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                                        <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                                        <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                                        <path d="M7 7h.01M17 7h.01M7 17h.01M14 14h3v3h-3zM20 14v3M14 20h3M20 20h.01"></path>
                                    </svg>
                                </button>
                            </td>

                            {{-- PENANGGUNG JAWAB (SEARCHABLE DROPDOWN LANGSUNG DI TABEL) --}}
                            @php
                                $rowPjNama = $item->penanggungjawab ?: '';
                                $rowPjNip = $item->bmn?->penanggungjawab_nip ?: '';
                            @endphp
                            <td class="py-2.5 px-3">
                                <form
                                    x-ref="form"
                                    method="POST"
                                    action="{{ route('bmns-tik.update-penanggungjawab', $item) }}"
                                    class="inline-block w-full min-w-[210px] max-w-[320px]"
                                    x-data="bmnPjCell({
                                        bmnId: {{ $item->id }},
                                        selected: @js($rowPjNama),
                                        selectedId: null,
                                        selectedNip: @js($rowPjNip),
                                        selectedNama: @js($rowPjNama ?: 'Pilih PJ...'),
                                        selectedJabatan: '',
                                        itemName: @js($item->nama_barang)
                                    })"
                                    onclick="event.stopPropagation()"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="penanggungjawab" :value="selected">

                                    <div class="flex items-center gap-1.5 w-full">
                                        <div class="relative flex-1 min-w-0" @click.outside="open = false" @keydown.escape="open = false">
                                            {{-- Trigger Button --}}
                                            <button
                                                type="button"
                                                @click="toggle($el)"
                                                class="w-full flex items-center justify-between gap-1.5 px-2.5 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] hover:bg-white hover:border-[#163828] focus:border-[#163828] focus:ring-2 focus:ring-[#163828]/15 text-xs text-[#132219] transition cursor-pointer shadow-2xs font-medium text-left"
                                                :class="[
                                                    open ? 'border-[#163828] ring-2 ring-[#163828]/15 bg-white' : '',
                                                    isChanged ? 'border-amber-400 bg-amber-50/40 text-amber-950 ring-1 ring-amber-400/50' : ''
                                                ]"
                                                title="Ubah Penanggung Jawab (Klik untuk cari & pilih)"
                                            >
                                                <div class="flex items-center gap-1.5 min-w-0 pr-1 truncate">
                                                    <svg class="w-3.5 h-3.5 text-[#8D9D95] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    <div class="flex flex-col min-w-0 truncate">
                                                        <span class="truncate text-xs text-[#132219]" x-text="selectedNama">{{ $rowPjNama ?: 'Pilih PJ...' }}</span>
                                                        <template x-if="selectedNip && selectedNip !== '-'">
                                                            <span class="text-[10px] text-[#62756A] font-mono leading-tight truncate" x-text="'NIP. ' + selectedNip"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                                <svg class="w-3.5 h-3.5 text-[#8D9D95] shrink-0 transition-transform duration-150" :class="open ? 'rotate-180 text-[#163828]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
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
                                                class="absolute left-0 w-72 sm:w-80 bg-white rounded-xl shadow-xl border border-[#D5E5DA] p-2 z-50 text-xs"
                                                :class="openUpward ? 'bottom-full mb-1' : 'top-full mt-1'"
                                                style="display: none;"
                                            >
                                                {{-- Search Input --}}
                                                <div class="relative mb-1.5">
                                                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-[#8D9D95]">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                        </svg>
                                                    </span>
                                                    <input
                                                        type="text"
                                                        x-ref="rowPjSearchInput"
                                                        x-model="search"
                                                        placeholder="Cari nama atau NIP pegawai..."
                                                        class="w-full pl-8 pr-7 py-1.5 rounded-lg border border-[#E3ECE6] bg-[#F7FAF8] focus:bg-white text-xs text-[#132219] focus:outline-none focus:border-[#163828] focus:ring-1 focus:ring-[#163828] transition"
                                                        @keydown.escape="open = false"
                                                        @keydown.enter.prevent="if(filtered.length > 0) select(filtered[0])"
                                                    >
                                                    <button
                                                        type="button"
                                                        x-show="search"
                                                        @click="search = ''; $refs.rowPjSearchInput.focus()"
                                                        class="absolute inset-y-0 right-0 pr-2 flex items-center text-[#8D9D95] hover:text-[#132219] cursor-pointer"
                                                    >
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                {{-- Option List --}}
                                                <div class="max-h-36 overflow-y-auto space-y-0.5 divide-y divide-[#E3ECE6]/40" style="max-height: 180px; overflow-y: auto; overscroll-behavior: contain;">
                                                    <template x-for="opt in filtered" :key="opt.label">
                                                        <button
                                                            type="button"
                                                            @click="select(opt)"
                                                            class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-left transition hover:bg-[#EAF2ED] cursor-pointer group"
                                                            :class="(selectedNama === opt.nama) ? 'bg-[#EAF2ED] text-[#163828] font-semibold' : 'text-[#132219]'"
                                                        >
                                                            <div class="flex flex-col min-w-0 pr-1 truncate">
                                                                <span class="truncate text-xs" x-text="opt.nama"></span>
                                                                <div class="flex items-center gap-1.5 text-[10px] text-[#62756A]">
                                                                    <template x-if="opt.nip && opt.nip !== '-'">
                                                                        <span class="font-mono" x-text="opt.nip"></span>
                                                                    </template>
                                                                    <template x-if="opt.jabatan">
                                                                        <span class="truncate text-[#8D9D95]" x-text="'• ' + opt.jabatan"></span>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                            <span x-show="selectedNama === opt.nama" class="text-[#163828] font-bold shrink-0 ml-1">✓</span>
                                                        </button>
                                                    </template>
                                                    <div x-show="filtered.length === 0" class="py-4 text-center text-[#8D9D95]">
                                                        <p class="text-xs">Pegawai tidak ditemukan</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Tombol Batal (Hanya muncul jika PJ diubah) --}}
                                        <template x-if="isChanged">
                                            <div class="flex items-center shrink-0">
                                                <button
                                                    type="button"
                                                    @click="cancel()"
                                                    class="w-7 h-7 rounded-lg bg-[#F7FAF8] hover:bg-[#FDF0EE] text-[#62756A] hover:text-[#9C322C] border border-[#D5E5DA] hover:border-[#F8D4D0] flex items-center justify-center shadow-2xs transition cursor-pointer"
                                                    title="Batal Ubah Penanggung Jawab"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </form>

                                @if($item->is_dikembalikan)
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#B25E00] bg-[#FFF4E5] px-1.5 py-0.5 rounded border border-[#FFE2BF]">
                                            <svg class="w-2.5 h-2.5 text-[#B25E00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                            <span>Barang Pengembalian</span>
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- KONDISI --}}
                            <td class="py-3.5 px-4">
                                @php
                                    $kondisi = $item->kondisi ?: ($item->bmn?->kondisi ?: 'Baik');
                                    $kondisiLower = strtolower($kondisi);
                                    $kondisiStyle = match($kondisiLower) {
                                        'baik' => 'bg-[#E6F4EA] text-[#137333] border-[#CEEAD6]',
                                        'rusak sedang' => 'bg-[#FFF4E5] text-[#B25E00] border-[#FFE2BF]',
                                        'rusak ringan' => 'bg-[#FEF7E0] text-[#B06000] border-[#FEEFC3]',
                                        'rusak', 'rusak berat' => 'bg-[#FDF0EE] text-[#9C322C] border-[#F8D4D0]',
                                        default => 'bg-[#F2F5F3] text-[#4A5D52] border-[#D9E2DC]',
                                    };
                                    $dotColor = match($kondisiLower) {
                                        'baik' => 'bg-[#137333]',
                                        'rusak sedang', 'rusak ringan' => 'bg-[#B06000]',
                                        'rusak', 'rusak berat' => 'bg-[#9C322C]',
                                        default => 'bg-[#4A5D52]',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium border {{ $kondisiStyle }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                    <span>{{ $kondisi }}</span>
                                </span>
                            </td>

                            {{-- AKSI --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('bmns-tik.edit', array_merge(['bmnTik' => $item->id], request()->query())) }}"
                                        class="p-1.5 text-[#4D6357] hover:text-[#163828] hover:bg-[#EAF2ED] rounded-lg transition"
                                        title="Edit Data"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    {{-- HAPUS --}}
                                    <form
                                        method="POST"
                                        action="{{ route('bmns-tik.destroy', ['bmnTik' => $item->id]) }}"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data BMN TIK ini?')"
                                        class="inline"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 text-[#9C322C]/70 hover:text-[#9C322C] hover:bg-[#FDF0EE] rounded-lg transition cursor-pointer"
                                            title="Hapus Data"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="py-12 text-center">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-[#EAF2ED] text-[#163828] flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-[#132219]">Belum Ada Data BMN TIK</div>
                                    <p class="text-xs text-[#62756A]">Belum ada perangkat teknologi informasi yang terdaftar.</p>
                                    <a
                                        href="{{ route('bmns-tik.create') }}"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#163828] text-white rounded-lg text-xs font-semibold hover:bg-[#1E4834] transition shadow-2xs"
                                    >
                                        + Tambah BMN TIK Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- FOOTER / PAGINATION --}}
        <div class="px-5 pb-5">
            <x-table-footer :paginator="$bmnsTik" />
        </div>
    </div>

    {{-- FORM SUBMIT BATCH UPDATE LOKASI & PJ --}}
    <form x-ref="batchForm" method="POST" action="{{ route('bmns-tik.batch-update') }}" class="hidden">
        @csrf
        <input type="hidden" name="items" x-ref="batchItemsInput">
        <input type="hidden" name="page" value="{{ request('page') }}">
        <input type="hidden" name="q" value="{{ request('q') }}">
        <input type="hidden" name="jenis" value="{{ request('jenis') }}">
        <input type="hidden" name="lokasi" value="{{ request('lokasi') }}">
        <input type="hidden" name="penanggungjawab" value="{{ request('penanggungjawab') }}">
        <input type="hidden" name="kondisi" value="{{ request('kondisi') }}">
    </form>

    {{-- FLOATING ACTION BAR BATCH UPDATE --}}
    <div
        x-show="dirtyCount > 0"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-6 right-6 z-40 bg-[#163828] text-white px-4 py-3 rounded-2xl shadow-2xl border border-emerald-500/30 flex items-center gap-3.5 backdrop-blur-md"
    >
        <div class="flex items-center gap-2 text-xs">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span><strong x-text="dirtyCount" class="font-bold text-emerald-300"></strong> baris diubah</span>
        </div>
        <div class="h-4 w-[1px] bg-white/20"></div>
        <div class="flex items-center gap-1.5">
            <button
                type="button"
                @click="submitBatch()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500 hover:bg-emerald-400 active:bg-emerald-600 text-emerald-950 font-semibold rounded-xl text-xs transition cursor-pointer shadow-xs"
                title="Simpan semua perubahan"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>Simpan Semua</span>
            </button>
            <button
                type="button"
                @click="cancelAll()"
                class="px-2.5 py-1.5 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl text-xs transition cursor-pointer"
                title="Batalkan semua perubahan"
            >
                Batal
            </button>
        </div>
    </div>

</div>

<x-qr-modal />

@endsection
