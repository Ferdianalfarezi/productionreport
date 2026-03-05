<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\LineConfig;
use App\Models\EPlanningStock;
use App\Models\ReportProduction;
use App\Models\BreakTime;
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

        // ── Tanggal tersedia untuk arsip ──
        // Gabungkan tanggal dari kedua tabel, unik, descending
        $eDateList = EPlanningStock::availableImportDates();
        $rDateList = ReportProduction::availableImportDates();
        $availableDates = $eDateList->merge($rDateList)
            ->unique()
            ->sort()
            ->values()
            ->sortDesc()
            ->values();

        // Default: tanggal terbaru
        $latestDate    = $availableDates->first();                            // null jika belum ada data
        $selectedDate  = $request->get('import_date', $latestDate);          // dari query string atau default

        // Pastikan tanggal yang dipilih valid (ada di daftar), fallback ke terbaru
        if ($selectedDate && !$availableDates->contains($selectedDate)) {
            $selectedDate = $latestDate;
        }

        // Label display (d/m/Y)
        $selectedDateLabel = $selectedDate
            ? \Carbon\Carbon::parse($selectedDate)->format('d/m/Y')
            : null;

        // ── Break Times ──
        $breakTimes   = BreakTime::active()->get();
        $breakByShift = [
            1 => $breakTimes->filter(fn($b) => is_null($b->shift) || $b->shift === 1)->values(),
            2 => $breakTimes->filter(fn($b) => is_null($b->shift) || $b->shift === 2)->values(),
        ];

        $parts       = collect();
        $stockMap    = collect();
        $reportMap   = [];
        $summaryData = [];

        if ($selectedMesin) {
            // ── Parts: filter by import_date ──
            $validPartNos = EPlanningStock::where('line', $selectedMesin)
                ->when($selectedDate, fn($q) => $q->whereDate('import_date', $selectedDate))
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

            // ── StockMap: filter by import_date ──
            $stockRows = EPlanningStock::whereIn('part_no_child', $partNos)
                ->where('line', $selectedMesin)
                ->when($selectedDate, fn($q) => $q->whereDate('import_date', $selectedDate))
                ->select(
                    'part_no_child',
                    DB::raw('AVG(calc_prod) as calc_prod'),
                    DB::raw('MAX(stock_store) as stock_store'),
                    DB::raw('MAX(lt_prod) as lt_prod'),
                    DB::raw('MAX(qty_kbn) as qty_kbn'),
                    DB::raw('MAX(e_planning_id) as e_planning_id')
                )
                ->groupBy('part_no_child')
                ->get();

            $stockMap = $stockRows->keyBy('part_no_child');

            // ── ReportMap: filter by import_date ──
            // Ambil report_id terbesar per (part_no, shift) pada import_date yang dipilih
            $latestIds = ReportProduction::whereIn('part_no', $partNos)
                ->where('machine_no', $selectedMesin)
                ->whereIn('shift', [1, 2])
                ->when($selectedDate, fn($q) => $q->whereDate('import_date', $selectedDate))
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

            // ── Helper: hitung diff menit dengan memotong break times ──
            $diffMinutes = function ($start, $finish, $breaks) {
                if (!$start || !$finish) return 0;
                try {
                    [$sh, $sm] = array_map('intval', explode(':', $start));
                    [$fh, $fm] = array_map('intval', explode(':', $finish));

                    $startMin  = $sh * 60 + $sm;
                    $finishMin = $fh * 60 + $fm;

                    if ($finishMin < $startMin) $finishMin += 1440;

                    $raw = $finishMin - $startMin;
                    if ($raw <= 0) return 0;

                    $cut = 0;
                    foreach ($breaks as $br) {
                        $bS = $br->break_start_min;
                        $bE = $br->break_end_min;

                        $overlapStart = max($startMin, $bS);
                        $overlapEnd   = min($finishMin, $bE);

                        if ($overlapEnd > $overlapStart) {
                            $cut += ($overlapEnd - $overlapStart);
                        }
                    }

                    return max(0, $raw - $cut);

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
            $gsphPlanCount      = 0;
            $totalGsphActualSum = 0;
            $gsphActualCount    = 0;
            $totalWtPlan        = 0;
            $totalWtActual      = 0;

            foreach ($parts as $part) {
                if (!$stockMap->has($part->part_no_child)) continue;

                $stock    = $stockMap[$part->part_no_child];
                $calcProd = (int)   $stock->calc_prod;
                $ltProd   = (float) ($stock->lt_prod  ?? 0);
                $qtyKbn   = (int)   $stock->qty_kbn;
                $qtyCat   = (int)   ($part->qty_category ?? 0);
                $category = strtolower(trim($part->category ?? ''));

                $totalBoxPlan += $calcProd;

                $plan = $calcProd * $qtyKbn;
                if ($category === 'shoot' && $qtyCat > 0) {
                    $plan = $plan * $qtyCat;
                } elseif ($category === 'cavity' && $qtyCat > 0) {
                    $plan = (int) round($plan / $qtyCat);
                }
                $totalStrokePlan += $plan;

                if ($plan >= 1) $totalDandoriPlan++;

                $gsph = 0;
                if ($ltProd > 0) {
                    $gsph = round((60 / $ltProd) * $qtyKbn * $qtyCat);
                }
                $totalGsphPlan += $gsph;
                if ($gsph > 0) $gsphPlanCount++;

                if ($plan > 0 && $gsph > 0) {
                    $totalWtPlan += round($plan / $gsph, 2);
                }

                $dataS1       = $reportMap[$part->part_no_child][1] ?? null;
                $dataS2       = $reportMap[$part->part_no_child][2] ?? null;
                $kbnForActual = $qtyKbn;

                $actualS1 = ($dataS1 !== null && $kbnForActual > 0)
                    ? round($dataS1['qty_ok'] / $kbnForActual, 2) : null;
                $actualS2 = ($dataS2 !== null && $kbnForActual > 0)
                    ? round($dataS2['qty_ok'] / $kbnForActual, 2) : null;

                $actualTotal = ($actualS1 !== null || $actualS2 !== null)
                    ? round(($actualS1 ?? 0) + ($actualS2 ?? 0), 2) : null;
                if ($actualTotal !== null) $totalBoxActual += $actualTotal;

                $strokePart = 0;
                if ($actualTotal !== null && $kbnForActual > 0) {
                    $strokePart = round($actualTotal * $kbnForActual, 2);
                    $totalStrokeActual += $strokePart;
                }

                $dS1 = $dataS1 ? ($dataS1['prod_finish'] ? 1 : 0) : 0;
                $dS2 = $dataS2 ? ($dataS2['prod_finish'] ? 1 : 0) : 0;
                $totalDandoriActual += ($dS1 + $dS2);

                $diff1     = $diffMinutes($dataS1['prod_start'] ?? null, $dataS1['prod_finish'] ?? null, $breakByShift[1]);
                $diff2     = $diffMinutes($dataS2['prod_start'] ?? null, $dataS2['prod_finish'] ?? null, $breakByShift[2]);
                $wtPartMin = $diff1 + $diff2;
                $totalWtActual += $wtPartMin;

                if ($wtPartMin > 0 && $strokePart > 0) {
                    $gsphActualPart      = round($strokePart / ($wtPartMin / 60), 2);
                    $totalGsphActualSum += $gsphActualPart;
                    $gsphActualCount++;
                }
            }

            if ($totalWtActual > 0) {
                $wtJam         = intdiv((int) $totalWtActual, 60);
                $wtMenit       = (int) $totalWtActual % 60;
                $wtActualHours = (float) ($wtJam . '.' . str_pad($wtMenit, 2, '0', STR_PAD_LEFT));
            } else {
                $wtActualHours = null;
            }

            $gsphPlanAvg   = $gsphPlanCount  > 0 ? round($totalGsphPlan / $gsphPlanCount, 2)        : null;
            $gsphActualAvg = $gsphActualCount > 0 ? round($totalGsphActualSum / $gsphActualCount, 2) : null;

            $summaryData['box_plan']       = $totalBoxPlan       > 0 ? $totalBoxPlan                : '';
            $summaryData['box_actual']     = $totalBoxActual     > 0 ? round($totalBoxActual, 2)     : '';
            $summaryData['stroke_plan']    = $totalStrokePlan    > 0 ? $totalStrokePlan              : '';
            $summaryData['stroke_actual']  = $totalStrokeActual  > 0 ? round($totalStrokeActual, 2)  : '';
            $summaryData['dandori_plan']   = $totalDandoriPlan   > 0 ? $totalDandoriPlan             : '';
            $summaryData['dandori_actual'] = $totalDandoriActual > 0 ? $totalDandoriActual           : '';
            $summaryData['gsph_plan']      = $gsphPlanAvg        ?? '';
            $summaryData['gsph_actual']    = $gsphActualAvg      ?? '';
            $summaryData['wt_plan']        = $totalWtPlan        > 0 ? round($totalWtPlan, 2)        : '';
            $summaryData['wt_actual']      = $wtActualHours      ?? '';
        }

        // lastImport: waktu upload terakhir untuk tanggal yang sedang dipilih
        $lastImport = EPlanningStock::when($selectedDate, fn($q) => $q->whereDate('import_date', $selectedDate))
            ->max('created_at');

        return view('report-produksi.index', compact(
            'lines',
            'selectedLine',
            'mesins',
            'selectedMesin',
            'parts',
            'stockMap',
            'reportMap',
            'summaryData',
            'breakByShift',
            'breakTimes',
            'availableDates',
            'selectedDate',
            'selectedDateLabel',
            'lastImport',
        ));
    }
}