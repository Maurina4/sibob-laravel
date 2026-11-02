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

    $penyetujuPegawai = $pengajuan->penyetujuUser ? \App\Models\Pegawai::where('nama', $pengajuan->penyetujuUser->name)->first() : null;

    $pihakPertama = [
        'nama' => $pengajuan->pemohonUser?->name ?: '....................................................',
        'nip' => $pengajuan->pegawai?->nip ?: '-',
        'jabatan' => $pengajuan->pegawai?->jabatan ?: 'Pegawai Pemakai BMN',
        'unit_kerja' => $pengajuan->bidang ?: 'Balai Besar POM di Banjarbaru',
    ];

    $pihakKedua = [
        'nama' => $pengajuan->penyetujuUser?->name ?: 'Pengelola BMN',
        'nip' => $penyetujuPegawai?->nip ?: '198001012005011001',
        'jabatan' => 'Operator / Pengelola BMN',
        'unit_kerja' => 'Subbag Tata Usaha',
    ];

    $kabalaiNama = 'Ary Yustantiningsih,S.Si.,Apt.';
    $isRusakBerat = ($pengajuan->alasan === 'rusak_berat');
    $titleDoc = $isRusakBerat ? 'BERITA ACARA KERUSAKAN BERAT BARANG MILIK NEGARA' : 'BERITA ACARA PENGEMBALIAN BARANG MILIK NEGARA';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titleDoc }} — {{ $pengajuan->nomor_ba ?: $pengajuan->nomor_pengajuan }}</title>
    <style id="dynamicPageSizeStyle">
        @page {
            size: 210mm 297mm;
            margin: 0;
        }
    </style>
    <style>
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

        body.size-a4 .page {
            width: 210mm;
            min-height: 297mm;
            height: 297mm;
        }

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

        #page1 .page-content {
            padding-left: 30mm;
            padding-right: 20mm;
            padding-bottom: 20mm;
            position: relative;
            z-index: 2;
        }

        .doc-title-block {
            text-align: left;
            margin-top: 14mm;
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

        .opening-paragraph {
            text-align: left;
            margin-top: 12px;
            margin-bottom: 12px;
            font-size: 11pt;
            line-height: 1.15;
        }

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
            text-align: left;
        }

        .party-clause {
            padding-top: 8px !important;
            padding-bottom: 12px !important;
            padding-left: 28px !important;
            font-size: 11pt;
            line-height: 1.15;
        }

        .statement-p {
            margin: 12px 0;
            font-size: 11pt;
            line-height: 1.15;
            text-align: left;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 10px 0;
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
            text-align: left;
            font-weight: bold;
            background: #FAFAFA;
            line-height: 1.2;
        }

        .items-table td.center {
            text-align: left;
        }

        .items-table td.font-mono {
            font-family: Arial, monospace !important;
            font-size: 9.5pt;
        }

        #page2 {
            padding: 20mm 20mm 20mm 30mm;
        }

        .page-2-header {
            text-align: center;
            font-size: 11pt;
            margin-top: 0;
            margin-bottom: 24px;
            line-height: 1.15;
        }

        #page2 .page-content {
            line-height: 1.15;
        }

        .closing-paragraph {
            text-align: left;
            font-size: 11pt;
            line-height: 1.15;
            margin-bottom: 30px;
        }

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
            text-align: left;
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

                /* KOTAK TEMPAT TTE SRIKANDI / TANDA TANGAN BASAH:
           Rata kiri, tag menjorok 6 spasi, jarak 3 enter ke atas dan 3 enter ke bawah */
        .srikandi-box {
            min-height: 1.15em;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-top: calc(3 * 1.15em);
            margin-bottom: calc(3 * 1.15em);
            padding-left: 6ch;
            box-sizing: border-box;
            transition: min-height 0.2s ease;
        }

        .srikandi-tag {
            font-family: Arial, sans-serif !important;
            font-size: 11pt;
            font-weight: normal;
            color: #000;
        }

        .sig-name {
            font-size: 11pt;
            font-weight: normal;
            margin-top: 0;
            line-height: 1.15;
        }

        .sig-mengetahui-block {
            text-align: left;
            margin: 50px auto 0 auto;
            display: table;
            font-size: 11pt;
            line-height: 1.15;
        }

        .sig-mengetahui-lead {
            text-align: center;
            margin-bottom: 2px;
            line-height: 1.2;
            font-size: 11pt;
        }

        .sig-mengetahui-title {
            margin-bottom: 2px;
            line-height: 1.2;
            font-size: 11pt;
        }

        body.mode-manual .srikandi-tag {
            display: none;
        }

        body.mode-manual .srikandi-box {
            min-height: 1.15em;
        }

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
                page-break-before: always !important;
                break-before: page !important;
            }
        }
    </style>
