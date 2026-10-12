<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Berita Acara Pengembalian — {{ $pengembalian->nomor_pengajuan }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
            font-size: 12pt;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h3 {
            margin: 2px 0;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 10pt;
        }
        .title {
            text-align: center;
            margin: 25px 0 20px;
        }
        .title h4 {
            margin: 0;
            font-size: 13pt;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .title span {
            font-size: 11pt;
        }
        .content-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .content-table td {
            padding: 6px 4px;
            vertical-align: top;
        }
        .content-table td.label {
            width: 32%;
        }
        .content-table td.colon {
            width: 3%;
        }
        .signature-table {
            width: 100%;
            margin-top: 50px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        .signature-space {
            height: 70px;
        }
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #163828;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-family: sans-serif;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <a href="javascript:window.print()" class="no-print">🖨️ Cetak / Unduh PDF</a>

    {{-- KOP SURAT BBPOM --}}
    <div class="header">
        <h2>BADAN PENGAWAS OBAT DAN MAKANAN</h2>
        <h3>BALAI BESAR POM DI BANJARMASIN</h3>
        <p>Jl. Bina Praja Barat, Komplek Perkantoran Pemprov Kalsel, Banjarbaru</p>
        <p>Telp: (0511) 4772186 | Email: bpom_banjarmasin@pom.go.id</p>
    </div>

    {{-- JUDUL BERITA ACARA --}}
    <div class="title">
        <h4>BERITA ACARA PENGEMBALIAN BARANG MILIK NEGARA</h4>
        <span>Nomor: {{ $pengembalian->nomor_pengajuan }}</span>
    </div>

    <p>
        Pada hari ini, tanggal <strong>{{ $pengembalian->tanggal ? $pengembalian->tanggal->translatedFormat('d F Y') : date('d F Y') }}</strong>, bertempat di Balai Besar Pengawas Obat dan Makanan di Banjarmasin, telah diserahkan kembali Barang Milik Negara (BMN) dari pihak pemakai ke Sub Bagian Tata Usaha / Pengelola BMN sebagai berikut:
    </p>

    <table class="content-table">
        <tr>
            <td class="label">Nomor Pengajuan</td>
            <td class="colon">:</td>
            <td><strong>{{ $pengembalian->nomor_pengajuan }}</strong></td>
        </tr>
        <tr>
            <td class="label">Nama Pegawai Pengembalian</td>
            <td class="colon">:</td>
            <td><strong>{{ $pengembalian->nama_pengaju }}</strong></td>
        </tr>
        <tr>
            <td class="label">Bidang / Unit Kerja</td>
            <td class="colon">:</td>
            <td>{{ $pengembalian->bidang }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Penyerahan</td>
            <td class="colon">:</td>
            <td>{{ $pengembalian->tanggal ? $pengembalian->tanggal->translatedFormat('d F Y') : '—' }}</td>
        </tr>
    </table>

    <p>
        Demikian Berita Acara Pengembalian BMN ini dibuat dengan penuh tanggung jawab untuk dipergunakan sebagaimana mestinya dalam rangka penertiban dan pemutakhiran data aset BMN Balai Besar POM di Banjarmasin.
    </p>

    {{-- BLOK TANDA TANGAN --}}
    <table class="signature-table">
        <tr>
            <td>
                Yang Menyerahkan Kembali,<br>
                <strong>Pegawai Pemakai</strong>
                <div class="signature-space"></div>
                <u><strong>{{ $pengembalian->nama_pengaju }}</strong></u><br>
                NIP. ....................................................
            </td>
            <td>
                Yang Menerima,<br>
                <strong>Pengelola BMN</strong>
                <div class="signature-space"></div>
                <u><strong>Operator BMN BBPOM Banjarmasin</strong></u><br>
                NIP. ....................................................
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center; padding-top: 30px;">
                Mengetahui,<br>
                <strong>Kepala Sub Bagian Tata Usaha</strong>
                <div class="signature-space"></div>
                <u><strong>Kepala Sub Bagian Tata Usaha</strong></u><br>
                NIP. ....................................................
            </td>
        </tr>
    </table>

</body>
</html>
