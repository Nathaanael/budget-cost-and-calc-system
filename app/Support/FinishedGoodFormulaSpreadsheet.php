<?php

namespace App\Support;

use RuntimeException;

/**
 * Membaca template Formula FG (sheet FORMULA):
 * FGCODE | FGNAME | OUPUT | UM | NO | RMCODE | RMNAME | USG/BATCH | UM | USG/UNIT | ID | KETERANGAN.
 * Baris RM tanpa NO merupakan sub-header premix (komponennya ada di baris berikutnya) dan dilewati.
 */
class FinishedGoodFormulaSpreadsheet
{
    private const COLUMNS = [
        'fg_code' => ['FGCODE', 'KODEFG', 'CODEFG'],
        'fg_name' => ['FGNAME', 'NAMAFG'],
        'output' => ['OUTPUT', 'OUPUT', 'BATCH'],
        'no' => ['NO', 'NOMOR'],
        'rm_code' => ['RMCODE', 'KODERM', 'CODERM'],
        'rm_name' => ['RMNAME', 'NAMARM'],
        'standard_batch' => ['USG/BATCH', 'USG/BTH', 'STANDARDBATCH', 'STANDARDA'],
        'notes' => ['KETERANGAN', 'CATATAN', 'NOTES'],
    ];

    /**
     * @param  array<int, array<int, string|float|null>>  $rows
     * @return array{fg_code: string, fg_name: string, output: float|null, notes: string, items: array<int, array<string, mixed>>, skipped: array<int, array<string, mixed>>}
     */
    public function parse(array $rows): array
    {
        [$headerIndex, $columns] = $this->header($rows);
        $fgCode = '';
        $fgName = '';
        $output = null;
        $items = [];
        $skipped = [];
        $invalid = [];
        $notes = [];

        for ($index = $headerIndex + 1; $index < count($rows); $index++) {
            $row = $rows[$index];
            $excelRow = $index + 1;
            $rowFgCode = $this->code($this->cell($row, $columns['fg_code']));
            $rmCode = $this->code($this->cell($row, $columns['rm_code']));
            $rmName = trim((string) $this->cell($row, $columns['rm_name']));

            // Blok formula berakhir pada baris TOTAL atau header FG berikutnya.
            if (strtoupper($rmName) === 'TOTAL' || $this->normalize($rowFgCode) === 'FGCODE') {
                break;
            }

            if ($rowFgCode !== '') {
                if ($fgCode !== '' && $rowFgCode !== $fgCode) {
                    throw new RuntimeException(__('File berisi lebih dari satu kode FG (:first dan :second). Satu file hanya boleh berisi satu formula FG.', ['first' => $fgCode, 'second' => $rowFgCode]));
                }

                $fgCode = $rowFgCode;
                $fgName = $fgName !== '' ? $fgName : trim((string) $this->cell($row, $columns['fg_name']));
                $output = $output ?? $this->number($this->cell($row, $columns['output'] ?? null));
            }

            $note = trim((string) $this->cell($row, $columns['notes']));

            if ($note !== '') {
                $notes[] = $note;
            }

            if ($rmCode === '') {
                continue;
            }

            $number = $this->code($this->cell($row, $columns['no']));

            if ($number === '') {
                $skipped[] = ['row' => $excelRow, 'code' => $rmCode, 'name' => $rmName];

                continue;
            }

            $standardBatch = $this->number($this->cell($row, $columns['standard_batch']));

            if ($standardBatch === null || $standardBatch < 0) {
                $invalid[] = $excelRow;

                continue;
            }

            $items[] = [
                'row' => $excelRow,
                'no' => $number,
                'code' => $rmCode,
                'name' => $rmName,
                'standard_batch' => round($standardBatch, 10),
            ];
        }

        if ($invalid !== []) {
            throw new RuntimeException(__('Nilai USG/BATCH tidak valid pada baris :rows.', ['rows' => implode(', ', $invalid)]));
        }

        if ($fgCode === '') {
            throw new RuntimeException(__('Kode FG (kolom FGCODE) pada file Excel tidak ditemukan.'));
        }

        if ($items === []) {
            throw new RuntimeException(__('Tidak ada baris raw material/packaging yang dapat dibaca dari file Excel.'));
        }

        return compact('items', 'skipped') + [
            'fg_code' => $fgCode,
            'fg_name' => $fgName,
            'output' => $output,
            'notes' => implode("\n", $notes),
        ];
    }

    /** @return array{0: int, 1: array<string, int|null>} */
    private function header(array $rows): array
    {
        foreach ($rows as $index => $row) {
            $normalized = array_map(fn ($value) => $this->normalize((string) $value), $row);
            $columns = [];

            foreach (self::COLUMNS as $key => $aliases) {
                $columns[$key] = null;
                foreach ($normalized as $column => $value) {
                    if (in_array($value, $aliases, true)) {
                        $columns[$key] = $column;
                        break;
                    }
                }
            }

            if ($columns['fg_code'] !== null && $columns['rm_code'] !== null) {
                $missing = collect(['no' => 'NO', 'rm_name' => 'RMNAME', 'standard_batch' => 'USG/BATCH'])
                    ->filter(fn ($label, $key) => $columns[$key] === null)
                    ->values();

                if ($missing->isNotEmpty()) {
                    throw new RuntimeException(__('Kolom :columns tidak ditemukan pada header template.', ['columns' => $missing->implode(', ')]));
                }

                return [$index, $columns];
            }
        }

        throw new RuntimeException(__('Header template (FGCODE dan RMCODE) tidak ditemukan. Gunakan template Formula FG.'));
    }

    /** @return string|float|null */
    private function cell(array $row, ?int $column)
    {
        return $column === null ? null : ($row[$column] ?? null);
    }

    private function code($value): string
    {
        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        return strtoupper(trim((string) $value));
    }

    private function number($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        $text = str_replace([' ', "\u{00A0}"], '', trim((string) $value));

        // Format Indonesia: titik ribuan, koma desimal.
        if (strpos($text, ',') !== false) {
            $text = str_replace(['.', ','], ['', '.'], $text);
        }

        return is_numeric($text) ? (float) $text : null;
    }

    private function normalize(string $value): string
    {
        return strtoupper(preg_replace('/\s+/', '', $value));
    }
}
