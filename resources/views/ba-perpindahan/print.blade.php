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
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Perpindahan BMN — {{ $baPerpindahan->nomor_ba ?: 'BA-' . $baPerpindahan->id }}</title>
    <style>
        /* ===================================================
           PENGATURAN KERTAS & CETAK DOKUMEN NASKAH DINAS
           Ukuran A4: 210mm x 297mm
           Ukuran F4: 215mm x 330mm (Folio)
           Margin: Kiri 3cm (30mm), Kanan 2cm (20mm), Bawah 2cm (20mm)
           Kop Surat: Full di kertas tanpa spasi (Edge-to-Edge)
           Spasi Antar Baris: 1.15 (Semua spasi kebawah 1.115/1.15)
           Font: Arial
           =================================================== */
        @page {
            size: {{ $size === 'f4' ? '215mm 330mm' : '210mm 297mm' }};
            margin: 0;
        }

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
            font-size: 11pt;
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

        /* CONTAINER KERTAS CETAK */
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
            padding: 0; /* Nol agar kop full di kertas tanpa spasi */
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
           Sesuai standar naskah dinas resmi di foto (media_1792541429479_d1d605f5.png):
           Margin Kop: Kiri ~6.7mm, Kanan ~5.3mm, Atas ~3.6mm
           Garis ganda membentang ~198mm (spans out of 210mm A4)
           Jarak ke Judul Dokumen: 14mm
           =================================================== */
        /* Kop Gambar Master Resmi (Ultra HD 300 DPI) */
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

        body.hide-kop #page1 .page-content {
            padding-top: 25mm !important;
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

        /* KONTEN HALAMAN 1 DENGAN MARGIN 3 2:
           Kiri: 30mm (3cm)
           Kanan: 20mm (2cm)
           Bawah: 20mm (2cm) */
        #page1 .page-content {
            padding-left: 30mm;
            padding-right: 20mm;
            padding-bottom: 20mm;
            position: relative;
            z-index: 2;
        }

        /* KE BERITA ACARA: JARAK 14MM PERSIS FOTO DOKUMEN ASLI */
        .doc-title-block {
            text-align: center;
            margin-top: 14mm; /* Jarak 14mm dari garis ganda kop surat ke judul sesuai foto naskah dinas resmi */
            margin-bottom: 12px;
            line-height: 1.15;
        }

        .doc-title-block h1 {
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .doc-title-block h2 {
            font-size: 11pt;
            font-weight: bold;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .doc-title-block .doc-number {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 3px;
            line-height: 1.2;
        }

        /* PARAGRAF PEMBUKA (SPASI KEBANWAH 1.15) */
        .opening-paragraph {
            text-align: justify;
            margin-top: 12px;
            margin-bottom: 12px;
            font-size: 11pt;
            line-height: 1.15;
        }

        /* TABEL IDENTITAS PARA PIHAK */
        .parties-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 0 0;
            font-size: 11pt;
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
            text-align: center;
        }

        .parties-table td.val {
            /* auto */
        }

        .party-clause {
            padding-top: 10px !important;
            padding-bottom: 14px !important;
            padding-left: 28px !important; /* Indentasi sejajar label Nama sesuai foto */
            font-size: 11pt;
            line-height: 1.15;
        }

        /* PERNYATAAN SERAH TERIMA & TANGGUNG JAWAB (SPASI 1.15) */
        .statement-p {
            margin: 12px 0;
            font-size: 11pt;
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
            font-weight: bold;
            background: #FAFAFA;
            line-height: 1.2;
        }

        .items-table td.center {
            text-align: center;
        }

        .items-table td.font-mono {
            font-family: Arial, monospace !important;
            font-size: 9.5pt;
        }

        .location-tag {
            font-size: 8.5pt;
            line-height: 1.2;
        }

        .location-tag span.badge-from {
            font-weight: bold;
            color: #111;
        }

        .location-tag span.badge-to {
            font-weight: bold;
            color: #111;
        }

        /* ===================================================
           HALAMAN 2: MARGIN ATAS 2CM, KIRI 3CM, KANAN 2CM, BAWAH 2CM
           =================================================== */
        #page2 {
            padding: 20mm 20mm 20mm 30mm; /* Atas 2cm, Kanan 2cm, Bawah 2cm, Kiri 3cm */
        }

        .page-2-header {
            text-align: center;
            font-size: 11pt;
            margin-top: 0;
            margin-bottom: 30px; /* Jarak dari -2- ke kalimat penutup */
            line-height: 1.15;
        }

        #page2 .page-content {
            line-height: 1.15;
        }

        .closing-paragraph {
            text-align: justify;
            font-size: 11pt;
            line-height: 1.15;
            margin-bottom: 40px; /* Jarak dari kalimat penutup ke judul TTD */
        }

        /* ===================================================
           BLOK TANDA TANGAN (PERSIS FOTO: SPASI KEBANWAH KAYA SRIKANDI)
           =================================================== */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11pt;
            line-height: 1.15;
        }

        .signatures-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 0 10px;
            line-height: 1.15;
        }

        .sig-title {
            font-size: 11pt;
            font-weight: normal;
            text-transform: uppercase;
            margin-bottom: 0;
            line-height: 1.15;
        }

        /* KOTAK TEMPAT TTE SRIKANDI / TANDA TANGAN BASAH (SPASI LAPANG): */
        .srikandi-box {
            min-height: 105px;
            height: 105px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 8px 0;
            transition: min-height 0.2s ease, height 0.2s ease;
        }

        .srikandi-tag {
            font-family: Arial, sans-serif !important;
            font-size: 11pt;
            font-weight: normal;
            color: #000;
        }

        .sig-name {
            font-size: 11pt;
            font-weight: normal; /* Persis di foto: font reguler/tidak underline */
            margin-top: 0;
            line-height: 1.15;
        }

        /* BLOK MENGETAHUI (KEPALA BALAI) */
        .sig-mengetahui-block {
            text-align: center;
            margin-top: 70px; /* Jarak vertikal lapang sesuai instruksi */
            font-size: 11pt;
            line-height: 1.15;
        }

        .sig-mengetahui-title {
            margin-bottom: 2px;
            line-height: 1.2;
            font-size: 11pt;
        }

        /* MODE TANDA TANGAN BASAH (TOGGLE):
           Sembunyikan tag ${ttd_pengirim} */
        body.mode-manual .srikandi-tag {
            display: none;
        }

        body.mode-manual .srikandi-box {
            min-height: 105px;
            height: 105px;
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
                margin-top: 14mm !important;
            }

            #page2 {
                padding: 20mm 20mm 20mm 30mm !important;
            }
        }
    </style>
