<?php

namespace App\Imports;

use App\Models\Part;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CavityImport implements ToCollection
{
    private int $updated = 0;
    private int $skipped = 0;

    /**
     * Struktur file data_cavity.xlsx:
     * - Row 1: judul ("Master Report")
     * - Row 2: header (None, ID, QRSOP, PART_NO, PROCESS_NO, MACHINE_NO, CATEGORY, QTY_CATEGORY, ...)
     * - Row 3+: data
     *
     * Mapping kolom (0-indexed):
     *   Index 3 (Col D) = PART_NO      → match ke part_no_child di tabel parts
     *   Index 6 (Col G) = CATEGORY
     *   Index 7 (Col H) = QTY_CATEGORY
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $rowIndex => $row) {
            // Skip baris 0 (judul) dan baris 1 (header)
            if ($rowIndex < 2) {
                continue;
            }

            $partNo      = trim($row[3] ?? '');
            $category    = trim($row[6] ?? '');
            $qtyCategory = $row[7] ?? null;

            if ($partNo === '') {
                continue;
            }

            // Lookup di tabel parts berdasarkan part_no_child
            $affected = Part::where('part_no_child', $partNo)
                ->update([
                    'category'     => $category ?: null,
                    'qty_category' => is_numeric($qtyCategory) ? (int) $qtyCategory : null,
                ]);

            if ($affected > 0) {
                $this->updated += $affected;
            } else {
                $this->skipped++;
            }
        }
    }

    public function getUpdatedCount(): int { return $this->updated; }
    public function getSkippedCount(): int  { return $this->skipped; }
}