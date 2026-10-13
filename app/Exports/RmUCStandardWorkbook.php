<?php

namespace App\Exports;

use App\Models\Reference;
use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

/**
 * Excel Report2() REP1.PRG: satu tabel per FG, tanpa warna.
 * Kolom C, Unit Cost dan total ditulis sebagai rumus Excel agar tetap terhitung bila nilai diubah.
 */
class RmUCStandardWorkbook
{
    private const PERIODS = [
        'current' => ['label' => 'Current', 'price' => 'D', 'cost' => 'E'],
        'le' => ['label' => 'LE', 'price' => 'F', 'cost' => 'G'],
        'qtr_1' => ['label' => 'Kuartal 1', 'price' => 'H', 'cost' => 'I'],
        'qtr_2' => ['label' => 'Kuartal 2', 'price' => 'J', 'cost' => 'K'],
        'qtr_3' => ['label' => 'Kuartal 3', 'price' => 'L', 'cost' => 'M'],
        'qtr_4' => ['label' => 'Kuartal 4', 'price' => 'N', 'cost' => 'O'],
    ];

    // Kolom A-G: No, Kode RM, Raw Material, ID Material, (A), (B), (C); periode mulai kolom H.
    private const FIRST_PERIOD_COLUMN = 8;

    private const STYLE_TITLE = 1;
    private const STYLE_LABEL = 2;
    private const STYLE_HEADER = 3;
    private const STYLE_TEXT = 4;
    private const STYLE_QTY = 5;
    private const STYLE_MONEY = 6;
    private const STYLE_TOTAL_LABEL = 7;
    private const STYLE_TOTAL_MONEY = 8;
    private const STYLE_PLAIN = 9;
    private const STYLE_CENTER = 10;
    private const STYLE_PRODUCT = 11;

    private $merges = [];

