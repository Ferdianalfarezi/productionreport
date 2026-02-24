<?php

namespace App\Imports;

use App\Models\Part;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PartsImport implements ToCollection, WithHeadingRow
{
    private int $imported = 0;
    private int $skipped  = 0;

    /**
     * Baris ke-2 adalah header (baris 1 = judul "E-Planning Production")
     */
    public function headingRow(): int
    {
        return 2;
    }

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

            // Skip jika part_no_child sudah ada di database
            if ($partNoChild !== '' && Part::where('part_no_child', $partNoChild)->exists()) {
                $this->skipped++;
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

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }
}