<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

/**
 * Export Formula FG mengikuti layout docs/tools/Contoh Template.ods (sheet FORMULA):
 * setiap FG = baris header, baris bahan (FG di baris pertama), lalu baris TOTAL.
 * File berisi satu FG dapat langsung di-upload kembali lewat Upload Excel.
 */
class FinishedGoodFormulaWorkbook
{
    private const HEADERS = ['FGCODE', 'FGNAME', 'OUPUT', 'UM', 'NO', 'RMCODE', 'RMNAME', 'USG/BATCH', 'UM', 'USG/UNIT', 'ID', 'KETERANGAN'];

    private const STYLE_HEADER = 1;

    private const STYLE_TEXT = 2;

    private const STYLE_OUTPUT = 3;

    private const STYLE_BATCH = 4;

    private const STYLE_UNIT = 5;

    private const STYLE_NOTES = 6;

    private const STYLE_TOTAL = 7;

    /**
     * @param  Collection<int, array{code: string, description: string, batch: int, notes: ?string, items: Collection<int, object>}>  $formulas
     */
    public function make(Collection $formulas): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'formula-fg-');

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
            $archive->addFromString('xl/workbook.xml', $this->workbookXml());
            $archive->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
            $archive->addFromString('xl/styles.xml', $this->stylesXml());
            $archive->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($formulas));
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

    private function worksheetXml(Collection $formulas): string
    {
        $rows = [];
        $rowNumber = 1;

        foreach ($formulas as $formula) {
            $cells = [];
            foreach (self::HEADERS as $index => $header) {
                $cells[] = $this->textCell($this->column($index).$rowNumber, $header, self::STYLE_HEADER);
            }
            $rows[] = $this->row($rowNumber++, $cells);

            $items = $formula['items']->values();

            if ($items->isEmpty()) {
                $rows[] = $this->row($rowNumber, $this->finishedGoodCells($formula, $rowNumber));
                $rowNumber++;
            }

            foreach ($items as $index => $item) {
                $cells = $index === 0 ? $this->finishedGoodCells($formula, $rowNumber) : [];
                $cells[] = $this->numberCell("E{$rowNumber}", $index + 1, self::STYLE_TEXT);
                $cells[] = $this->textCell("F{$rowNumber}", $item->code, self::STYLE_TEXT);
                $cells[] = $this->textCell("G{$rowNumber}", $item->description, self::STYLE_TEXT);
                $cells[] = $this->numberCell("H{$rowNumber}", (float) $item->standard_batch, self::STYLE_BATCH);
                $cells[] = $this->textCell("I{$rowNumber}", $item->unit, self::STYLE_TEXT);
                $cells[] = $this->numberCell("J{$rowNumber}", (float) $item->standard, self::STYLE_UNIT);
                $cells[] = $this->textCell("K{$rowNumber}", $item->material_id, self::STYLE_TEXT);
                $rows[] = $this->row($rowNumber++, $cells);
            }

            $rows[] = $this->row($rowNumber, [$this->textCell("G{$rowNumber}", 'TOTAL', self::STYLE_TOTAL)]);
            // Satu baris kosong sebagai pemisah antar FG.
            $rowNumber += 2;
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            .'<cols>'
            .'<col min="1" max="1" width="11" customWidth="1"/>'
            .'<col min="2" max="2" width="40" customWidth="1"/>'
            .'<col min="3" max="3" width="10" customWidth="1"/>'
            .'<col min="4" max="5" width="6" customWidth="1"/>'
            .'<col min="6" max="6" width="11" customWidth="1"/>'
            .'<col min="7" max="7" width="44" customWidth="1"/>'
            .'<col min="8" max="8" width="14" customWidth="1"/>'
            .'<col min="9" max="9" width="6" customWidth="1"/>'
            .'<col min="10" max="10" width="16" customWidth="1"/>'
            .'<col min="11" max="11" width="12" customWidth="1"/>'
            .'<col min="12" max="12" width="60" customWidth="1"/>'
            .'</cols>'
            .'<sheetData>'.implode('', $rows).'</sheetData>'
            .'</worksheet>';
    }

    private function finishedGoodCells(array $formula, int $rowNumber): array
    {
        return [
            $this->textCell("A{$rowNumber}", $formula['code'], self::STYLE_TEXT),
            $this->textCell("B{$rowNumber}", $formula['description'], self::STYLE_TEXT),
            $this->numberCell("C{$rowNumber}", (float) $formula['batch'], self::STYLE_OUTPUT),
            $this->textCell("L{$rowNumber}", $formula['notes'], self::STYLE_NOTES),
        ];
    }

    /** @param array<int, string> $cells */
    private function row(int $number, array $cells): string
    {
        // Sel harus berurutan per kolom agar valid di Excel.
        usort($cells, fn (string $left, string $right) => $this->cellColumn($left) <=> $this->cellColumn($right));

        return '<row r="'.$number.'">'.implode('', $cells).'</row>';
    }

    private function textCell(string $reference, ?string $value, int $style): string
    {
        return '<c r="'.$reference.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'
            .htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</t></is></c>';
    }

    private function numberCell(string $reference, float $value, int $style): string
    {
        return '<c r="'.$reference.'" s="'.$style.'"><v>'.$this->number($value).'</v></c>';
    }

    private function number(float $value): string
    {
        $formatted = rtrim(rtrim(number_format($value, 10, '.', ''), '0'), '.');

        return $formatted === '' || $formatted === '-0' ? '0' : $formatted;
    }

    private function column(int $index): string
    {
        return chr(65 + $index);
    }

    private function cellColumn(string $cellXml): int
    {
        preg_match('/r="([A-Z]+)\d+"/', $cellXml, $matches);

        return ord($matches[1] ?? 'A');
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
            .'</Types>';
    }

    private function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<bookViews><workbookView/></bookViews>'
            .'<sheets><sheet name="FORMULA" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
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
        // Format angka mengikuti template: OUTPUT #.##0, USG/BATCH 6 desimal, USG/UNIT 10 desimal.
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="2"><numFmt numFmtId="164" formatCode="0.000000"/><numFmt numFmtId="165" formatCode="0.0000000000"/></numFmts>'
            .'<fonts count="2"><font><sz val="10"/><name val="Arial"/></font><font><b/><sz val="10"/><name val="Arial"/></font></fonts>'
            .'<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="8">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment vertical="top"/></xf>'
            .'<xf numFmtId="3" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"><alignment vertical="top"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"><alignment vertical="top"/></xf>'
            .'<xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"><alignment vertical="top"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }
}