</head>
<body class="size-{{ $size }}" id="docBody">

    {{-- TOOLBAR KONTROL ATAS --}}
    <div class="toolbar-wrapper no-print">
        <div class="toolbar-group">
            <a href="{{ route('ba-perpindahan.show', $baPerpindahan) }}" class="btn-tool" title="Kembali ke Detail Pengajuan">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali</span>
            </a>

            <span style="color: rgba(255,255,255,0.3);">|</span>

            {{-- PILIHAN UKURAN KERTAS --}}
            <span style="font-size: 11px; text-transform: uppercase; color: #94A3B8; font-weight: bold;">Ukuran:</span>
            <button
                type="button"
                id="btnSizeA4"
                onclick="setPageSize('a4')"
                class="btn-tool {{ $size === 'a4' ? 'active' : '' }}"
            >
                A4 (210 &times; 297 mm)
            </button>
            <button
                type="button"
                id="btnSizeF4"
                onclick="setPageSize('f4')"
                class="btn-tool {{ $size === 'f4' ? 'active' : '' }}"
            >
                F4 (215 &times; 330 mm)
            </button>
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

            {{-- TOMBOL CETAK / SIMPAN PDF --}}
            <button
                type="button"
                onclick="window.print()"
                class="btn-tool btn-primary"
                style="padding: 6px 16px; font-size: 12.5px;"
            >
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    {{-- CONTAINER KERTAS CETAK --}}
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

                {{-- KE BERITA ACARA BARU SPASI 1.5 --}}
                <div class="doc-title-block">
                    <h1>BERITA ACARA SERAH TERIMA BARANG MILIK NEGARA (BMN)</h1>
                    <h2>BALAI BESAR PENGAWAS OBAT DAN MAKANAN DI BANJARBARU</h2>
                    <div class="doc-number">
                        NOMOR : {{ $baPerpindahan->nomor_ba ?: 'PL.03.03.43B.08.26.944' }}
                    </div>
                </div>

                {{-- TANGGAL TERBILANG (SPASI 1.15) --}}
                <p class="opening-paragraph">
                    {{ $baPerpindahan->tanggal_terbilang }}
                </p>

                {{-- DATA IDENTITAS PARA PIHAK --}}
                <table class="parties-table">
                    {{-- PIHAK PERTAMA --}}
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
                        <td class="val">{{ $pihakPertama['nip'] }}</td>
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
                            Selanjutnya disebut <strong>PIHAK PERTAMA</strong>
                        </td>
                    </tr>

                    {{-- PIHAK KEDUA --}}
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
                        <td class="val">{{ $pihakKedua['nip'] }}</td>
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
                            Selanjutnya disebut <strong>PIHAK KEDUA</strong>
                        </td>
                    </tr>
                </table>

                @if(!empty($baPerpindahan->pengaju_nama))
                    <div style="font-size: 8.5pt; color: #444; margin: 2px 0 6px 28px;">
                        <em>(Diajukan Oleh: {{ $baPerpindahan->pengajuPegawai ? $baPerpindahan->pengajuPegawai->nama : $baPerpindahan->pengaju_nama }})</em>
                    </div>
                @endif

                {{-- PERNYATAAN SERAH TERIMA --}}
                <p class="statement-p">
                    PIHAK PERTAMA menyerahkan dan PIHAK KEDUA menerima penyerahan Barang Milik Negara (BMN) dengan kondisi <strong>{{ $kondisiGeneral }}</strong> Spesifikasi sebagai berikut :
                </p>

                <p class="statement-p">
                    PIHAK KEDUA bertanggung jawab segala sesuatu atas barang-barang tersebut.
                </p>

                <p class="statement-p">
                    Apabila terjadi kehilangan menjadi tanggung jawab petugas yang diserahkan barang.
                </p>

                {{-- TABEL BARANG DENGAN KOLOM KET. LOKASI --}}
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 20%;">Nama Barang</th>
                            <th style="width: 14%;">Kode Barang</th>
                            <th style="width: 7%;">NUP</th>
                            <th style="width: 15%;">Merk/Type</th>
                            <th style="width: 8%;">Jumlah (unit)</th>
                            <th style="width: 11%;">Tahun Perolehan</th>
                            <th style="width: 20%;">Ket. Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $idx => $it)
                            <tr>
                                <td class="center">{{ $idx + 1 }}</td>
                                <td>{{ $it->nama_barang }}</td>
                                <td class="center font-mono">{{ $it->kode_barang }}</td>
                                <td class="center font-mono">{{ $it->nup }}</td>
                                <td>{{ $it->merk }}</td>
                                <td class="center">{{ $it->jumlah }}</td>
                                <td class="center">{{ $it->tahun_perolehan }}</td>
                                <td>
                                    <div class="location-tag">
                                        <div><span class="badge-from">Dari:</span> {{ $it->lokasi_asal ?: '-' }}</div>
                                        <div><span class="badge-to">Ke:</span> {{ $it->lokasi_tujuan ?: '-' }}</div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="center">Data barang tidak ditemukan.</td>
                            </tr>
                        @endforelse
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

                {{-- TANDA TANGAN PIHAK PERTAMA & PIHAK KEDUA --}}
                <table class="signatures-table">
                    <tr>
                        {{-- KIRI: PIHAK KEDUA --}}
                        <td>
                            <div class="sig-title">PIHAK KEDUA</div>
                            <div class="srikandi-box" id="sigBoxPihak2">
                                <span class="srikandi-tag">${ttd_pengirim2}</span>
                            </div>
                            <div class="sig-name">
                                {{ $pihakKedua['nama'] }}
                            </div>
                        </td>

                        {{-- KANAN: PIHAK PERTAMA --}}
                        <td>
                            <div class="sig-title">PIHAK PERTAMA</div>
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
                    <div class="sig-mengetahui-title">Mengetahui,</div>
                    <div class="sig-mengetahui-title">Kepala Balai Besar POM di Banjarbaru</div>
                    <div class="sig-mengetahui-title">Selaku Kuasa Pengguna Barang</div>

                    <div class="srikandi-box" id="sigBoxKabalai" style="margin: 8px auto; justify-content: center;">
                        <span class="srikandi-tag">${ttd_pengirim3}</span>
                    </div>

                    <div class="sig-name">
                        {{ $kabalaiNama }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- SCRIPT INTERAKSI UKURAN, FORMAT, DAN SPASI TTD --}}
    <script>
        function setPageSize(size) {
            const body = document.getElementById('docBody');
            const btnA4 = document.getElementById('btnSizeA4');
            const btnF4 = document.getElementById('btnSizeF4');
            let styleTag = document.getElementById('dynamicPageSizeStyle');
            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = 'dynamicPageSizeStyle';
                document.head.appendChild(styleTag);
            }

            if (size === 'f4') {
                body.classList.remove('size-a4');
                body.classList.add('size-f4');
                btnF4.classList.add('active');
                btnA4.classList.remove('active');
                styleTag.textContent = '@page { size: 215mm 330mm; margin: 0; }';
            } else {
                body.classList.remove('size-f4');
                body.classList.add('size-a4');
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

        // Buka dialog cetak otomatis jika parameter URL auto_print=1
        if (new URLSearchParams(window.location.search).get('auto_print') === '1') {
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => window.print(), 400);
            });
        }
    </script>
</body>
</html>
