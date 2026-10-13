<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

/**
 * Pembaca spreadsheet ringan (.xlsx dan .ods) tanpa dependensi tambahan.
 * Mengembalikan baris sheet sebagai array 0-based; sel formula memakai nilai hasil (cache).
 */
class SpreadsheetReader
{
    private const MAX_COLUMNS = 64;

    private const MAX_ROWS = 5000;

    /** @return array<int, array<int, string|float|null>> */
    public function rows(string $path, string $extension, ?string $preferredSheet = null): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException(__('File tidak dapat dibaca. Pastikan format file .xlsx atau .ods.'));
        }

        try {
            return strtolower($extension) === 'ods'
                ? $this->odsRows($zip, $preferredSheet)
                : $this->xlsxRows($zip, $preferredSheet);
        } finally {
            $zip->close();
        }
    }

    /** @return array<int, array<int, string|float|null>> */
    private function xlsxRows(ZipArchive $zip, ?string $preferredSheet): array
    {
        $workbook = $this->xpath($zip, 'xl/workbook.xml', 'm', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $sheets = $workbook->query('//m:sheets/m:sheet');

        if ($sheets->length === 0) {
            throw new RuntimeException(__('Sheet pada file Excel tidak ditemukan.'));
        }

        $sheet = $sheets->item(0);
        foreach ($sheets as $candidate) {
            if ($preferredSheet !== null && strcasecmp($candidate->getAttribute('name'), $preferredSheet) === 0) {
                $sheet = $candidate;
                break;
            }
        }

        $relationId = $sheet->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
        $relations = $this->xpath($zip, 'xl/_rels/workbook.xml.rels', 'p', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $target = optional($relations->query("//p:Relationship[@Id='{$relationId}']")->item(0))->getAttribute('Target');

        if (! $target) {
            throw new RuntimeException(__('Sheet pada file Excel tidak ditemukan.'));
        }

        $sheetPath = strpos($target, '/') === 0 ? ltrim($target, '/') : 'xl/'.ltrim($target, './');
        $sharedStrings = $this->sharedStrings($zip);
        $xml = $this->xpath($zip, $sheetPath, 'm', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = [];

        foreach ($xml->query('//m:sheetData/m:row') as $rowNode) {
            $rowIndex = ((int) $rowNode->getAttribute('r') ?: count($rows) + 1) - 1;

            if ($rowIndex >= self::MAX_ROWS) {
                break;
            }

            $cells = [];
            foreach ($xml->query('./m:c', $rowNode) as $cell) {
                $column = $this->columnIndex($cell->getAttribute('r'));

                if ($column === null || $column >= self::MAX_COLUMNS) {
                    continue;
                }

                $cells[$column] = $this->xlsxValue($xml, $cell, $sharedStrings);
            }

            $rows[$rowIndex] = $this->fill($cells);
        }

        ksort($rows);

        return $this->fillRows($rows);
    }

    /** @return array<int, string> */
    private function sharedStrings(ZipArchive $zip): array
    {
        if ($zip->locateName('xl/sharedStrings.xml') === false) {
            return [];
        }

        $xml = $this->xpath($zip, 'xl/sharedStrings.xml', 'm', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $strings = [];

        foreach ($xml->query('//m:si') as $item) {
            $text = '';
            foreach ($xml->query('.//m:t', $item) as $node) {
                $text .= $node->textContent;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /** @return string|float|null */
    private function xlsxValue(DOMXPath $xml, DOMElement $cell, array $sharedStrings)
    {
        $type = $cell->getAttribute('t');

        if ($type === 'inlineStr') {
            $text = '';
            foreach ($xml->query('.//m:t', $cell) as $node) {
                $text .= $node->textContent;
            }

            return $text;
        }

        $valueNode = $xml->query('./m:v', $cell)->item(0);

        if (! $valueNode) {
            return null;
        }

        $value = $valueNode->textContent;

        if ($type === 's') {
            return $sharedStrings[(int) $value] ?? '';
        }

        if ($type === 'str' || $type === 'e' || $type === 'b') {
            return $value;
        }

        return is_numeric($value) ? (float) $value : $value;
    }

    /** @return array<int, array<int, string|float|null>> */
    private function odsRows(ZipArchive $zip, ?string $preferredSheet): array
    {
        $xml = $this->xpath($zip, 'content.xml', 'table', 'urn:oasis:names:tc:opendocument:xmlns:table:1.0');
        $xml->registerNamespace('office', 'urn:oasis:names:tc:opendocument:xmlns:office:1.0');
        $xml->registerNamespace('text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');
        $tables = $xml->query('//table:table');

        if ($tables->length === 0) {
            throw new RuntimeException(__('Sheet pada file Excel tidak ditemukan.'));
        }

        $table = $tables->item(0);
        foreach ($tables as $candidate) {
            if ($preferredSheet !== null && strcasecmp($candidate->getAttribute('table:name'), $preferredSheet) === 0) {
                $table = $candidate;
                break;
            }
        }

        $rows = [];
        $rowIndex = 0;

        foreach ($xml->query('.//table:table-row', $table) as $rowNode) {
            $repeat = max(1, (int) $rowNode->getAttribute('table:number-rows-repeated'));
            $cells = [];
            $column = 0;

            foreach ($xml->query('./table:table-cell|./table:covered-table-cell', $rowNode) as $cell) {
                $columnRepeat = max(1, (int) $cell->getAttribute('table:number-columns-repeated'));
                $value = $this->odsValue($xml, $cell);

                for ($i = 0; $i < $columnRepeat && $column < self::MAX_COLUMNS; $i++, $column++) {
                    if ($value !== null) {
                        $cells[$column] = $value;
                    }
                }

                if ($column >= self::MAX_COLUMNS) {
                    break;
                }
            }

            // Baris kosong berulang (padding sheet) cukup dilewati tanpa diperluas.
            if ($cells !== []) {
                for ($i = 0; $i < $repeat && $rowIndex < self::MAX_ROWS; $i++) {
                    $rows[$rowIndex++] = $this->fill($cells);
                }
            } else {
                $rowIndex += $repeat;
            }

            if ($rowIndex >= self::MAX_ROWS) {
                break;
            }
        }

        return $this->fillRows($rows);
    }

    /** @return string|float|null */
    private function odsValue(DOMXPath $xml, DOMElement $cell)
    {
        $type = $cell->getAttribute('office:value-type');

        if (in_array($type, ['float', 'percentage', 'currency'], true)) {
            return (float) $cell->getAttribute('office:value');
        }

        $paragraphs = [];
        foreach ($xml->query('./text:p', $cell) as $paragraph) {
            $paragraphs[] = $this->odsText($paragraph);
        }

        $text = implode("\n", $paragraphs);

        return $text === '' ? null : $text;
    }

    /** Teks ODS menyimpan spasi berulang, tab, dan baris baru sebagai elemen (text:s, text:tab, text:line-break). */
    private function odsText(\DOMNode $node): string
    {
        $text = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE || $child->nodeType === XML_CDATA_SECTION_NODE) {
                $text .= $child->nodeValue;
            } elseif ($child instanceof DOMElement && $child->localName === 's') {
                $text .= str_repeat(' ', max(1, (int) $child->getAttribute('text:c')));
            } elseif ($child instanceof DOMElement && $child->localName === 'tab') {
                $text .= "\t";
            } elseif ($child instanceof DOMElement && $child->localName === 'line-break') {
                $text .= "\n";
            } elseif ($child instanceof DOMElement) {
                $text .= $this->odsText($child);
            }
        }

        return $text;
    }

    private function xpath(ZipArchive $zip, string $name, string $prefix, string $namespace): DOMXPath
    {
        $content = $zip->getFromName($name);

        if ($content === false) {
            throw new RuntimeException(__('File tidak dapat dibaca. Pastikan format file .xlsx atau .ods.'));
        }

        $document = new DOMDocument();

        if (! @$document->loadXML($content, LIBXML_NONET | LIBXML_COMPACT)) {
            throw new RuntimeException(__('File tidak dapat dibaca. Pastikan format file .xlsx atau .ods.'));
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace($prefix, $namespace);

        return $xpath;
    }

    private function columnIndex(string $reference): ?int
    {
        if (! preg_match('/^([A-Z]+)\d*$/i', $reference, $matches)) {
            return null;
        }

        $index = 0;
        foreach (str_split(strtoupper($matches[1])) as $letter) {
            $index = $index * 26 + (ord($letter) - 64);
        }

        return $index - 1;
    }

    /** @param array<int, string|float|null> $cells */
    private function fill(array $cells): array
    {
        if ($cells === []) {
            return [];
        }

        $row = array_fill(0, max(array_keys($cells)) + 1, null);

        foreach ($cells as $column => $value) {
            $row[$column] = $value;
        }

        return $row;
    }

    /** @param array<int, array<int, string|float|null>> $rows */
    private function fillRows(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $filled = array_fill(0, max(array_keys($rows)) + 1, []);

        foreach ($rows as $index => $row) {
            $filled[$index] = $row;
        }

        return $filled;
    }
}
