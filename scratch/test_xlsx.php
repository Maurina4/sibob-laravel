<?php

class SimpleXlsx
{
    public static function create(string $filename, string $sheetTitle, array $headers, array $rows, ?float $totalNilai = null): void
    {
        $zip = new ZipArchive;
        if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Cannot create zip file: $filename");
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
        $cleanSheetName = htmlspecialchars(substr($sheetTitle, 0, 31), ENT_XML1, 'UTF-8');
        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="'.$cleanSheetName.'" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';
        $zip->addFromString('xl/workbook.xml', $wb);

        // 5. xl/styles.xml
        // Style 0: Normal
        // Style 1: Title (Bold, 14pt, Dark Green)
        // Style 2: Subtitle (10pt, Gray)
        // Style 3: Table Header (Bold, 11pt, White on Dark Green #163828, Centered)
        // Style 4: Data Text Left
        // Style 5: Data Text Center
        // Style 6: Data Number/Currency (Right, #,##0)
        // Style 7: Total Label (Bold, Right)
        // Style 8: Total Amount (Bold, Right, #,##0)
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
        $sheetXml = self::buildSheetXml($headers, $rows, $totalNilai);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();
    }

    private static function buildSheetXml(array $headers, array $rows, ?float $totalNilai): string
    {
        $cols = [
            ['width' => 6],   // No
            ['width' => 38],  // Nama Barang
            ['width' => 20],  // Kode Barang
            ['width' => 12],  // NUP
            ['width' => 22],  // Merek
            ['width' => 28],  // Lokasi
            ['width' => 16],  // Tahun Perolehan
            ['width' => 24],  // Nilai Perolehan (Rp)
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

        // Title row 1
        $xml .= '<row r="'.$rowIdx.'" ht="26" customHeight="1">';
        $xml .= '<c r="A'.$rowIdx.'" s="1" t="inlineStr"><is><t>MONITORING DAN EVALUASI PENGADAAN BMN</t></is></c>';
        $xml .= '</row>';
        $rowIdx++;

        // Subtitle row 2
        $xml .= '<row r="'.$rowIdx.'" ht="18" customHeight="1">';
        $xml .= '<c r="A'.$rowIdx.'" s="0" t="inlineStr"><is><t>Balai Besar Pengawas Obat dan Makanan di Banjarbaru — Diekspor pada: '.date('d/m/Y H:i').'</t></is></c>';
        $xml .= '</row>';
        $rowIdx++;

        // Empty row
        $rowIdx++;

        // Table Header row
        $xml .= '<row r="'.$rowIdx.'" ht="28" customHeight="1">';
        $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($headers as $colIdx => $h) {
            $ref = $colLetters[$colIdx].$rowIdx;
            $escaped = htmlspecialchars($h, ENT_XML1, 'UTF-8');
            $xml .= '<c r="'.$ref.'" s="3" t="inlineStr"><is><t>'.$escaped.'</t></is></c>';
        }
        $xml .= '</row>';
        $rowIdx++;

        $startDataRow = $rowIdx;

        // Data rows
        foreach ($rows as $item) {
            $xml .= '<row r="'.$rowIdx.'" ht="20" customHeight="1">';

            // Col A: No (Center, style 5)
            $xml .= '<c r="A'.$rowIdx.'" s="5"><v>'.(int) $item['no'].'</v></c>';

            // Col B: Nama Barang (Left, style 4)
            $nama = htmlspecialchars($item['nama_barang'] ?? '', ENT_XML1, 'UTF-8');
            $xml .= '<c r="B'.$rowIdx.'" s="4" t="inlineStr"><is><t>'.$nama.'</t></is></c>';

            // Col C: Kode Barang (Center, style 5)
            $kode = htmlspecialchars($item['kode_barang'] ?? '', ENT_XML1, 'UTF-8');
            $xml .= '<c r="C'.$rowIdx.'" s="5" t="inlineStr"><is><t>'.$kode.'</t></is></c>';

            // Col D: NUP (Center, style 5)
            $nup = htmlspecialchars($item['nup'] ?? '', ENT_XML1, 'UTF-8');
            $xml .= '<c r="D'.$rowIdx.'" s="5" t="inlineStr"><is><t>'.$nup.'</t></is></c>';

            // Col E: Merek (Left, style 4)
            $merek = htmlspecialchars($item['merek'] ?? '—', ENT_XML1, 'UTF-8');
            $xml .= '<c r="E'.$rowIdx.'" s="4" t="inlineStr"><is><t>'.$merek.'</t></is></c>';

            // Col F: Lokasi (Left, style 4)
            $lokasi = htmlspecialchars($item['lokasi'] ?? '—', ENT_XML1, 'UTF-8');
            $xml .= '<c r="F'.$rowIdx.'" s="4" t="inlineStr"><is><t>'.$lokasi.'</t></is></c>';

            // Col G: Tahun Perolehan (Center, style 5)
            $tahun = htmlspecialchars($item['tahun_perolehan'] ?? '—', ENT_XML1, 'UTF-8');
            $xml .= '<c r="G'.$rowIdx.'" s="5" t="inlineStr"><is><t>'.$tahun.'</t></is></c>';

            // Col H: Nilai Perolehan (Number, style 6)
            $nilai = (float) ($item['nilai_perolehan'] ?? 0);
            $xml .= '<c r="H'.$rowIdx.'" s="6"><v>'.$nilai.'</v></c>';

            $xml .= '</row>';
            $rowIdx++;
        }

        $endDataRow = $rowIdx - 1;

        // Total row if data exists
        if (! empty($rows)) {
            $xml .= '<row r="'.$rowIdx.'" ht="24" customHeight="1">';
            $xml .= '<c r="A'.$rowIdx.'" s="7"/>';
            $xml .= '<c r="B'.$rowIdx.'" s="7"/>';
            $xml .= '<c r="C'.$rowIdx.'" s="7"/>';
            $xml .= '<c r="D'.$rowIdx.'" s="7"/>';
            $xml .= '<c r="E'.$rowIdx.'" s="7"/>';
            $xml .= '<c r="F'.$rowIdx.'" s="7"/>';
            $xml .= '<c r="G'.$rowIdx.'" s="7" t="inlineStr"><is><t>TOTAL NILAI (RP):</t></is></c>';
            $xml .= '<c r="H'.$rowIdx.'" s="8"><f>SUM(H'.$startDataRow.':H'.$endDataRow.')</f><v>'.($totalNilai ?? 0).'</v></c>';
            $xml .= '</row>';
        }

        $xml .= '</sheetData>
</worksheet>';

        return $xml;
    }
}

// Test run
$testFile = __DIR__.'/test_monev.xlsx';
$headers = [
    'No',
    'Nama Barang',
    'Kode Barang',
    'NUP',
    'Merek',
    'Lokasi',
    'Tahun Perolehan',
    'Nilai Perolehan (Rp)',
];
$dummyRows = [
    [
        'no' => 1,
        'nama_barang' => 'Laptop ThinkPad X1 Carbon',
        'kode_barang' => '3.05.01.04.001',
        'nup' => '00001',
        'merek' => 'Lenovo',
        'lokasi' => 'Ruang Server / Ruang IT',
        'tahun_perolehan' => '2024',
        'nilai_perolehan' => 26500000,
    ],
    [
        'no' => 2,
        'nama_barang' => 'Spectrophotometer UV-Vis UV-1900i',
        'kode_barang' => '3.08.01.40.005',
        'nup' => '00001',
        'merek' => 'Shimadzu',
        'lokasi' => 'Laboratorium Kimia Makanan',
        'tahun_perolehan' => '2023',
        'nilai_perolehan' => 185000000,
    ],
];

SimpleXlsx::create($testFile, 'Monev Pengadaan', $headers, $dummyRows, 211500000);
echo "SUCCESS: Created $testFile (size: ".filesize($testFile)." bytes)\n";
