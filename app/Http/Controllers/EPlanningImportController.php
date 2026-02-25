<?php

namespace App\Http\Controllers;

use App\Models\EPlanningStock;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EPlanningImportController extends Controller
{
    public function index()
    {
        $lastImport = EPlanningStock::max('created_at');
        $totalRows  = EPlanningStock::count();

        return view('e-planning.import', compact('lastImport', 'totalRows'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'File Excel wajib dipilih.',
            'file.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        try {
            $file        = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, false);

            $headerRowIndex = null;
            foreach ($rows as $idx => $row) {
                if (in_array('ID', $row)) {
                    $headerRowIndex = $idx;
                    break;
                }
            }

            if ($headerRowIndex === null) {
                return back()->with('error', 'Format file tidak sesuai. Baris header "ID" tidak ditemukan.');
            }

            $headers = array_map('trim', $rows[$headerRowIndex]);
            $colMap  = array_flip($headers);

            $requiredCols = ['ID', 'PART_NO_CHILD', 'LINE', 'STOCK_STORE'];
            foreach ($requiredCols as $col) {
                if (!isset($colMap[$col])) {
                    return back()->with('error', "Kolom wajib \"{$col}\" tidak ditemukan di file Excel.");
                }
            }

            EPlanningStock::truncate();

            $inserted  = 0;
            $batchSize = 500;
            $batch     = [];
            $now       = now()->toDateTimeString();

            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                if (empty(array_filter($row))) {
                    continue;
                }

                $get = fn($col) => isset($colMap[$col]) ? ($row[$colMap[$col]] ?? null) : null;

                $calcTime = null;
                $rawCalc  = $get('CALC_TIME');
                if ($rawCalc) {
                    try {
                        $calcTime = \Carbon\Carbon::parse($rawCalc)->toDateTimeString();
                    } catch (\Exception $e) {
                        $calcTime = null;
                    }
                }

                $batch[] = [
                    'e_planning_id'  => $get('ID'),
                    'judgement'      => $get('JUDGEMENT'),
                    'part_no_fg'     => $get('PART_NO_FG'),
                    'part_no_parent' => $get('PART_NO_PARENT'),
                    'part_no_child'  => $get('PART_NO_CHILD'),
                    'store'          => $get('STORE'),
                    'process'        => $get('PROCESS'),
                    'line'           => $get('LINE'),
                    'rack_no'        => $get('RACK_NO'),
                    'seq_calc'       => is_numeric($get('SEQ_CALC'))    ? (int) $get('SEQ_CALC')    : null,
                    'qty_kbn'        => is_numeric($get('QTY_KBN'))     ? (int) $get('QTY_KBN')     : null,
                    'qty_consume'    => is_numeric($get('QTY_CONSUME')) ? (int) $get('QTY_CONSUME') : null,
                    'qty_lot'        => is_numeric($get('QTY_LOT'))     ? (int) $get('QTY_LOT')     : null,
                    'stock_min'      => is_numeric($get('STOCK_MIN'))   ? (int) $get('STOCK_MIN')   : null,
                    'stock_max'      => is_numeric($get('STOCK_MAX'))   ? (int) $get('STOCK_MAX')   : null,
                    'stock_prod'     => is_numeric($get('STOCK_PROD'))  ? (int) $get('STOCK_PROD')  : null,
                    'stock_store'    => is_numeric($get('STOCK_STORE')) ? (int) $get('STOCK_STORE') : null,
                    'calc_prod'      => is_numeric($get('CALC_PROD'))   ? (int) $get('CALC_PROD')   : null,
                    'lt_prod'        => is_numeric($get('LT_PROD')) ? (float) $get('LT_PROD') : null, // ← tambahan
                    'status'         => $get('STATUS'),
                    'calc_by'        => $get('CALC_BY'),
                    'calc_time'      => $calcTime,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];

                $inserted++;

                if (count($batch) >= $batchSize) {
                    EPlanningStock::insert($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                EPlanningStock::insert($batch);
            }

            return back()->with('success', "Import berhasil! {$inserted} baris data berhasil diimpor.");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi error saat import: ' . $e->getMessage());
        }
    }
}