    public function make(?Reference $reference, Collection $finishedGoods, array $filters): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'rm-uc-standard-');

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
        $this->merges = [];
        $lastColumn = $this->columnName(self::FIRST_PERIOD_COLUMN + (count(self::PERIODS) * 2) - 1);
        $rows = [];

        $rows[] = $this->row(1, [$this->textCell('A1', 'RAW MATERIAL UNIT COST STANDARD', self::STYLE_TITLE)]);
        $this->merges[] = "A1:{$lastColumn}1";

        $info = [
            'Perusahaan' => $reference ? $reference->description_1 : '',
            'Divisi' => $reference ? $reference->description_2 : '',
            'Periode' => $reference ? ($reference->period_description ?: $reference->period) : '',
            'Rentang FG' => $this->rangeDescription($filters),
            'Tanggal Cetak' => now()->format('d/m/Y H:i'),
        ];
        $rowNumber = 3;

        foreach ($info as $label => $value) {
            $rows[] = $this->row($rowNumber, [
                $this->textCell("A{$rowNumber}", $label, self::STYLE_LABEL),
                $this->textCell("C{$rowNumber}", ': '.$value, self::STYLE_PLAIN),
            ]);
            $this->merges[] = "A{$rowNumber}:B{$rowNumber}";
            $rowNumber++;
        }

        $rowNumber++;

        foreach ($finishedGoods as $finishedGood) {
            $rowNumber = $this->appendFinishedGood($rows, $rowNumber, $finishedGood, $lastColumn) + 2;
        }

        if ($finishedGoods->isEmpty()) {
            $rows[] = $this->row($rowNumber, [$this->textCell("A{$rowNumber}", 'Tidak ada Finished Good yang memiliki formula pada rentang ini.', self::STYLE_PLAIN)]);
        }

        $mergeXml = '<mergeCells count="'.count($this->merges).'">'
            .implode('', array_map(fn (string $ref) => '<mergeCell ref="'.$ref.'"/>', $this->merges))
            .'</mergeCells>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>'
            .'<sheetViews><sheetView workbookViewId="0" showGridLines="0"/></sheetViews>'
            .'<cols>'
            .'<col min="1" max="1" width="5" customWidth="1"/>'
            .'<col min="2" max="2" width="12" customWidth="1"/>'
            .'<col min="3" max="3" width="34" customWidth="1"/>'
            .'<col min="4" max="4" width="14" customWidth="1"/>'
            .'<col min="5" max="7" width="16" customWidth="1"/>'
            .'<col min="8" max="19" width="15" customWidth="1"/>'
            .'</cols>'
            .'<sheetData>'.implode('', $rows).'</sheetData>'
            .$mergeXml
            .'<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
            .'<pageSetup orientation="landscape" fitToWidth="1" fitToHeight="0" paperSize="9"/>'
            .'</worksheet>';
    }

    /**
     * Menulis satu blok tabel FG dan mengembalikan nomor baris total.
     */
    private function appendFinishedGood(array &$rows, int $rowNumber, array $finishedGood, string $lastColumn): int
    {
        $rows[] = $this->row($rowNumber, [
            $this->textCell("A{$rowNumber}", 'Product : '.$finishedGood['code'].' - '.$finishedGood['description'], self::STYLE_PRODUCT),
        ]);
        $this->merges[] = "A{$rowNumber}:{$lastColumn}{$rowNumber}";
        $rowNumber++;

        $top = $rowNumber;
        $bottom = $rowNumber + 1;
        $topCells = [];
        $bottomCells = [];

        foreach (['No', 'Kode RM', 'Raw Material', 'ID Material'] as $offset => $header) {
            $column = $this->columnName(1 + $offset);
            $topCells[] = $this->textCell("{$column}{$top}", $header, self::STYLE_HEADER);
            $bottomCells[] = $this->blankCell("{$column}{$bottom}", self::STYLE_HEADER);
            $this->merges[] = "{$column}{$top}:{$column}{$bottom}";
        }

        $quantityHeaders = [
            ['Standard Qty Usage (Zero Waste)', '(A)'],
            ['Budget Wastage Allowance', '(B)'],
            ['Budget Qty Usage', 'C = A + (A x B)'],
        ];

        foreach ($quantityHeaders as $offset => [$header, $formula]) {
            $column = $this->columnName(5 + $offset);
            $topCells[] = $this->textCell("{$column}{$top}", $header, self::STYLE_HEADER);
            $bottomCells[] = $this->textCell("{$column}{$bottom}", $formula, self::STYLE_HEADER);
        }

        foreach (array_values(self::PERIODS) as $offset => $meta) {
            $priceColumn = $this->columnName(self::FIRST_PERIOD_COLUMN + ($offset * 2));
            $costColumn = $this->columnName(self::FIRST_PERIOD_COLUMN + ($offset * 2) + 1);
            $topCells[] = $this->textCell("{$priceColumn}{$top}", $meta['label'], self::STYLE_HEADER);
            $topCells[] = $this->blankCell("{$costColumn}{$top}", self::STYLE_HEADER);
            $bottomCells[] = $this->textCell("{$priceColumn}{$bottom}", "RM Price ({$meta['price']})", self::STYLE_HEADER);
            $bottomCells[] = $this->textCell("{$costColumn}{$bottom}", "Unit Cost ({$meta['cost']} = C x {$meta['price']})", self::STYLE_HEADER);
            $this->merges[] = "{$priceColumn}{$top}:{$costColumn}{$top}";
        }

        $rows[] = $this->row($top, $topCells, 30);
        $rows[] = $this->row($bottom, $bottomCells, 30);
        $rowNumber = $bottom + 1;
        $firstDataRow = $rowNumber;

        foreach ($finishedGood['rows'] as $index => $item) {
            $r = $rowNumber;
            $cells = [
                $this->numberCell("A{$r}", $index + 1, self::STYLE_CENTER),
                $this->textCell("B{$r}", $item['code'], self::STYLE_TEXT),
                $this->textCell("C{$r}", $item['missing'] ? 'Raw Material tidak ditemukan' : $item['description'], self::STYLE_TEXT),
                $this->textCell("D{$r}", $item['material_id'], self::STYLE_TEXT),
                $this->numberCell("E{$r}", $item['standard'], self::STYLE_QTY),
                $this->numberCell("F{$r}", $item['waste'], self::STYLE_QTY),
                $this->formulaCell("G{$r}", "E{$r}+(E{$r}*F{$r})", $item['standard_with_waste'], self::STYLE_QTY),
            ];

            foreach (array_keys(self::PERIODS) as $offset => $period) {
                $priceColumn = $this->columnName(self::FIRST_PERIOD_COLUMN + ($offset * 2));
                $costColumn = $this->columnName(self::FIRST_PERIOD_COLUMN + ($offset * 2) + 1);
                $formula = "G{$r}*{$priceColumn}{$r}".($item['is_packaging'] ? '' : '/1000');
                $cells[] = $this->numberCell("{$priceColumn}{$r}", $item['periods'][$period]['price'], self::STYLE_MONEY);
                $cells[] = $this->formulaCell("{$costColumn}{$r}", $formula, $item['periods'][$period]['cost'], self::STYLE_MONEY);
            }

            $rows[] = $this->row($r, $cells);
            $rowNumber++;
        }

        $lastDataRow = $rowNumber - 1;
        $totalCells = [$this->textCell("A{$rowNumber}", 'Unit Cost Per Pack', self::STYLE_TOTAL_LABEL)];

        foreach (['B', 'C', 'D', 'E', 'F', 'G'] as $column) {
            $totalCells[] = $this->blankCell("{$column}{$rowNumber}", self::STYLE_TOTAL_LABEL);
        }

        $this->merges[] = "A{$rowNumber}:G{$rowNumber}";

        foreach (array_keys(self::PERIODS) as $offset => $period) {
            $priceColumn = $this->columnName(self::FIRST_PERIOD_COLUMN + ($offset * 2));
            $costColumn = $this->columnName(self::FIRST_PERIOD_COLUMN + ($offset * 2) + 1);
            $totalCells[] = $this->blankCell("{$priceColumn}{$rowNumber}", self::STYLE_TOTAL_MONEY);
            $totalCells[] = $this->formulaCell(
                "{$costColumn}{$rowNumber}",
                "SUM({$costColumn}{$firstDataRow}:{$costColumn}{$lastDataRow})",
                $finishedGood['totals'][$period],
                self::STYLE_TOTAL_MONEY
            );
        }

        $rows[] = $this->row($rowNumber, $totalCells);

        return $rowNumber;
    }

    private function rangeDescription(array $filters): string
    {
        $from = $filters['codeFrom'] !== '' ? $filters['codeFrom'] : 'Awal';
        $to = $filters['codeTo'] !== '' ? $filters['codeTo'] : 'Akhir';
        $description = "{$from} s/d {$to}";

        return $filters['search'] !== '' ? "{$description} (pencarian: {$filters['search']})" : $description;
    }

    private function row(int $number, array $cells, ?float $height = null): string
    {
        $heightAttributes = $height ? ' ht="'.$height.'" customHeight="1"' : '';

        return '<row r="'.$number.'"'.$heightAttributes.'>'.implode('', $cells).'</row>';
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

    private function formulaCell(string $reference, string $formula, float $cachedValue, int $style = 0): string
    {
        return '<c r="'.$reference.'" s="'.$style.'"><f>'.$this->escape($formula).'</f><v>'.$cachedValue.'</v></c>';
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
            .'<sheets><sheet name="Unit Cost Standar RM" sheetId="1" r:id="rId1"/></sheets>'
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
     * Tanpa warna: hanya font hitam default, garis tipis hitam, dan huruf tebal untuk header/total.
     */
    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="2"><numFmt numFmtId="164" formatCode="#,##0.00"/><numFmt numFmtId="165" formatCode="#,##0.000000"/></numFmts>'
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
            .'<cellXfs count="12">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="164" fontId="2" fillId="0" borderId="1" xfId="0" applyFont="1" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment vertical="center"/></xf>'
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
            .'<dc:title>Raw Material Unit Cost Standard</dc:title><dc:creator>Indofood Budget Cost and Sales Program</dc:creator>'
            .'<dcterms:created xsi:type="dcterms:W3CDTF">'.$createdAt.'</dcterms:created>'
            .'</cp:coreProperties>';
    }
}
