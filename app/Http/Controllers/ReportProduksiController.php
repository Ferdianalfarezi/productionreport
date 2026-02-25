<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\LineConfig;
use App\Models\EPlanningStock;
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

        $parts = collect();
        if ($selectedMesin) {
            $validPartNos = EPlanningStock::where('line', $selectedMesin)
                ->pluck('part_no_child')
                ->unique();

            $parts = Part::where('line', $selectedMesin)
                ->whereIn('part_no_child', $validPartNos)
                ->orderBy('part_no_child')
                ->get();
        }

        $stockMap = collect();
        if ($parts->isNotEmpty()) {
            $partNos = $parts->pluck('part_no_child')->unique()->values();

            // Ambil e_planning_id terbesar per part_no_child
            $stockData = EPlanningStock::whereIn('part_no_child', $partNos)
                ->where('line', $selectedMesin)
                ->select('part_no_child', DB::raw('MAX(e_planning_id) as max_id'))
                ->groupBy('part_no_child')
                ->get();

            if ($stockData->isNotEmpty()) {
                $maxIds = $stockData->pluck('max_id')->toArray();

                // Ambil stock_store + calc_prod dari baris terbaru
                $stockRows = EPlanningStock::whereIn('e_planning_id', $maxIds)
                ->where('line', $selectedMesin)
                ->get(['part_no_child', 'stock_store', 'calc_prod', 'lt_prod', 'qty_kbn', 'e_planning_id']);

                $stockMap = $stockRows->keyBy('part_no_child');
            }
        }

        return view('report-produksi.index', compact(
            'lines',
            'selectedLine',
            'mesins',
            'selectedMesin',
            'parts',
            'stockMap'
        ));
    }
}