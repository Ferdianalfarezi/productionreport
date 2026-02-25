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

        // Default semua variable
        $parts       = collect();
        $stockMap    = collect();
        $reportMap   = collect();
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

            // StockMap
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

            // ReportMap
            $reportRows = ReportProduction::whereIn('part_no', $partNos)
                ->where('machine_no', $selectedMesin)
                ->select('part_no', DB::raw('SUM(qty_ok) as total_qty_ok'))
                ->groupBy('part_no')
                ->get();

            $reportMap = $reportRows->keyBy('part_no');
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