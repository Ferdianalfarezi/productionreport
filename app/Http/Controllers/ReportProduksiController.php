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

            // ── SummaryData ──
            $totalBoxPlan    = 0;
            $totalStrokePlan = 0;
            $totalDandori    = 0;
            $totalGsph       = 0;
            $totalWt         = 0;

            foreach ($parts as $part) {
                if (!$stockMap->has($part->part_no_child)) continue;

                $stock    = $stockMap[$part->part_no_child];
                $calcProd = (int)   $stock->calc_prod;
                $ltProd   = (float) ($stock->lt_prod  ?? 0);
                $qtyKbn   = (int)   $stock->qty_kbn;
                $qtyCat   = (int)   ($part->qty_category ?? 0);
                $category = strtolower(trim($part->category ?? ''));

                // box_plan
                $totalBoxPlan += $calcProd;

                // stroke_plan (= plan per part)
                $plan = $calcProd * $qtyKbn;
                if ($category === 'shoot' && $qtyCat > 0) {
                    $plan = $plan * $qtyCat;
                } elseif ($category === 'cavity' && $qtyCat > 0) {
                    $plan = (int) round($plan / $qtyCat);
                }
                $totalStrokePlan += $plan;

                // dandori_plan: 1 jika plan >= 1
                if ($plan >= 1) {
                    $totalDandori += 1;
                }

                // gsph per part
                $gsph = 0;
                if ($ltProd > 0) {
                    $gsph = round((60 / $ltProd) * $qtyKbn * $qtyCat);
                }
                $totalGsph += $gsph;

                // wt per part
                if ($plan > 0 && $gsph > 0) {
                    $totalWt += round($plan / $gsph, 2);
                }
            }

            $summaryData['box_plan']    = $totalBoxPlan    > 0 ? $totalBoxPlan              : '';
            $summaryData['stroke_plan'] = $totalStrokePlan > 0 ? $totalStrokePlan           : '';
            $summaryData['dandori_plan']= $totalDandori    > 0 ? $totalDandori              : '';
            $summaryData['gsph_plan']   = $totalGsph       > 0 ? $totalGsph                : '';
            $summaryData['wt_plan']     = $totalWt         > 0 ? round($totalWt, 2)        : '';
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