<?php

namespace App\Services;

use App\Models\Bmn;
use Illuminate\Support\Collection;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class BmnExportService
{
    /**
     * Header kolom tabel ekspor monev pengadaan BMN.
     *
     * @var array<int, string>
     */
    protected const TABLE_HEADERS = [
        'No',
        'Nama Barang',
        'Kode Barang',
        'NUP',
        'Merek',
        'Kondisi',
        'Tahun Perolehan',
        'Nilai Perolehan (Rp)',
        'Lokasi',
    ];

    /**
     * Ekspor data BMN ke format Microsoft Excel (.xlsx).
     *
     * @param  Collection<int, Bmn>|iterable<int, Bmn>  $bmns
     * @param  array<string, mixed>  $metadata
     */
    public function exportXlsx(iterable $bmns, string $filename, array $metadata = []): BinaryFileResponse
    {
        $tempDir = storage_path('framework/cache');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir.'/monev_bmn_'.bin2hex(random_bytes(8)).'.xlsx';

        $this->generateXlsxFile($tempPath, $bmns, $metadata);

        return response()
            ->download($tempPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-cache, private',
            ])
            ->deleteFileAfterSend(true);
    }

    /**
     * Ekspor data BMN ke format CSV UTF-8.
     *
     * @param  Collection<int, Bmn>|iterable<int, Bmn>  $bmns
     * @param  array<string, mixed>  $metadata
     */
    public function exportCsv(iterable $bmns, string $filename, array $metadata = []): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-cache, private',
        ];

        return response()->stream(function () use ($bmns, $metadata) {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM agar Excel membuka langsung dengan encoding yang benar
            fwrite($handle, "\xEF\xBB\xBF");

            // Baris Judul
            fputcsv($handle, ['MONITORING DAN EVALUASI PENGADAAN BARANG MILIK NEGARA (BMN)']);
            fputcsv($handle, ['Balai Besar Pengawas Obat dan Makanan di Banjarbaru']);
            if (! empty($metadata['periode'])) {
                fputcsv($handle, ['Periode / Kriteria: '.$metadata['periode']]);
            }
            fputcsv($handle, ['Tanggal Unduh: '.date('d/m/Y H:i:s')]);
            fputcsv($handle, []); // Baris kosong pemisah

            // Header Tabel
            fputcsv($handle, self::TABLE_HEADERS);

            $no = 1;
            $totalNilai = 0.0;

            foreach ($bmns as $bmn) {
                $nilai = (float) ($bmn->nilai_perolehan ?? 0);
                $totalNilai += $nilai;

                fputcsv($handle, [
                    $no++,
                    $bmn->nama_barang ?? '—',
                    $bmn->kode_barang ?? '—',
                    $bmn->nup ? (string) $bmn->nup : '—',
                    $bmn->merek ?: ($bmn->spesifikasi ?: '—'),
                    $bmn->kondisi ?: 'Baik',
                    $bmn->tahun_perolehan ? (string) $bmn->tahun_perolehan : '—',
                    $nilai,
                    $bmn->lokasi ?: '—',
                ]);
            }

            // Baris Total
            fputcsv($handle, []);
            fputcsv($handle, ['', '', '', '', '', '', 'TOTAL NILAI PENGADAAN (RP)', $totalNilai, '']);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Membangun berkas .xlsx murni berstandar OpenXML menggunakan ZipArchive.
     *
     * @param  Collection<int, Bmn>|iterable<int, Bmn>  $bmns
     * @param  array<string, mixed>  $metadata
     */
    protected function generateXlsxFile(string $filePath, iterable $bmns, array $metadata): void
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Tidak dapat membuat berkas arsip XLSX pada: {$filePath}");
        }

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // 3. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // 4. xl/workbook.xml
        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Monev Pengadaan BMN" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';
        $zip->addFromString('xl/workbook.xml', $wb);

        // 5. xl/styles.xml
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <numFmts count="1">
        <numFmt numFmtId="164" formatCode="#,##0"/>
    </numFmts>
    <fonts count="4">
        <font><name val="Calibri"/><sz val="11"/><color rgb="FF132219"/></font>
        <font><b/><name val="Calibri"/><sz val="14"/><color rgb="FF163828"/></font>
        <font><b/><name val="Calibri"/><sz val="11"/><color rgb="FFFFFFFF"/></font>
        <font><b/><name val="Calibri"/><sz val="11"/><color rgb="FF163828"/></font>
    </fonts>
    <fills count="4">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FF163828"/></patternFill></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFEAF2ED"/></patternFill></fill>
    </fills>
    <borders count="2">
        <border><left/><right/><top/><bottom/><diagonal/></border>
        <border>
            <left style="thin"><color rgb="FFD5E5DA"/></left>
            <right style="thin"><color rgb="FFD5E5DA"/></right>
            <top style="thin"><color rgb="FFD5E5DA"/></top>
            <bottom style="thin"><color rgb="FFD5E5DA"/></bottom>
        </border>
    </borders>
    <cellXfs count="9">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
            <alignment horizontal="center" vertical="center" wrapText="1"/>
        </xf>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">
            <alignment vertical="center"/>
        </xf>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">
            <alignment horizontal="center" vertical="center"/>
        </xf>
        <xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1">
            <alignment horizontal="right" vertical="center"/>
        </xf>
        <xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
            <alignment horizontal="right" vertical="center"/>
        </xf>
        <xf numFmtId="164" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyNumberFormat="1" applyBorder="1" applyAlignment="1">
            <alignment horizontal="right" vertical="center"/>
        </xf>
    </cellXfs>
</styleSheet>';
        $zip->addFromString('xl/styles.xml', $styles);

        // 6. xl/worksheets/sheet1.xml
        $sheetXml = $this->buildWorksheetXml($bmns, $metadata);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();
    }

    /**
     * Menghasilkan isi lembar kerja XML untuk berkas XLSX.
     *
     * @param  Collection<int, Bmn>|iterable<int, Bmn>  $bmns
     * @param  array<string, mixed>  $metadata
     */
    protected function buildWorksheetXml(iterable $bmns, array $metadata): string
    {
        $cols = [
            ['width' => 6],   // Col A: No
            ['width' => 38],  // Col B: Nama Barang
            ['width' => 20],  // Col C: Kode Barang
            ['width' => 12],  // Col D: NUP
            ['width' => 22],  // Col E: Merek
            ['width' => 16],  // Col F: Kondisi
            ['width' => 16],  // Col G: Tahun Perolehan
            ['width' => 24],  // Col H: Nilai Perolehan (Rp)
            ['width' => 28],  // Col I: Lokasi
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <cols>';
        foreach ($cols as $i => $col) {
            $idx = $i + 1;
            $xml .= '<col min="'.$idx.'" max="'.$idx.'" width="'.$col['width'].'" customWidth="1"/>';
        }
        $xml .= '</cols>
    <sheetData>';

        $rowIdx = 1;

        // Judul Utama (Row 1)
        $xml .= '<row r="'.$rowIdx.'" ht="26" customHeight="1">';
        $xml .= '<c r="A'.$rowIdx.'" s="1" t="inlineStr"><is><t>MONITORING DAN EVALUASI PENGADAAN BARANG MILIK NEGARA (BMN)</t></is></c>';
        $xml .= '</row>';
        $rowIdx++;

        // Subtitle Instansi & Metadata (Row 2)
        $periodeText = ! empty($metadata['periode']) ? ' | Periode: '.$metadata['periode'] : '';
        $subText = 'Balai Besar Pengawas Obat dan Makanan di Banjarbaru'.$periodeText.' — Diekspor pada: '.date('d/m/Y H:i');
        $xml .= '<row r="'.$rowIdx.'" ht="18" customHeight="1">';
        $xml .= '<c r="A'.$rowIdx.'" s="0" t="inlineStr"><is><t>'.htmlspecialchars($subText, ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '</row>';
        $rowIdx++;

        // Baris kosong jeda (Row 3)
        $rowIdx++;

        // Header Kolom Tabel (Row 4)
        $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        $xml .= '<row r="'.$rowIdx.'" ht="28" customHeight="1">';
        foreach (self::TABLE_HEADERS as $colIdx => $headerTitle) {
            $ref = $colLetters[$colIdx].$rowIdx;
            $escaped = htmlspecialchars($headerTitle, ENT_XML1, 'UTF-8');
            $xml .= '<c r="'.$ref.'" s="3" t="inlineStr"><is><t>'.$escaped.'</t></is></c>';
        }
        $xml .= '</row>';
        $rowIdx++;

        $startDataRow = $rowIdx;
        $totalNilai = 0.0;
        $count = 0;

        // Baris Data BMN
        foreach ($bmns as $bmn) {
            $count++;
            $nilai = (float) ($bmn->nilai_perolehan ?? 0);
            $totalNilai += $nilai;

            $nama = htmlspecialchars($bmn->nama_barang ?? '—', ENT_XML1, 'UTF-8');
            $kode = htmlspecialchars($bmn->kode_barang ?? '—', ENT_XML1, 'UTF-8');
            $nup = htmlspecialchars($bmn->nup ? (string) $bmn->nup : '—', ENT_XML1, 'UTF-8');
            $merek = htmlspecialchars($bmn->merek ?: ($bmn->spesifikasi ?: '—'), ENT_XML1, 'UTF-8');
            $kondisi = htmlspecialchars($bmn->kondisi ?: 'Baik', ENT_XML1, 'UTF-8');
            $tahun = htmlspecialchars($bmn->tahun_perolehan ? (string) $bmn->tahun_perolehan : '—', ENT_XML1, 'UTF-8');
            $lokasi = htmlspecialchars($bmn->lokasi ?: '—', ENT_XML1, 'UTF-8');

            $xml .= '<row r="'.$rowIdx.'" ht="20" customHeight="1">';
            // Col A: No
            $xml .= '<c r="A'.$rowIdx.'" s="5"><v>'.$count.'</v></c>';
            // Col B: Nama Barang
            $xml .= '<c r="B'.$rowIdx.'" s="4" t="inlineStr"><is><t>'.$nama.'</t></is></c>';
            // Col C: Kode Barang
            $xml .= '<c r="C'.$rowIdx.'" s="5" t="inlineStr"><is><t>'.$kode.'</t></is></c>';
            // Col D: NUP
            $xml .= '<c r="D'.$rowIdx.'" s="5" t="inlineStr"><is><t>'.$nup.'</t></is></c>';
            // Col E: Merek
            $xml .= '<c r="E'.$rowIdx.'" s="4" t="inlineStr"><is><t>'.$merek.'</t></is></c>';
            // Col F: Kondisi
            $xml .= '<c r="F'.$rowIdx.'" s="5" t="inlineStr"><is><t>'.$kondisi.'</t></is></c>';
            // Col G: Tahun Perolehan
            $xml .= '<c r="G'.$rowIdx.'" s="5" t="inlineStr"><is><t>'.$tahun.'</t></is></c>';
            // Col H: Nilai Perolehan
            $xml .= '<c r="H'.$rowIdx.'" s="6"><v>'.$nilai.'</v></c>';
            // Col I: Lokasi (di paling belakang)
            $xml .= '<c r="I'.$rowIdx.'" s="4" t="inlineStr"><is><t>'.$lokasi.'</t></is></c>';
            $xml .= '</row>';

            $rowIdx++;
        }

        $endDataRow = $rowIdx - 1;

        // Baris Total Pengadaan
        if ($count > 0) {
            $xml .= '<row r="'.$rowIdx.'" ht="24" customHeight="1">';
            $xml .= '<c r="A'.$rowIdx.'" s="7"/>';
            $xml .= '<c r="B'.$rowIdx.'" s="7"/>';
            $xml .= '<c r="C'.$rowIdx.'" s="7"/>';
            $xml .= '<c r="D'.$rowIdx.'" s="7"/>';
            $xml .= '<c r="E'.$rowIdx.'" s="7"/>';
            $xml .= '<c r="F'.$rowIdx.'" s="7"/>';
            $xml .= '<c r="G'.$rowIdx.'" s="7" t="inlineStr"><is><t>TOTAL NILAI (RP):</t></is></c>';
            $xml .= '<c r="H'.$rowIdx.'" s="8"><f>SUM(H'.$startDataRow.':H'.$endDataRow.')</f><v>'.$totalNilai.'</v></c>';
            $xml .= '<c r="I'.$rowIdx.'" s="7"/>';
            $xml .= '</row>';
        }

        $xml .= '</sheetData>
</worksheet>';

        return $xml;
    }
}
