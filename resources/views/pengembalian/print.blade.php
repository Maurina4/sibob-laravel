@php
    $kopMasterBase64 = '';
    $kopPath = public_path('images/kop-header-white.png');
    if (!file_exists($kopPath)) {
        $kopPath = public_path('images/kop-header.png');
    }
    if (file_exists($kopPath)) {
        $kopMasterBase64 = base64_encode(file_get_contents($kopPath));
    }
    $kopFooterBase64 = '';
    $footerPath = public_path('images/kop-footer.png');
    if (file_exists($footerPath)) {
        $kopFooterBase64 = base64_encode(file_get_contents($footerPath));
    }

    $pihakPertama = $pihakPertama ?? [
        'nama' => $pengembalian->nama_pengaju ?: '....................................................',
        'nip' => $pengembalian->pengajuPegawai?->nip ?: '-',
        'jabatan' => $pengembalian->pengajuPegawai?->jabatan ?: 'Pegawai Pemakai BMN',
        'unit_kerja' => $pengembalian->bidang ?: 'Balai Besar POM di Banjarbaru',
    ];

    $pihakKedua = $pihakKedua ?? [
        'nama' => 'Yuli Supriyatno, A.Md.',
        'nip' => '198407222023211010',
        'jabatan' => 'Pengelola Barang Milik Negara (BMN)',
        'unit_kerja' => 'Balai Besar POM di Banjarbaru',
    ];

    $kabalaiNama = $kabalaiNama ?? 'Ary Yustantiningsih,S.Si.,Apt.';
    $kabalaiNip = $kabalaiNip ?? '-';
    $kasubagNama = $kasubagNama ?? 'Kepala Sub Bagian Tata Usaha';
    $kasubagNip = $kasubagNip ?? '-';

    $kondisiGeneral = $kondisiGeneral ?? ($pengembalian->kondisi_pengembalian ?: ($pengembalian->items?->first()?->kondisi ?: 'Baik'));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BERITA ACARA SERAH TERIMA BARANG MILIK NEGARA (BMN) — {{ $pengembalian->nomor_pengajuan }} (BERITA ACARA PENGEMBALIAN BARANG MILIK NEGARA)</title>
    <style id="dynamicPageSizeStyle">
        @page {
            size: 210mm 297mm;
            margin: 0;
        }
    </style>
    <style>
        /* ===================================================
           PENGATURAN KERTAS & CETAK DOKUMEN NASKAH DINAS
           Ukuran A4: 210mm x 297mm
           Ukuran F4: 215mm x 330mm (Folio)
           Margin: Kiri 3cm (30mm), Kanan 2cm (20mm), Bawah 2cm (20mm)
           Kop Surat: Full di kertas tanpa spasi (Edge-to-Edge)
           Spasi Antar Baris: 1.15
           Font: Arial
           =================================================== */
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            margin: 0;
            padding: 0;
            background: #4A5568;
            color: #000;
            font-family: Arial, sans-serif !important;
            font-size: 12pt;
            line-height: 1.15;
        }

        p, div, span, table, th, td, h1, h2, h3, h4, h5, h6 {
            font-family: Arial, sans-serif !important;
            line-height: 1.15;
        }

        /* TOOLBAR ATAS (HANYA LAYAR MONITOR) */
        .toolbar-wrapper {
            position: sticky;
            top: 0;
            z-index: 9999;
            background: #112217;
            color: #fff;
            padding: 10px 20px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            font-family: system-ui, -apple-system, sans-serif !important;
            font-size: 12.5px;
        }

        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
        }

        .btn-tool {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 7px;
            font-weight: 600;
            font-size: 11.5px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .btn-tool:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .btn-tool.active {
            background: #2563EB;
            border-color: #3B82F6;
            color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        .btn-primary {
            background: #16A34A;
            border-color: #22C55E;
            color: #fff;
        }

        .btn-primary:hover {
            background: #15803D;
        }

        /* CONTAINER KERTAS CETAK (2 LEMBAR / HALAMAN) */
        .paper-container {
            padding: 24px 0 60px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
        }

        .page {
            background: #fff;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            page-break-after: always;
            break-after: page;
            overflow: hidden;
            padding: 0;
        }

        /* DIMENSI A4 */
        body.size-a4 .page {
            width: 210mm;
            min-height: 297mm;
            height: 297mm;
        }

        /* DIMENSI F4 (FOLIO) */
        body.size-f4 .page {
            width: 215mm;
            min-height: 330mm;
            height: 330mm;
        }

        .page:last-child {
            page-break-after: avoid;
            break-after: avoid;
            margin-bottom: 0;
        }

        /* ===================================================
           HALAMAN 1: KOP SURAT RESMI BBPOM BANJARBARU
           Margin Kop: Kiri ~6.7mm, Kanan ~5.3mm, Atas ~3.6mm
           Garis ganda membentang ~198mm
           Jarak ke Judul Dokumen: 14mm
           =================================================== */
        .kop-header {
            width: 100%;
            margin: 0;
            padding: 0;
            line-height: 0;
            border: 0;
            position: relative;
            background: #fff;
        }

        .kop-header img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0;
            padding: 0;
        }

        /* DEKORASI SUDUT BAWAH KANAN RESMI BADAN POM */
        .kop-footer-decoration {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 100%;
            pointer-events: none;
            z-index: 1;
            line-height: 0;
        }

        .kop-footer-decoration img {
            width: 100%;
            display: block;
            margin: 0;
            padding: 0;
        }

        /* KONTEN HALAMAN 1 (MARGIN KIRI 30MM, KANAN 20MM, BAWAH 20MM) */
        #page1 .page-content {
            padding-left: 30mm;
            padding-right: 20mm;
            padding-bottom: 20mm;
            position: relative;
            z-index: 2;
        }

        /* BLOK JUDUL BERITA ACARA: JARAK 14MM PERSIS NASKAH DINAS */
        .doc-title-block {
            text-align: center;
            margin-top: calc(12px + 1.15em); /* Disamakan dengan jarak di bawah nomor */
            margin-bottom: calc(12px + 1.15em); /* Tambah 1 enter di bawah nomor */
            line-height: 1.15;
        }

        .doc-title-block h1 {
            font-size: 12pt;
            font-weight: normal;
            margin: 0;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .doc-title-block h2 {
            font-size: 12pt;
            font-weight: normal;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .doc-title-block .doc-number {
            font-size: 12pt;
            font-weight: normal;
            margin-top: 3px;
            line-height: 1.2;
        }

        /* PARAGRAF PEMBUKA (SPASI 1.15) */
        .opening-paragraph {
            text-align: justify;
            margin-top: 12px;
            margin-bottom: 12px;
            font-size: 12pt;
            line-height: 1.15;
        }

        /* TABEL IDENTITAS PARA PIHAK (FORMAT 2 PIHAK) */
        .parties-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 0 0;
            font-size: 12pt;
            line-height: 1.15;
        }

        .parties-table td {
            padding: 1px 2px;
            vertical-align: top;
            line-height: 1.15;
        }

        .parties-table td.num {
            width: 28px;
        }

        .parties-table td.lbl {
            width: 110px;
        }

        .parties-table td.sep {
            width: 16px;
            text-align: left;
        }

        .parties-table td.val {
            /* auto */
        }

        .party-clause {
            padding-top: 10px !important;
            padding-bottom: 14px !important;
            padding-left: 2px !important;
            font-size: 12pt;
            line-height: 1.15;
        }

        /* PERNYATAAN SERAH TERIMA & TANGGUNG JAWAB (SPASI 1.15) */
        .statement-p {
            margin: 12px 0;
            font-size: 12pt;
            line-height: 1.15;
            text-align: justify;
        }

        /* TABEL DAFTAR BARANG (DENGAN KET LOKASI) */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0 10px 0;
            font-size: 9.5pt;
            line-height: 1.15;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            vertical-align: middle;
            line-height: 1.15;
        }

        .items-table th {
            text-align: center;
            font-weight: normal;
            background: #fff;
            line-height: 1.2;
        }

        .items-table td.center {
            text-align: center;
        }

        .items-table td.font-mono {
            font-family: Arial, sans-serif !important;
            font-size: 9.5pt;
        }

        /* ===================================================
           HALAMAN 2: MARGIN ATAS 2CM, KIRI 3CM, KANAN 2CM, BAWAH 2CM
           =================================================== */
        #page2 {
            padding: 20mm 20mm 20mm 30mm;
            position: relative;
        }

        .doc-footer-code {
            position: absolute;
            bottom: 12mm;
            left: 30mm;
            font-size: 8pt;
            font-family: Arial, sans-serif !important;
            color: #000;
            line-height: 1;
        }

        .page-2-header {
            text-align: center;
            font-size: 12pt;
            margin-top: 0;
            margin-bottom: 30px;
            line-height: 1.15;
        }

        #page2 .page-content {
            line-height: 1.15;
        }

        .closing-paragraph {
            text-align: justify;
            font-size: 12pt;
            line-height: 1.15;
            margin-bottom: 40px;
        }

        /* BLOK TANDA TANGAN PERSIS FOTO */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12pt;
            line-height: 1.15;
        }

        .signatures-table td {
            width: 50%;
            vertical-align: top;
            text-align: left;
            line-height: 1.15;
            box-sizing: border-box;
        }

        .signatures-table td:first-child {
            padding: 0 10px 0 0;
        }

        .signatures-table td:last-child {
            padding: 0 0 0 25mm;
        }

        .sig-title {
            font-size: 12pt;
            font-weight: normal;
            text-transform: uppercase;
            margin-bottom: 0;
            line-height: 1.15;
        }

                /* KOTAK TEMPAT TTE SRIKANDI / TANDA TANGAN BASAH:
           Rata kiri, tag menjorok 6 spasi, jarak 4 enter ke atas dan 4 enter ke bawah */
        .srikandi-box {
            min-height: 1.15em;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-top: calc(4 * 1.15em);
            margin-bottom: calc(4 * 1.15em);
            padding-left: 6ch;
            box-sizing: border-box;
            transition: min-height 0.2s ease;
        }

        .srikandi-tag {
            font-family: Arial, sans-serif !important;
            font-size: 12pt;
            font-weight: normal;
            color: #000;
        }

        .sig-name {
            font-size: 12pt;
            font-weight: normal; /* Persis di foto: font reguler/tidak underline */
            margin-top: 0;
            line-height: 1.15;
        }

        /* BLOK MENGETAHUI (KEPALA BALAI) */
        .sig-mengetahui-block {
            text-align: left;
            margin: 70px auto 0 auto;
            display: table;
            font-size: 12pt;
            line-height: 1.15;
        }

        .sig-mengetahui-lead {
            text-align: center;
            margin-bottom: 2px;
            line-height: 1.2;
            font-size: 12pt;
        }

        .sig-mengetahui-title {
            margin-bottom: 2px;
            line-height: 1.2;
            font-size: 12pt;
        }

        /* MODE TANDA TANGAN BASAH (TOGGLE):
           Sembunyikan tag ${ttd_pengirim} */
        body.mode-manual .srikandi-tag {
            display: none;
        }

        body.mode-manual .srikandi-box {
            min-height: 1.15em;
        }

        /* ===================================================
           MEDIA CETAK BROWSER & EKSPOR PDF
           =================================================== */
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .toolbar-wrapper {
                display: none !important;
            }

            .paper-container {
                padding: 0 !important;
                gap: 0 !important;
            }

            .page {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body.size-a4 .page {
                width: 210mm !important;
                height: 297mm !important;
            }

            body.size-f4 .page {
                width: 215mm !important;
                height: 330mm !important;
            }

            #page1 .page-content {
                padding-left: 30mm !important;
                padding-right: 20mm !important;
                padding-bottom: 20mm !important;
            }

            .doc-title-block {
                margin-top: calc(12px + 1.15em) !important;
                margin-bottom: calc(12px + 1.15em) !important;
            }

            #page2 {
                padding: 20mm 20mm 20mm 30mm !important;
                position: relative !important;
                page-break-before: always !important;
                break-before: page !important;
            }

            .doc-footer-code {
                position: absolute !important;
                bottom: 12mm !important;
                left: 30mm !important;
            }
        }
    </style>
