<?php

namespace App\Http\Controllers;

use App\Models\EPlanningStock;
use App\Models\ReportProduction;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EPlanningImportController extends Controller
{
    public function index()
    {
        $lastImport      = EPlanningStock::max('created_at');
        $totalRows       = EPlanningStock::count();
        $lastReportImport = ReportProduction::max('created_at');
        $totalReportRows  = ReportProduction::count();

        return view('e-planning.import', compact(
            'lastImport', 'totalRows',
            'lastReportImport', 'totalReportRows',
        ));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_eplanning' => 'required|file|mimes:xlsx,xls|max:50000',
            'file_report'    => 'nullable|file|mimes:xlsx,xls|max:10240',
        ], [
            'file_eplanning.required' => 'File History E-Planning wajib dipilih.',
            'file_eplanning.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file_eplanning.max'      => 'Ukuran file maksimal 10 MB.',
            'file_report.mimes'       => 'File Report harus berformat .xlsx atau .xls.',
            'file_report.max'         => 'Ukuran file Report maksimal 10 MB.',
        ]);

        try {
            // ─────────────────────────────────────────────────────────
            // 1. IMPORT HISTORY E-PLANNING
            // ─────────────────────────────────────────────────────────
            $insertedEplanning = $this->importEPlanning($request->file('file_eplanning'));

            // ─────────────────────────────────────────────────────────
            // 2. IMPORT REPORT PRODUCTION (opsional)
            // ─────────────────────────────────────────────────────────
            $insertedReport = 0;
            if ($request->hasFile('file_report')) {
                $insertedReport = $this->importReportProduction($request->file('file_report'));
            }

            $msg = "Import berhasil! {$insertedEplanning} baris E-Planning diimpor.";
            if ($insertedReport > 0) {
                $msg .= " {$insertedReport} baris Report Production diimpor.";
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi error saat import: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVATE: Import History E-Planning
    // ─────────────────────────────────────────────────────────────────
    private function importEPlanning($file): int
    {
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
            throw new \Exception('Format file E-Planning tidak sesuai. Baris header "ID" tidak ditemukan.');
        }

        $headers = array_map('trim', $rows[$headerRowIndex]);
        $colMap  = array_flip($headers);

        foreach (['ID', 'PART_NO_CHILD', 'LINE', 'STOCK_STORE'] as $col) {
            if (!isset($colMap[$col])) {
                throw new \Exception("Kolom wajib \"{$col}\" tidak ditemukan di file E-Planning.");
            }
        }

        EPlanningStock::truncate();

        $inserted  = 0;
        $batchSize = 500;
        $batch     = [];
        $now       = now()->toDateTimeString();

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row))) continue;

            $get = fn($col) => isset($colMap[$col]) ? ($row[$colMap[$col]] ?? null) : null;

            // Skip baris dengan JUDGEMENT = 'No Prod'
            if (strtolower(trim((string)$get('JUDGEMENT'))) === 'no prod') continue;

            $calcTime = null;
            if ($raw = $get('CALC_TIME')) {
                try { $calcTime = \Carbon\Carbon::parse($raw)->toDateTimeString(); } catch (\Exception $e) {}
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
                'seq_calc'       => is_numeric($get('SEQ_CALC'))    ? (int)   $get('SEQ_CALC')    : null,
                'qty_kbn'        => is_numeric($get('QTY_KBN'))     ? (int)   $get('QTY_KBN')     : null,
                'qty_consume'    => is_numeric($get('QTY_CONSUME')) ? (int)   $get('QTY_CONSUME') : null,
                'qty_lot'        => is_numeric($get('QTY_LOT'))     ? (int)   $get('QTY_LOT')     : null,
                'stock_min'      => is_numeric($get('STOCK_MIN'))   ? (int)   $get('STOCK_MIN')   : null,
                'stock_max'      => is_numeric($get('STOCK_MAX'))   ? (int)   $get('STOCK_MAX')   : null,
                'stock_prod'     => is_numeric($get('STOCK_PROD'))  ? (int)   $get('STOCK_PROD')  : null,
                'stock_store'    => is_numeric($get('STOCK_STORE')) ? (int)   $get('STOCK_STORE') : null,
                'calc_prod'      => is_numeric($get('CALC_PROD'))   ? (int)   $get('CALC_PROD')   : null,
                'lt_prod'        => is_numeric($get('LT_PROD'))     ? (float) $get('LT_PROD')     : null,
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

        if (!empty($batch)) EPlanningStock::insert($batch);

        return $inserted;
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVATE: Import Report Production
    // ─────────────────────────────────────────────────────────────────
    private function importReportProduction($file): int
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false);

        // Cari baris header yang mengandung 'ID' atau 'PART_NO'
        $headerRowIndex = null;
        foreach ($rows as $idx => $row) {
            if (in_array('PART_NO', $row) || in_array('ID', $row)) {
                $headerRowIndex = $idx;
                break;
            }
        }

        if ($headerRowIndex === null) {
            throw new \Exception('Format file Report Production tidak sesuai. Header tidak ditemukan.');
        }

        $headers = array_map(fn($h) => is_string($h) ? strtoupper(trim($h)) : $h, $rows[$headerRowIndex]);
        $colMap  = array_flip(array_filter($headers, fn($h) => $h !== null && $h !== ''));

        foreach (['PART_NO', 'MACHINE_NO', 'QTY_OK'] as $col) {
            if (!isset($colMap[$col])) {
                throw new \Exception("Kolom wajib \"{$col}\" tidak ditemukan di file Report Production.");
            }
        }

        ReportProduction::truncate();

        $inserted  = 0;
        $batchSize = 500;
        $batch     = [];
        $now       = now()->toDateTimeString();

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row))) continue;

            $get = fn($col) => isset($colMap[$col]) ? ($row[$colMap[$col]] ?? null) : null;

            // Parse tanggal prod_date
            $prodDate = null;
            if ($raw = $get('PROD_DATE')) {
                try { $prodDate = \Carbon\Carbon::parse($raw)->toDateString(); } catch (\Exception $e) {}
            }

            // Parse update_time
            $updateTime = null;
            if ($raw = $get('UPDATE_TIME')) {
                try { $updateTime = \Carbon\Carbon::parse($raw)->toDateTimeString(); } catch (\Exception $e) {}
            }

            $batch[] = [
                'report_id'    => is_numeric($get('ID'))            ? (int)   $get('ID')            : null,
                'prod_date'    => $prodDate,
                'prod_start'   => $get('PROD_START'),
                'prod_finish'  => $get('PROD_FINISH'),
                'part_no'      => $get('PART_NO'),
                'category'     => $get('CATEGORY'),
                'qty_category' => is_numeric($get('QTY_CATEGORY')) ? (int)   $get('QTY_CATEGORY')  : null,
                'process_no'   => $get('PROCESS_NO'),
                'machine_no'   => $get('MACHINE_NO'),
                'act_machine'  => $get('ACT_MACHINE'),
                'shift'        => is_numeric($get('SHIFT'))        ? (int)   $get('SHIFT')          : null,
                'group_no'     => $get('GROUP_NO'),
                'dandori'      => is_numeric($get('DANDORI'))      ? (int)   $get('DANDORI')        : null,
                'stroke'       => is_numeric($get('STROKE'))       ? (int)   $get('STROKE')         : null,
                'qty_ok'       => is_numeric($get('QTY_OK'))       ? (int)   $get('QTY_OK')         : null,
                'qty_ng'       => is_numeric($get('QTY_NG'))       ? (int)   $get('QTY_NG')         : null,
                'code_ng'      => $get('CODE_NG'),
                'remarks'      => $get('REMARKS'),
                'wt_gross'     => is_numeric($get('WT_GROSS'))     ? (float) $get('WT_GROSS')       : null,
                'wt_net'       => is_numeric($get('WT_NET'))       ? (float) $get('WT_NET')         : null,
                'lt_process'   => is_numeric($get('LT_PROCESS'))   ? (int)   $get('LT_PROCESS')     : null,
                'lt_total'     => is_numeric($get('LT_TOTAL'))     ? (int)   $get('LT_TOTAL')       : null,
                'gsph_theory'  => is_numeric($get('GSPH_THEORY'))  ? (int)   $get('GSPH_THEORY')    : null,
                'gsph'         => is_numeric($get('GSPH'))         ? (int)   $get('GSPH')           : null,
                'sph'          => is_numeric($get('SPH'))          ? (int)   $get('SPH')            : null,
                'keterangan'   => $get('KETERANGAN'),
                'update_by'    => $get('UPDATE_BY'),
                'update_time'  => $updateTime,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];

            $inserted++;

            if (count($batch) >= $batchSize) {
                ReportProduction::insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) ReportProduction::insert($batch);

        return $inserted;
    }
}