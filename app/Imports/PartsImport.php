<?php

namespace App\Imports;

use App\Models\Part;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PartsImport implements ToCollection, WithHeadingRow
{
    private int $imported = 0;

    /**
     * Kolom yang diambil dari Excel:
     *   F = PART_NO_CHILD (index heading: part_no_child)
     *   I = LINE          (index heading: line)
     *   L = QTY_KBN       (index heading: qty_kbn)
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $partNoChild = trim($row['part_no_child'] ?? '');
            $line        = trim($row['line'] ?? '');
            $qtyKbn      = $row['qty_kbn'] ?? null;

            // Skip baris kosong
            if ($partNoChild === '' && $line === '' && $qtyKbn === null) {
                continue;
            }

            Part::create([
                'part_no_child' => $partNoChild ?: null,
                'line'          => $line ?: null,
                'qty_kbn'       => is_numeric($qtyKbn) ? $qtyKbn : null,
                'update_by'     => auth()->check() ? auth()->user()->name : 'import',
            ]);

            $this->imported++;
        }
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }
}
