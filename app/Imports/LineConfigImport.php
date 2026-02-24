<?php

namespace App\Imports;

use App\Models\LineConfig;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Auth;

class LineConfigImport implements ToModel, WithChunkReading, SkipsEmptyRows
{
    private int $imported = 0;
    private int $skipped  = 0;

    /**
     * Excel: Kolom A = line, Kolom B = mesin (tanpa header row)
     */
    public function model(array $row): ?LineConfig
    {
        $line  = isset($row[0]) ? trim((string) $row[0]) : null;
        $mesin = isset($row[1]) ? trim((string) $row[1]) : null;

        if (empty($line) && empty($mesin)) {
            $this->skipped++;
            return null;
        }

        // Cek duplikat
        $exists = LineConfig::where('line', $line)->where('mesin', $mesin)->exists();
        if ($exists) {
            $this->skipped++;
            return null;
        }

        $this->imported++;

        return new LineConfig([
            'line'      => $line,
            'mesin'     => $mesin,
            'update_by' => Auth::check() ? Auth::user()->name : 'system',
        ]);
    }

    public function chunkSize(): int { return 500; }

    public function getImportedCount(): int { return $this->imported; }
    public function getSkippedCount(): int  { return $this->skipped;  }
}