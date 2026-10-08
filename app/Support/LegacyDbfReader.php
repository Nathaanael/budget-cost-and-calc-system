<?php

namespace App\Support;

use Generator;
use RuntimeException;

class LegacyDbfReader
{
    /**
     * @return Generator<int, array<string, mixed>>
     */
    public function records(string $path): Generator
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("File DBF tidak dapat dibuka: {$path}");
        }

        try {
            $header = fread($handle, 32);

            if ($header === false || strlen($header) !== 32) {
                throw new RuntimeException('Header DBF tidak valid.');
            }

            $recordCount = unpack('Vcount', substr($header, 4, 4))['count'];
            $headerLength = unpack('vlength', substr($header, 8, 2))['length'];
            $recordLength = unpack('vlength', substr($header, 10, 2))['length'];
            $fields = $this->readFields($handle, $headerLength);

            if ($recordLength < 2 || $fields === []) {
                throw new RuntimeException('Struktur field DBF tidak valid.');
            }

            fseek($handle, $headerLength);

            for ($index = 0; $index < $recordCount; $index++) {
                $record = fread($handle, $recordLength);

                if ($record === false || strlen($record) !== $recordLength) {
                    throw new RuntimeException('Record DBF terpotong pada baris '.($index + 1).'.');
                }

                if ($record[0] === '*') {
                    continue;
                }

                $values = [];
                $offset = 1;

                foreach ($fields as $field) {
                    $raw = substr($record, $offset, $field['length']);
                    $values[$field['name']] = $this->decodeValue($raw, $field['type']);
                    $offset += $field['length'];
                }

                yield $index + 1 => $values;
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * @return array<int, array{name: string, type: string, length: int}>
     */
    private function readFields($handle, int $headerLength): array
    {
        $fields = [];

        while (ftell($handle) < $headerLength) {
            $descriptor = fread($handle, 32);

            if ($descriptor === false || $descriptor === '' || ord($descriptor[0]) === 0x0D) {
                break;
            }

            if (strlen($descriptor) !== 32) {
                throw new RuntimeException('Descriptor field DBF tidak lengkap.');
            }

            $fields[] = [
                'name' => strtoupper(rtrim(substr($descriptor, 0, 11), "\0 ")),
                'type' => $descriptor[11],
                'length' => ord($descriptor[16]),
            ];
        }

        return $fields;
    }

    private function decodeValue(string $value, string $type): mixed
    {
        $value = trim(str_replace("\0", '', $value));

        if ($value === '') {
            return null;
        }

        if (in_array($type, ['N', 'F'], true)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        if ($type === 'L') {
            return in_array(strtoupper($value), ['Y', 'T'], true);
        }

        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        }

        return $value;
    }
}
