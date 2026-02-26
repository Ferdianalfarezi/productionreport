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
                ->get()
                ->unique('part_no_child')
                ->values();
                
        }

        if ($parts->isNotEmpty()) {
            $partNos = $parts->pluck('part_no_child')->unique()->values();
            
            // ── StockMap ──
            $stockRows = EPlanningStock::whereIn('part_no_child', $partNos)
                ->where('line', $selectedMesin)
                ->select(
                    'part_no_child',
                    DB::raw('AVG(calc_prod) as calc_prod'),   // ← rata-rata
                    DB::raw('MAX(stock_store) as stock_store'),
                    DB::raw('MAX(lt_prod) as lt_prod'),
                    DB::raw('MAX(qty_kbn) as qty_kbn'),
                    DB::raw('MAX(e_planning_id) as e_planning_id')
                )
                ->groupBy('part_no_child')
                ->get();

            $stockMap = $stockRows->keyBy('part_no_child');

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

            // ── Helper: hitung diff menit dari HH:MM (support lintas tengah malam) ──
            $diffMinutes = function ($start, $finish) {
                if (!$start || !$finish) return 0;
                try {
                    [$sh, $sm] = array_map('intval', explode(':', $start));
                    [$fh, $fm] = array_map('intval', explode(':', $finish));
                    $diff = ($fh * 60 + $fm) - ($sh * 60 + $sm);
                    if ($diff < 0) $diff += 1440; // lintas tengah malam
                    return $diff > 0 ? $diff : 0;
                } catch (\Throwable $e) {
                    return 0;
                }
            };

            // ── SummaryData ──
            $totalBoxPlan       = 0;
            $totalBoxActual     = 0;
            $totalStrokePlan    = 0;
            $totalStrokeActual  = 0;
            $totalDandoriPlan   = 0;
            $totalDandoriActual = 0;
            $totalGsphPlan      = 0;
            $gsphPlanCount      = 0;   // counter untuk rata-rata gsph_plan
            $totalGsphActualSum = 0;   // akumulasi gsph_actual per part
            $gsphActualCount    = 0;   // counter untuk rata-rata gsph_actual
            $totalWtPlan        = 0;
            $totalWtActual      = 0;   // dalam menit, konversi ke jam di akhir

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
                if ($gsph > 0) $gsphPlanCount++;

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
                $strokePart = 0;
                if ($actualTotal !== null && $kbnForActual > 0) {
                    $strokePart = round($actualTotal * $kbnForActual, 2);
                    $totalStrokeActual += $strokePart;
                }

                // dandori_actual = D shift1 + D shift2
                $dS1 = $dataS1 ? ($dataS1['prod_finish'] ? 1 : 0) : 0;
                $dS2 = $dataS2 ? ($dataS2['prod_finish'] ? 1 : 0) : 0;
                $totalDandoriActual += ($dS1 + $dS2);

                // wt_actual dalam menit (support lintas tengah malam)
                $diff1 = $diffMinutes($dataS1['prod_start'] ?? null, $dataS1['prod_finish'] ?? null);
                $diff2 = $diffMinutes($dataS2['prod_start'] ?? null, $dataS2['prod_finish'] ?? null);
                $wtPartMin = $diff1 + $diff2;
                $totalWtActual += $wtPartMin;

                // gsph_actual per part = stroke_actual / wt_actual (jam)
                if ($wtPartMin > 0 && $strokePart > 0) {
                    $gsphActualPart      = round($strokePart / ($wtPartMin / 60), 2);
                    $totalGsphActualSum += $gsphActualPart;
                    $gsphActualCount++;
                }
            }

            // wt_actual konversi menit → jam
            $wtActualHours = $totalWtActual > 0 ? round($totalWtActual / 60, 2) : null;

            // gsph_plan rata-rata
            $gsphPlanAvg = ($gsphPlanCount > 0)
                ? round($totalGsphPlan / $gsphPlanCount, 2) : null;

            // gsph_actual rata-rata
            $gsphActualAvg = ($gsphActualCount > 0)
                ? round($totalGsphActualSum / $gsphActualCount, 2) : null;

            $summaryData['box_plan']       = $totalBoxPlan       > 0 ? $totalBoxPlan                  : '';
            $summaryData['box_actual']     = $totalBoxActual     > 0 ? round($totalBoxActual, 2)       : '';
            $summaryData['stroke_plan']    = $totalStrokePlan    > 0 ? $totalStrokePlan                : '';
            $summaryData['stroke_actual']  = $totalStrokeActual  > 0 ? round($totalStrokeActual, 2)    : '';
            $summaryData['dandori_plan']   = $totalDandoriPlan   > 0 ? $totalDandoriPlan               : '';
            $summaryData['dandori_actual'] = $totalDandoriActual > 0 ? $totalDandoriActual             : '';
            $summaryData['gsph_plan']      = $gsphPlanAvg        ?? '';
            $summaryData['gsph_actual']    = $gsphActualAvg      ?? '';
            $summaryData['wt_plan']        = $totalWtPlan        > 0 ? round($totalWtPlan, 2)          : '';
            $summaryData['wt_actual']      = $wtActualHours      ?? '';
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