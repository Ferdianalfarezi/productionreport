<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\LineConfig;
use App\Models\EPlanningStock;
use App\Models\ReportProduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportProduksiController extends Controller
{
    public function index(Request $request)
    {
        $lines = LineConfig::select('line')->distinct()->orderBy('line')->pluck('line');

        $selectedLine  = $request->get('line', $lines->first());
        $selectedMesin = $request->get('mesin');

        $mesins = LineConfig::where('line', $selectedLine)
            ->orderBy('mesin')
            ->pluck('mesin');

        if (!$selectedMesin && $mesins->isNotEmpty()) {
            $selectedMesin = $mesins->first();
        }

        $parts       = collect();
        $stockMap    = collect();
        $reportMap   = [];
        $summaryData = [];

        if ($selectedMesin) {
            $validPartNos = EPlanningStock::where('line', $selectedMesin)
                ->pluck('part_no_child')
                ->unique();

            $parts = Part::where('line', $selectedMesin)
                ->whereIn('part_no_child', $validPartNos)
                ->orderBy('part_no_child')
                ->get();
        }

        if ($parts->isNotEmpty()) {
            $partNos = $parts->pluck('part_no_child')->unique()->values();

            // ── StockMap ──
            $stockData = EPlanningStock::whereIn('part_no_child', $partNos)
                ->where('line', $selectedMesin)
                ->select('part_no_child', DB::raw('MAX(e_planning_id) as max_id'))
                ->groupBy('part_no_child')
                ->get();

            if ($stockData->isNotEmpty()) {
                $maxIds    = $stockData->pluck('max_id')->toArray();
                $stockRows = EPlanningStock::whereIn('e_planning_id', $maxIds)
                    ->where('line', $selectedMesin)
                    ->get(['part_no_child', 'stock_store', 'calc_prod', 'lt_prod', 'qty_kbn', 'e_planning_id']);

                $stockMap = $stockRows->keyBy('part_no_child');
            }

            // ── ReportMap ──
            $latestIds = ReportProduction::whereIn('part_no', $partNos)
                ->where('machine_no', $selectedMesin)
                ->whereIn('shift', [1, 2])
                ->select('part_no', 'shift', DB::raw('MAX(report_id) as max_id'))
                ->groupBy('part_no', 'shift')
                ->get();

            if ($latestIds->isNotEmpty()) {
                $maxReportIds = $latestIds->pluck('max_id')->toArray();

                $reportRows = ReportProduction::whereIn('report_id', $maxReportIds)
                    ->whereIn('shift', [1, 2])
                    ->get(['part_no', 'shift', 'qty_ok', 'prod_start', 'prod_finish']);

                foreach ($reportRows as $row) {
                    $reportMap[$row->part_no][$row->shift] = [
                        'qty_ok'      => (int) $row->qty_ok,
                        'prod_start'  => $row->prod_start,
                        'prod_finish' => $row->prod_finish,
                    ];
                }
            }

            // ── Helper: hitung diff menit dari HH:MM ──
            $diffMinutes = function($start, $finish) {
                if (!$start || !$finish) return 0;
                try {
                    [$sh, $sm] = array_map('intval', explode(':', $start));
                    [$fh, $fm] = array_map('intval', explode(':', $finish));
                    $diff = ($fh * 60 + $fm) - ($sh * 60 + $sm);
                    return $diff > 0 ? $diff : 0;
                } catch (\Throwable $e) {
                    return 0;
                }
            };

            // ── SummaryData ──
            $totalBoxPlan     = 0;
            $totalBoxActual   = 0;
            $totalStrokePlan  = 0;
            $totalStrokeActual= 0;
            $totalDandoriPlan = 0;
            $totalDandoriActual = 0;
            $totalGsphPlan    = 0;
            $totalWtPlan      = 0;
            $totalWtActual    = 0; // dalam menit, konversi ke jam di akhir

            foreach ($parts as $part) {
                if (!$stockMap->has($part->part_no_child)) continue;

                $stock    = $stockMap[$part->part_no_child];
                $calcProd = (int)   $stock->calc_prod;
                $ltProd   = (float) ($stock->lt_prod  ?? 0);
                $qtyKbn   = (int)   $stock->qty_kbn;
                $qtyCat   = (int)   ($part->qty_category ?? 0);
                $category = strtolower(trim($part->category ?? ''));

                // ── box_plan ──
                $totalBoxPlan += $calcProd;

                // ── stroke_plan ──
                $plan = $calcProd * $qtyKbn;
                if ($category === 'shoot' && $qtyCat > 0) {
                    $plan = $plan * $qtyCat;
                } elseif ($category === 'cavity' && $qtyCat > 0) {
                    $plan = (int) round($plan / $qtyCat);
                }
                $totalStrokePlan += $plan;

                // ── dandori_plan ──
                if ($plan >= 1) $totalDandoriPlan++;

                // ── gsph_plan ──
                $gsph = 0;
                if ($ltProd > 0) {
                    $gsph = round((60 / $ltProd) * $qtyKbn * $qtyCat);
                }
                $totalGsphPlan += $gsph;

                // ── wt_plan ──
                if ($plan > 0 && $gsph > 0) {
                    $totalWtPlan += round($plan / $gsph, 2);
                }

                // ── actual per part ──
                $dataS1       = $reportMap[$part->part_no_child][1] ?? null;
                $dataS2       = $reportMap[$part->part_no_child][2] ?? null;
                $kbnForActual = $qtyKbn;

                $actualS1 = ($dataS1 !== null && $kbnForActual > 0)
                    ? round($dataS1['qty_ok'] / $kbnForActual, 2) : null;
                $actualS2 = ($dataS2 !== null && $kbnForActual > 0)
                    ? round($dataS2['qty_ok'] / $kbnForActual, 2) : null;

                // box_actual = actualS1 + actualS2
                $actualTotal = ($actualS1 !== null || $actualS2 !== null)
                    ? round(($actualS1 ?? 0) + ($actualS2 ?? 0), 2) : null;
                if ($actualTotal !== null) $totalBoxActual += $actualTotal;

                // stroke_actual = actualTotal × qty_kbn
                if ($actualTotal !== null && $kbnForActual > 0) {
                    $totalStrokeActual += round($actualTotal * $kbnForActual, 2);
                }

                // dandori_actual = D shift1 + D shift2
                $dS1 = $dataS1 ? ($dataS1['prod_finish'] ? 1 : 0) : 0;
                $dS2 = $dataS2 ? ($dataS2['prod_finish'] ? 1 : 0) : 0;
                $totalDandoriActual += ($dS1 + $dS2);

                // wt_actual dalam menit
                $diff1 = $diffMinutes($dataS1['prod_start'] ?? null, $dataS1['prod_finish'] ?? null);
                $diff2 = $diffMinutes($dataS2['prod_start'] ?? null, $dataS2['prod_finish'] ?? null);
                $totalWtActual += ($diff1 + $diff2);
            }

            // wt_actual konversi menit → jam
            $wtActualHours = $totalWtActual > 0 ? round($totalWtActual / 60, 2) : null;

            // gsph_actual = total stroke_actual ÷ total wt_actual (jam)
            $gsphActualSummary = null;
            if ($totalStrokeActual > 0 && $wtActualHours > 0) {
                $gsphActualSummary = round($totalStrokeActual / $wtActualHours, 2);
            }

            $summaryData['box_plan']       = $totalBoxPlan      > 0 ? $totalBoxPlan                : '';
            $summaryData['box_actual']     = $totalBoxActual    > 0 ? round($totalBoxActual, 2)    : '';
            $summaryData['stroke_plan']    = $totalStrokePlan   > 0 ? $totalStrokePlan             : '';
            $summaryData['stroke_actual']  = $totalStrokeActual > 0 ? round($totalStrokeActual, 2) : '';
            $summaryData['dandori_plan']   = $totalDandoriPlan  > 0 ? $totalDandoriPlan            : '';
            $summaryData['dandori_actual'] = $totalDandoriActual> 0 ? $totalDandoriActual          : '';
            $summaryData['gsph_plan']      = $totalGsphPlan     > 0 ? $totalGsphPlan              : '';
            $summaryData['gsph_actual']    = $gsphActualSummary ?? '';
            $summaryData['wt_plan']        = $totalWtPlan       > 0 ? round($totalWtPlan, 2)       : '';
            $summaryData['wt_actual']      = $wtActualHours     ?? '';
        }

        return view('report-produksi.index', compact(
            'lines',
            'selectedLine',
            'mesins',
            'selectedMesin',
            'parts',
            'stockMap',
            'reportMap',
            'summaryData'
        ));
    }
}