</head>
<body id="docBody" class="size-a4">

    {{-- TOOLBAR ATAS --}}
    <div class="toolbar-wrapper">
        <div class="toolbar-group">
            <button type="button" onclick="window.history.back()" class="btn-tool" title="Kembali">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali</span>
            </button>

            <span style="color: rgba(255,255,255,0.3);">|</span>

            <span style="font-size: 11px; text-transform: uppercase; color: #94A3B8; font-weight: bold;">Ukuran:</span>
            <button type="button" id="btnA4" onclick="setDocSize('a4')" class="btn-tool active">A4</button>
            <button type="button" id="btnF4" onclick="setDocSize('f4')" class="btn-tool">F4 (Folio)</button>
        </div>

        <div class="toolbar-group">
            <span style="font-size: 11px; text-transform: uppercase; color: #94A3B8; font-weight: bold;">Tanda Tangan:</span>
            <button
                type="button"
                id="btnModeSrikandi"
                onclick="setTteMode('srikandi')"
                class="btn-tool active"
                title="Srikandi TTE Paperless"
            >
                Srikandi TTE
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

    {{-- CONTAINER LEMBAR CETAK --}}
    <div class="paper-container">

        {{-- HALAMAN 1 --}}
        <div class="page" id="page1">
            <div class="kop-header" id="kopHeaderRaster">
                <img
                    src="{{ !empty($kopMasterBase64) ? 'data:image/png;base64,' . $kopMasterBase64 : asset('images/kop-header-white.png') }}"
                    alt="Kop Surat Balai Besar Pengawas Obat dan Makanan di Banjarbaru"
                />
            </div>

            <div class="page-content">
                {{-- JUDUL BERITA ACARA --}}
                <div class="doc-title-block">
                    <h1>{{ $titleDoc }}</h1>
                    <h2>BALAI BESAR PENGAWAS OBAT DAN MAKANAN DI BANJARBARU</h2>
                    <div class="doc-number">
                        NOMOR : {{ $pengajuan->nomor_ba ?: $pengajuan->nomor_pengajuan }}
                    </div>
                </div>

                {{-- PARAGRAF PEMBUKA --}}
                <p class="opening-paragraph">
                    Pada hari ini, tanggal {{ $pengajuan->tanggal ? \Carbon\Carbon::parse($pengajuan->tanggal)->translatedFormat('d F Y') : date('d F Y') }}, kami yang bertanda tangan di bawah ini:
                </p>

                {{-- IDENTITAS PARA PIHAK --}}
                <table class="parties-table">
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

                {{-- STATEMENT SERAH TERIMA --}}
                <p class="statement-p">
                    @if ($isRusakBerat)
                        PIHAK PERTAMA melaporkan dan menyerahkan kepada PIHAK KEDUA dan PIHAK KEDUA menerima penyerahan Barang Milik Negara (BMN) dari bidang / unit kerja <strong>{{ $pengajuan->bidang }}</strong> dalam keadaan <strong>Rusak Berat</strong> untuk diproses tindak lanjut usulan penghapusan BMN dengan rincian sebagai berikut:
                    @else
                        PIHAK PERTAMA menyerahkan kembali kepada PIHAK KEDUA dan PIHAK KEDUA menerima penyerahan kembali Barang Milik Negara (BMN) dari bidang / unit kerja <strong>{{ $pengajuan->bidang }}</strong> dengan kondisi <strong>Baik</strong> untuk disimpan kembali di Gudang BMN dengan rincian sebagai berikut:
                    @endif
                </p>

                {{-- TABEL BARANG BMN --}}
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 28%;">Nama Barang</th>
                            <th style="width: 17%;">Kode Barang</th>
                            <th style="width: 10%;">NUP</th>
                            <th style="width: 15%;">Kondisi</th>
                            <th style="width: 25%;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengajuan->items as $idx => $it)
                        <tr>
                            <td class="center">{{ $idx + 1 }}</td>
                            <td>{{ $it->nama_barang }}</td>
                            <td class="center font-mono">{{ $it->kode_barang }}</td>
                            <td class="center font-mono">{{ $it->nup ?: '-' }}</td>
                            <td class="center">{{ $it->kondisi ?: ($isRusakBerat ? 'Rusak Berat' : 'Baik') }}</td>
                            <td>
                                @if($it->deskripsi_kerusakan)
                                    <div>{{ $it->deskripsi_kerusakan }}</div>
                                    @if($it->laporanKerusakan)
                                        <div style="font-size: 8pt; font-family: monospace; color: #444; margin-top: 2px;">
                                            LK: {{ $it->laporanKerusakan->nomor_laporan }}
                                        </div>
                                    @endif
                                @else
                                    {{ $it->keterangan ?: 'Selesai pemakaian' }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="kop-footer-decoration">
                <img src="{{ !empty($kopFooterBase64) ? 'data:image/png;base64,' . $kopFooterBase64 : asset('images/kop-footer.png') }}" alt="Dekorasi BBPOM" />
            </div>
        </div>

        {{-- HALAMAN 2 --}}
        <div class="page" id="page2">
            <div class="page-2-header">-2-</div>

            <div class="page-content">
                <p class="closing-paragraph">
                    @if ($isRusakBerat)
                        Demikian Berita Acara Kerusakan Berat BMN ini dibuat dengan sebenarnya dan penuh tanggung jawab untuk dipergunakan sebagaimana mestinya dalam rangka pemutakhiran data inventaris aset dan tindak lanjut proses pengusulan penghapusan BMN di lingkungan Balai Besar POM di Banjarbaru.
                    @else
                        Demikian Berita Acara Pengembalian BMN ini dibuat dengan sebenarnya dan penuh tanggung jawab untuk dipergunakan sebagaimana mestinya dalam rangka penertiban, pemeliharaan, serta pemutakhiran data aset BMN di lingkungan Balai Besar POM di Banjarbaru.
                    @endif
                </p>

                <table class="signatures-table">
                    <tr>
                        <td>
                            <div class="sig-title">PIHAK KEDUA</div>
                            <div class="srikandi-box" id="sigBoxPihak2">
                                <span class="srikandi-tag">${ttd_pengirim2}</span>
                            </div>
                            <div class="sig-name">
                                {{ $pihakKedua['nama'] }}
                            </div>
                        </td>

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
