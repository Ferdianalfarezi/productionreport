<?php

namespace App\Imports;

use App\Models\Mesin;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class MesinImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 3;
    }

    public function model(array $row)
    {
        $lineMachine = $row[1] ?? null;
        $machineNo   = $row[2] ?? null;

        if (empty($lineMachine) || empty($machineNo)) {
            return null;
        }

        // Skip jika machine_no sudah ada
        if (Mesin::where('machine_no', trim($machineNo))->exists()) {
            return null;
        }

        $gsph = $row[5] ?? 0;
        if (!is_numeric($gsph)) $gsph = 0;

        $updateTime = null;
        if (!empty($row[8])) {
            try {
                if (is_numeric($row[8])) {
                    $updateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[8]);
                } else {
                    $updateTime = \Carbon\Carbon::parse($row[8]);
                }
            } catch (\Exception $e) {
                $updateTime = null;
            }
        }

        return new Mesin([
            'line_machine' => trim($lineMachine),
            'machine_no'   => trim($machineNo),
            'tonage'       => isset($row[3]) && $row[3] !== '-' ? trim($row[3]) : null,
            'line'         => isset($row[4]) ? trim($row[4]) : null,
            'gsph_theory'  => (int) $gsph,
            'remarks'      => isset($row[6]) && !empty($row[6]) ? trim($row[6]) : null,
            'sw_line'      => isset($row[16]) && !empty($row[16]) ? trim($row[16]) : null,
            'sw_no'        => isset($row[17]) && !empty($row[17]) ? trim($row[17]) : null,
            'update_by'    => isset($row[7]) && !empty($row[7]) ? trim($row[7]) : '-',
            'update_time'  => $updateTime,
        ]);
    }
}