</head>
<body id="docBody" class="size-a4">

    {{-- TOOLBAR ATAS --}}
    <div class="toolbar-wrapper">
        <div class="toolbar-group">
            <a href="{{ Route::has('pengembalians.show') ? route('pengembalians.show', $pengembalian) : route('pengembalians.index') }}" class="btn-tool" title="Kembali ke Detail">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali</span>
            </a>

            <span style="color: rgba(255,255,255,0.3);">|</span>

            {{-- PILIHAN UKURAN KERTAS --}}
            <span style="font-size: 11px; text-transform: uppercase; color: #94A3B8; font-weight: bold;">Ukuran:</span>
            <button type="button" id="btnA4" onclick="setDocSize('a4')" class="btn-tool active">A4 (210 &times; 297 mm)</button>
            <button type="button" id="btnF4" onclick="setDocSize('f4')" class="btn-tool">F4 (215 &times; 330 mm)</button>
        </div>

        <div class="toolbar-group">
            {{-- TOGGLE SRIKANDI / MANUAL --}}
            <span style="font-size: 11px; text-transform: uppercase; color: #94A3B8; font-weight: bold;">Format TTE:</span>
            <button
                type="button"
                id="btnModeSrikandi"
                onclick="setTteMode('srikandi')"
                class="btn-tool active"
                title="Srikandi TTE Paperless: menyertakan tag ${ttd_pengirim} untuk penempelan barcode BSrE"
            >
                Srikandi TTE (${ttd})
            </button>
            <button
                type="button"
                id="btnModeManual"
                onclick="setTteMode('manual')"
                class="btn-tool"
                title="Tanda tangan basah fisik"
            >
                Tanda Tangan Basah
            </button>

            <button type="button" onclick="window.print()" class="btn-tool btn-primary" style="padding: 6px 16px;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak / Unduh PDF</span>
            </button>
        </div>
    </div>

    {{-- CONTAINER KERTAS CETAK (2 LEMBAR) --}}
    <div class="paper-container">

        {{-- =========================================================
             HALAMAN 1: KOP SURAT FULL EDGE-TO-EDGE + MARGIN 3 2
             ========================================================= --}}
        <div class="page" id="page1">

            {{-- KOP SURAT MASTER RESMI BBPOM (ULTRA HD 300 DPI) --}}
            <div class="kop-header kop-header-raster" id="kopHeaderRaster">
                <img
                    src="{{ !empty($kopMasterBase64) ? 'data:image/png;base64,' . $kopMasterBase64 : asset('images/kop-header-white.png') }}"
                    alt="Kop Surat Balai Besar Pengawas Obat dan Makanan di Banjarbaru"
                />
            </div>

            {{-- KONTEN DOKUMEN: MARGIN KIRI 3CM, KANAN 2CM, BAWAH 2CM --}}
            <div class="page-content">

                {{-- JUDUL BERITA ACARA --}}
                <div class="doc-title-block">
                    <h1>BERITA ACARA SERAH TERIMA BARANG MILIK NEGARA (BMN)</h1>
                    <h2>BALAI BESAR PENGAWAS OBAT DAN MAKANAN DI BANJARBARU</h2>
                    <div class="doc-number">
                        NOMOR : {{ $pengembalian->nomor_pengajuan }}
                    </div>
                </div>

                {{-- PARAGRAF PEMBUKA (TERBILANG LENGKAP) --}}
                <p class="opening-paragraph">
                    {{ \App\Support\NaskahDinasHelper::tanggalTerbilang($pengembalian->tanggal) }}
                </p>

                {{-- DATA IDENTITAS PARA PIHAK (FORMAT 2 PIHAK) --}}
                <table class="parties-table">
                    {{-- PIHAK PERTAMA (YANG MENGEMBALIKAN) --}}
                    <tr>
                        <td class="num">I.</td>
                        <td class="lbl">Nama</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $pihakPertama['nama'] }}</td>
                    </tr>
                    <tr>
                        <td class="num"></td>
                        <td class="lbl">NIP</td>
                        <td class="sep">:</td>
                        <td class="val">{{ \App\Support\NaskahDinasHelper::formatNip($pihakPertama['nip']) }}</td>
                    </tr>
                    <tr>
                        <td class="num"></td>
                        <td class="lbl">Jabatan</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $pihakPertama['jabatan'] }}</td>
                    </tr>
                    <tr>
                        <td class="num"></td>
                        <td class="lbl">Unit Kerja</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $pihakPertama['unit_kerja'] }}</td>
                    </tr>
                    <tr>
                        <td class="num"></td>
                        <td colspan="3" class="party-clause">
                            Selanjutnya disebut PIHAK PERTAMA
                        </td>
                    </tr>

                    {{-- PIHAK KEDUA (PENGELOLA BMN YANG MENERIMA KEMBALI) --}}
                    <tr>
                        <td class="num">II.</td>
                        <td class="lbl">Nama</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $pihakKedua['nama'] }}</td>
                    </tr>
                    <tr>
                        <td class="num"></td>
                        <td class="lbl">NIP</td>
                        <td class="sep">:</td>
                        <td class="val">{{ \App\Support\NaskahDinasHelper::formatNip($pihakKedua['nip']) }}</td>
                    </tr>
                    <tr>
                        <td class="num"></td>
                        <td class="lbl">Jabatan</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $pihakKedua['jabatan'] }}</td>
                    </tr>
                    <tr>
                        <td class="num"></td>
                        <td class="lbl">Unit Kerja</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $pihakKedua['unit_kerja'] }}</td>
                    </tr>
                    <tr>
                        <td class="num"></td>
                        <td colspan="3" class="party-clause">
                            Selanjutnya disebut PIHAK KEDUA
                        </td>
                    </tr>
                </table>

                {{-- PERNYATAAN SERAH TERIMA PENGEMBALIAN --}}
                @php
                    $kondisiText = $kondisiGeneral ? ($kondisiGeneral === 'Baik' ? 'Baik/ Rusak' : $kondisiGeneral) : 'Baik/ Rusak';
                @endphp
                <p class="statement-p">
                    PIHAK PERTAMA menyerahkan dan PIHAK KEDUA menerima penyerahan Barang Milik Negara (BMN) dengan kondisi {{ $kondisiText }} Spesifikasi sebagai berikut :
                </p>

                {{-- KALIMAT TANGGUNG JAWAB (DI ATAS TABEL) --}}
                <p class="statement-p">
                    PIHAK KEDUA bertanggung jawab segala sesuatu atas barang-barang tersebut.
                </p>
                <p class="statement-p">
                    Apabila terjadi kehilangan menjadi tanggung jawab petugas yang diserahkan barang.
                </p>

                {{-- TABEL BARANG BMN YANG DIKEMBALIKAN (7 KOLOM SESUAI NASKAH RESMI) --}}
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 27%;">Nama Barang</th>
                            <th style="width: 17%;">Kode Barang</th>
                            <th style="width: 8%;">NUP</th>
                            <th style="width: 20%;">Merk/Type</th>
                            <th style="width: 10%;">Jumlah (unit)</th>
                            <th style="width: 13%;">Tahun Perolehan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($pengembalian->items && $pengembalian->items->isNotEmpty())
                            @foreach($pengembalian->items as $idx => $it)
                            <tr>
                                <td class="center">{{ $idx + 1 }}</td>
                                <td>{{ $it->nama_barang_snapshot ?: ($it->bmn?->nama_barang ?: '—') }}</td>
                                <td class="center font-mono">{{ $it->kode_barang ?: ($it->bmn?->kode_barang ?: '—') }}</td>
                                <td class="center font-mono">{{ $it->nup ?: ($it->bmn?->nup ?: '—') }}</td>
                                <td>{{ $it->bmn?->merek ?: ($it->bmn?->spesifikasi ?: '—') }}</td>
                                <td class="center">{{ $it->jumlah ?: 1 }}</td>
                                <td class="center">{{ $it->bmn?->tahun_perolehan ?: '—' }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="center">1</td>
                                <td>{{ $pengembalian->nama_barang ?: ($pengembalian->bmn?->nama_barang ?: 'Barang Milik Negara (BMN)') }}</td>
                                <td class="center font-mono">{{ $pengembalian->bmn?->kode_barang ?: '—' }}</td>
                                <td class="center font-mono">{{ $pengembalian->bmn?->nup ?: '—' }}</td>
                                <td>{{ $pengembalian->bmn?->merek ?: ($pengembalian->bmn?->spesifikasi ?: '—') }}</td>
                                <td class="center">1</td>
                                <td class="center">{{ $pengembalian->bmn?->tahun_perolehan ?: '—' }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- DEKORASI SUDUT BAWAH KANAN RESMI BADAN POM --}}
            <div class="kop-footer-decoration">
                <img src="{{ !empty($kopFooterBase64) ? 'data:image/png;base64,' . $kopFooterBase64 : asset('images/kop-footer.png') }}" alt="Dekorasi BBPOM" />
            </div>
        </div>

        {{-- =========================================================
             HALAMAN 2: MARGIN 3 2, PENUTUP & TANDA TANGAN (PERSIS FOTO)
             ========================================================= --}}
        <div class="page" id="page2">
            <div class="page-2-header">-2-</div>

            <div class="page-content">
                <p class="closing-paragraph">
                    Demikian Berita Acara ini dibuat dengan benar untuk dipergunakan sebagaimana mestinya.
                </p>

                {{-- TANDA TANGAN PIHAK KEDUA & PIHAK PERTAMA --}}
                <table class="signatures-table">
                    <tr>
                        {{-- KIRI: PIHAK KEDUA --}}
                        <td>
                            <div class="sig-title">PIHAK KEDUA,</div>
                            <div class="srikandi-box" id="sigBoxPihak2">
                                <span class="srikandi-tag">${ttd_pengirim2}</span>
                            </div>
                            <div class="sig-name">
                                {{ $pihakKedua['nama'] }}
                            </div>
                        </td>

                        {{-- KANAN: PIHAK PERTAMA --}}
                        <td>
                            <div class="sig-title">PIHAK PERTAMA,</div>
                            <div class="srikandi-box" id="sigBoxPihak1">
                                <span class="srikandi-tag">${ttd_pengirim1}</span>
                            </div>
                            <div class="sig-name">
                                {{ $pihakPertama['nama'] }}
                            </div>
                        </td>
                    </tr>
                </table>

                {{-- TANDA TANGAN MENGETAHUI (KEPALA BALAI BESAR POM DI BANJARBARU) --}}
                <div class="sig-mengetahui-block">
                    <div class="sig-mengetahui-lead">Mengetahui,</div>
                    <div class="sig-mengetahui-title">Kepala Balai Besar POM di Banjarbaru</div>
                    <div class="sig-mengetahui-title">Selaku Kuasa Pengguna Barang</div>

                    <div class="srikandi-box" id="sigBoxKabalai">
                        <span class="srikandi-tag">${ttd_pengirim3}</span>
                    </div>

                    <div class="sig-name">
                        {{ $kabalaiNama }}
                    </div>
                </div>

                {{-- KODE DOKUMEN NASKAH DINAS --}}
                <div class="doc-footer-code">
                    {{ \App\Support\NaskahDinasHelper::KODE_FORM_BAST }}
                </div>
            </div>
        </div>

    </div>

    <script>
        function setDocSize(sz) {
            const body = document.getElementById('docBody');
            const btnA4 = document.getElementById('btnA4');
            const btnF4 = document.getElementById('btnF4');
            let styleTag = document.getElementById('dynamicPageSizeStyle');
            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = 'dynamicPageSizeStyle';
                document.head.appendChild(styleTag);
            }

            if (sz === 'f4') {
                body.classList.remove('size-a4');
                body.classList.add('size-f4');
                btnF4.classList.add('active');
                btnA4.classList.remove('active');
                styleTag.textContent = '@page { size: 215mm 330mm; margin: 0; }';
            } else {
                body.classList.add('size-a4');
                body.classList.remove('size-f4');
                btnA4.classList.add('active');
                btnF4.classList.remove('active');
                styleTag.textContent = '@page { size: 210mm 297mm; margin: 0; }';
            }
        }

        function setTteMode(mode) {
            const body = document.getElementById('docBody');
            const btnSrikandi = document.getElementById('btnModeSrikandi');
            const btnManual = document.getElementById('btnModeManual');

            if (mode === 'manual') {
                body.classList.add('mode-manual');
                btnManual.classList.add('active');
                btnSrikandi.classList.remove('active');
            } else {
                body.classList.remove('mode-manual');
                btnSrikandi.classList.add('active');
                btnManual.classList.remove('active');
            }
        }
    </script>
</body>
</html>