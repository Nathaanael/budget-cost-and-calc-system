<?php

namespace App\Exports;

use App\Http\Controllers\Admin\Reporting\SellUCUPController;
use App\Models\Reference;
use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

/**
 * Excel Report3() REP1.PRG: satu tabel, dikelompokkan per Product Type 1 / 2, tanpa warna.
 */
class SellUCUPWorkbook
{
    // Kolom A-B: Kode FG, Deskripsi; nilai mulai kolom C.
    private const FIRST_VALUE_COLUMN = 3;

    private const HEADER_FIRST_ROW = 9;

    private const STYLE_TITLE = 1;
    private const STYLE_LABEL = 2;
    private const STYLE_HEADER = 3;
    private const STYLE_TEXT = 4;
    private const STYLE_MONEY = 5;
    private const STYLE_GROUP = 6;
    private const STYLE_PLAIN = 7;

    public function make(?Reference $reference, Collection $finishedGoods, array $filters): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'sell-uc-up-');

        if ($temporaryPath === false) {
            throw new RuntimeException('Gagal membuat file Excel sementara.');
        }

        try {
            $archive = new ZipArchive;

            if ($archive->open($temporaryPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Gagal membuat arsip Excel.');
            }

            $archive->addFromString('[Content_Types].xml', $this->contentTypesXml());
            $archive->addFromString('_rels/.rels', $this->rootRelationshipsXml());
            $archive->addFromString('docProps/app.xml', $this->appPropertiesXml());
            $archive->addFromString('docProps/core.xml', $this->corePropertiesXml());
            $archive->addFromString('xl/workbook.xml', $this->workbookXml());
            $archive->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
            $archive->addFromString('xl/styles.xml', $this->stylesXml());
            $archive->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($reference, $finishedGoods, $filters));
            $archive->close();

            $contents = file_get_contents($temporaryPath);

            if ($contents === false) {
                throw new RuntimeException('Gagal membaca file Excel.');
            }

            return $contents;
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    private function worksheetXml(?Reference $reference, Collection $finishedGoods, array $filters): string
    {
        $blocks = SellUCUPController::BLOCKS;
        $periods = SellUCUPController::PERIODS;
        $lastColumn = $this->columnName(self::FIRST_VALUE_COLUMN + (count($blocks) * count($periods)) - 1);
        $merges = ["A1:{$lastColumn}1"];
        $rows = [$this->row(1, [$this->textCell('A1', 'SELLING PRICE & UNIT COST', self::STYLE_TITLE)])];
        $aopYear = SellUCUPController::aopYear($reference);

        $info = [
            'Perusahaan' => $reference ? $reference->description_1 : '',
            'Divisi' => $reference ? $reference->description_2 : '',
            'AOP Year' => $aopYear ? (string) $aopYear : '-',
            'Rentang FG' => $this->rangeDescription($filters),
            'Tanggal Cetak' => now()->format('d/m/Y H:i'),
        ];
        $rowNumber = 3;

        foreach ($info as $label => $value) {
            $rows[] = $this->row($rowNumber, [
                $this->textCell("A{$rowNumber}", $label, self::STYLE_LABEL),
                $this->textCell("B{$rowNumber}", ': '.$value, self::STYLE_PLAIN),
            ]);
            $rowNumber++;
        }

        $top = self::HEADER_FIRST_ROW;
        $bottom = $top + 1;
        $topCells = [
            $this->textCell("A{$top}", 'Kode FG', self::STYLE_HEADER),
            $this->textCell("B{$top}", 'Finished Goods', self::STYLE_HEADER),
        ];
        $bottomCells = [$this->blankCell("A{$bottom}", self::STYLE_HEADER), $this->blankCell("B{$bottom}", self::STYLE_HEADER)];
        $merges[] = "A{$top}:A{$bottom}";
        $merges[] = "B{$top}:B{$bottom}";
        $column = self::FIRST_VALUE_COLUMN;

        foreach ($blocks as $block) {
            $first = $this->columnName($column);
            $last = $this->columnName($column + count($periods) - 1);
            $merges[] = "{$first}{$top}:{$last}{$top}";

            foreach (array_values($periods) as $offset => $label) {
                $name = $this->columnName($column + $offset);
                $topCells[] = $offset === 0
                    ? $this->textCell("{$name}{$top}", $block['label'], self::STYLE_HEADER)
                    : $this->blankCell("{$name}{$top}", self::STYLE_HEADER);
                $bottomCells[] = $this->textCell("{$name}{$bottom}", $label, self::STYLE_HEADER);
            }

            $column += count($periods);
        }

        $rows[] = $this->row($top, $topCells);
        $rows[] = $this->row($bottom, $bottomCells);
        $rowNumber = $bottom + 1;
        $group = null;

        foreach ($finishedGoods as $finishedGood) {
            $currentGroup = $finishedGood['product_type_1'].'/'.$finishedGood['product_type_2'];

            // Report3() memberi jeda tiap pergantian PrType1/PrType2; di Excel diganti baris judul grup.
            if ($currentGroup !== $group) {
                $rows[] = $this->row($rowNumber, [$this->textCell(
                    "A{$rowNumber}",
                    'Product Type '.$finishedGood['product_type_1'].' / '.$finishedGood['product_type_2'],
                    self::STYLE_GROUP
                )]);
                $merges[] = "A{$rowNumber}:{$lastColumn}{$rowNumber}";
                $group = $currentGroup;
                $rowNumber++;
            }

            $cells = [
                $this->textCell("A{$rowNumber}", $finishedGood['code'], self::STYLE_TEXT),
                $this->textCell("B{$rowNumber}", $finishedGood['description'], self::STYLE_TEXT),
            ];

            foreach (SellUCUPController::valueFields() as $offset => $field) {
                $cells[] = $this->numberCell(
                    $this->columnName(self::FIRST_VALUE_COLUMN + $offset).$rowNumber,
                    (float) $finishedGood[$field],
                    self::STYLE_MONEY
                );
            }

            $rows[] = $this->row($rowNumber, $cells);
            $rowNumber++;
        }

        if ($finishedGoods->isEmpty()) {
            $rows[] = $this->row($rowNumber, [$this->textCell("A{$rowNumber}", 'Tidak ada Finished Good dengan Unit Cost atau Selling Price pada rentang ini.', self::STYLE_PLAIN)]);
        }

        $mergeXml = '<mergeCells count="'.count($merges).'">'
            .implode('', array_map(fn (string $ref) => '<mergeCell ref="'.$ref.'"/>', $merges))
            .'</mergeCells>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>'
            .'<sheetViews><sheetView workbookViewId="0" showGridLines="0">'
            .'<pane xSplit="2" ySplit="'.$bottom.'" topLeftCell="C'.($bottom + 1).'" activePane="bottomRight" state="frozen"/>'
            .'</sheetView></sheetViews>'
            .'<cols>'
            .'<col min="1" max="1" width="11" customWidth="1"/>'
            .'<col min="2" max="2" width="34" customWidth="1"/>'
            .'<col min="3" max="'.(self::FIRST_VALUE_COLUMN + 29).'" width="12" customWidth="1"/>'
            .'</cols>'
            .'<sheetData>'.implode('', $rows).'</sheetData>'
            .$mergeXml
            .'<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
            .'<pageSetup orientation="landscape" fitToWidth="1" fitToHeight="0" paperSize="9"/>'
            .'</worksheet>';
    }

    private function rangeDescription(array $filters): string
    {
        $from = $filters['codeFrom'] !== '' ? $filters['codeFrom'] : 'Awal';
        $to = $filters['codeTo'] !== '' ? $filters['codeTo'] : 'Akhir';
        $description = "{$from} s/d {$to}";

        if ($filters['productType1'] !== '' || $filters['productType2'] !== '') {
            $type1 = $filters['productType1'] !== '' ? $filters['productType1'] : 'Semua';
            $type2 = $filters['productType2'] !== '' ? $filters['productType2'] : 'Semua';
            $description .= " (Product Type {$type1} / {$type2})";
        }

        return $filters['search'] !== '' ? "{$description} (pencarian: {$filters['search']})" : $description;
    }

    private function row(int $number, array $cells): string
    {
        return '<row r="'.$number.'">'.implode('', $cells).'</row>';
    }

    private function textCell(string $reference, ?string $value, int $style = 0): string
    {
        return '<c r="'.$reference.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'
            .$this->escape($value ?? '').'</t></is></c>';
    }

    private function numberCell(string $reference, float $value, int $style = 0): string
    {
        return '<c r="'.$reference.'" s="'.$style.'" t="n"><v>'.$value.'</v></c>';
    }

    private function blankCell(string $reference, int $style = 0): string
    {
        return '<c r="'.$reference.'" s="'.$style.'"/>';
    }

    private function columnName(int $number): string
    {
        $name = '';

        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            .'<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            .'</Types>';
    }

    private function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            .'</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<bookViews><workbookView/></bookViews>'
            .'<sheets><sheet name="Selling Price &amp; Unit Cost" sheetId="1" r:id="rId1"/></sheets>'
            .'<calcPr calcId="191029" fullCalcOnLoad="1"/></workbook>';
    }

    private function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    /**
     * Tanpa warna: font hitam default, garis tipis hitam, huruf tebal untuk header dan judul grup.
     * Nilai nol ditampilkan "-" seperti picture "@z" di Report3().
     */
    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00;-#,##0.00;&quot;-&quot;"/></numFmts>'
            .'<fonts count="3">'
            .'<font><sz val="10"/><name val="Arial"/></font>'
            .'<font><b/><sz val="14"/><name val="Arial"/></font>'
            .'<font><b/><sz val="10"/><name val="Arial"/></font>'
            .'</fonts>'
            .'<fills count="2">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'</fills>'
            .'<borders count="2">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border><left style="thin"><color auto="1"/></left><right style="thin"><color auto="1"/></right><top style="thin"><color auto="1"/></top><bottom style="thin"><color auto="1"/></bottom><diagonal/></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="8">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment vertical="center"/></xf>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function appPropertiesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            .'<Application>Microsoft Excel</Application><AppVersion>16.0300</AppVersion>'
            .'</Properties>';
    }

    private function corePropertiesXml(): string
    {
        $createdAt = now()->utc()->format('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            .'<dc:title>Selling Price &amp; Unit Cost</dc:title><dc:creator>Indofood Budget Cost and Sales Program</dc:creator>'
            .'<dcterms:created xsi:type="dcterms:W3CDTF">'.$createdAt.'</dcterms:created>'
            .'</cp:coreProperties>';
    }
}
