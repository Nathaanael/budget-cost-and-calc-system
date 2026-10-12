<?php

namespace App\Exports;

use App\Models\Reference;
use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

class RmPriceBudgetWorkbook
{
    private const PERIODS = [
        'current' => 'Current',
        'le' => 'LE',
        'qtr_1' => 'Kuartal 1',
        'qtr_2' => 'Kuartal 2',
        'qtr_3' => 'Kuartal 3',
        'qtr_4' => 'Kuartal 4',
    ];

    public function make(?Reference $reference, Collection $materials): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'rm-price-budget-');

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
            $archive->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($reference, $materials));
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

    private function worksheetXml(?Reference $reference, Collection $materials): string
    {
        $rows = [];
        $rows[] = $this->row(1, [$this->textCell('A1', 'Anggaran Harga RM', 1)]);
        $rows[] = $this->row(2, [
            $this->textCell('A2', 'Referensi', 2),
            $this->textCell('B2', $reference
                ? trim("{$reference->code} - {$reference->period_description}")
                : 'Referensi belum tersedia', 2),
        ]);
        $rows[] = $this->row(3, [
            $this->textCell('A3', 'Perusahaan / Divisi', 2),
            $this->textCell('B3', $reference
                ? trim($reference->description_1.($reference->description_2 ? ' - '.$reference->description_2 : ''))
                : '', 2),
        ]);

        $rows[] = $this->row(5, array_merge(
            [$this->textCell('A5', 'Kurs Referensi (Rp)', 3)],
            $this->periodCells(5, 'B', fn (string $label) => $label, 3),
        ));

        $rateFields = ['rate_current', 'rate_le', 'rate_1', 'rate_2', 'rate_3', 'rate_4'];
        $rateCells = [$this->textCell('A6', 'Nilai Kurs', 4)];

        foreach ($rateFields as $offset => $field) {
            $rateCells[] = $reference
                ? $this->numberCell($this->columnName(2 + $offset).'6', (float) $reference->{$field}, 8)
                : $this->blankCell($this->columnName(2 + $offset).'6', 8);
        }

        $rows[] = $this->row(6, $rateCells);
        $rows[] = $this->row(7, [
            $this->textCell('A7', 'Informasi Raw Material', 3),
            $this->textCell('F7', 'Harga USD', 3),
            $this->textCell('L7', 'Harga Rupiah', 3),
        ]);

        $headers = ['Kode RM', 'Raw Material', 'ID Material', 'Unit', 'Mata Uang'];
        $headerCells = [];

        foreach ($headers as $offset => $header) {
            $headerCells[] = $this->textCell($this->columnName(1 + $offset).'8', $header, 4);
        }

        foreach (array_values(self::PERIODS) as $offset => $label) {
            $headerCells[] = $this->textCell($this->columnName(6 + $offset).'8', $label, 4);
            $headerCells[] = $this->textCell($this->columnName(12 + $offset).'8', $label, 4);
        }

        usort($headerCells, fn (string $left, string $right) => $this->cellColumnNumber($left) <=> $this->cellColumnNumber($right));
        $rows[] = $this->row(8, $headerCells);

        foreach ($materials as $index => $material) {
            $rowNumber = 9 + $index;
            $prices = $material->prices->keyBy('period');
            $cells = [
                $this->textCell("A{$rowNumber}", $material->code, 5),
                $this->textCell("B{$rowNumber}", $material->description, 5),
                $this->textCell("C{$rowNumber}", $material->material_id ?? '', 5),
                $this->textCell("D{$rowNumber}", $material->unit, 5),
                $this->textCell("E{$rowNumber}", $material->currency_type, 5),
            ];

            foreach (array_keys(self::PERIODS) as $offset => $period) {
                $price = $prices->get($period);
                $cells[] = $price
                    ? $this->numberCell($this->columnName(6 + $offset).$rowNumber, (float) $price->usd_amount, 6)
                    : $this->blankCell($this->columnName(6 + $offset).$rowNumber, 6);
                $cells[] = $price
                    ? $this->numberCell($this->columnName(12 + $offset).$rowNumber, (float) $price->rupiah_amount, 6)
                    : $this->blankCell($this->columnName(12 + $offset).$rowNumber, 6);
            }

            usort($cells, fn (string $left, string $right) => $this->cellColumnNumber($left) <=> $this->cellColumnNumber($right));
            $rows[] = $this->row($rowNumber, $cells);
        }

        $lastRow = max(8, 8 + $materials->count());

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0" showGridLines="0">'
            .'<pane ySplit="8" topLeftCell="A9" activePane="bottomLeft" state="frozen"/>'
            .'</sheetView></sheetViews>'
            .'<cols>'
            .'<col min="1" max="1" width="15" customWidth="1"/>'
            .'<col min="2" max="2" width="36" customWidth="1"/>'
            .'<col min="3" max="3" width="18" customWidth="1"/>'
            .'<col min="4" max="5" width="13" customWidth="1"/>'
            .'<col min="6" max="17" width="16" customWidth="1"/>'
            .'</cols>'
            .'<sheetData>'.implode('', $rows).'</sheetData>'
            .'<autoFilter ref="A8:Q'.$lastRow.'"/>'
            .'<mergeCells count="4"><mergeCell ref="A1:Q1"/><mergeCell ref="A7:E7"/><mergeCell ref="F7:K7"/><mergeCell ref="L7:Q7"/></mergeCells>'
            .'<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
            .'<pageSetup orientation="landscape" fitToWidth="1" fitToHeight="0" paperSize="9"/>'
            .'</worksheet>';
    }

    private function periodCells(int $row, string $startColumn, callable $value, int $style): array
    {
        $cells = [];
        $start = ord($startColumn) - 64;

        foreach (array_values(self::PERIODS) as $offset => $label) {
            $cells[] = $this->textCell($this->columnName($start + $offset).$row, $value($label), $style);
        }

        return $cells;
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

    private function cellColumnNumber(string $cellXml): int
    {
        preg_match('/r="([A-Z]+)\d+"/', $cellXml, $matches);
        $number = 0;

        foreach (str_split($matches[1] ?? 'A') as $character) {
            $number = ($number * 26) + ord($character) - 64;
        }

        return $number;
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
            .'<sheets><sheet name="Anggaran Harga RM" sheetId="1" r:id="rId1"/></sheets>'
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

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00"/></numFmts>'
            .'<fonts count="4">'
            .'<font><sz val="10"/><name val="Arial"/></font>'
            .'<font><b/><sz val="15"/><color rgb="FF101828"/><name val="Arial"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FF344054"/><name val="Arial"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>'
            .'</fonts>'
            .'<fills count="4">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF0061A7"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFEAF4FB"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="2">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border><left style="thin"><color rgb="FFD0D5DD"/></left><right style="thin"><color rgb="FFD0D5DD"/></right><top style="thin"><color rgb="FFD0D5DD"/></top><bottom style="thin"><color rgb="FFD0D5DD"/></bottom><diagonal/></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="9">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="164" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
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
            .'<dc:title>Anggaran Harga RM</dc:title><dc:creator>Indofood Budget Cost and Sales Program</dc:creator>'
            .'<dcterms:created xsi:type="dcterms:W3CDTF">'.$createdAt.'</dcterms:created>'
            .'</cp:coreProperties>';
    }
}
