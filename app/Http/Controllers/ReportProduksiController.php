<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\LineConfig;
use Illuminate\Http\Request;

class ReportProduksiController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua line yang unik untuk dropdown
        $lines = LineConfig::select('line')->distinct()->orderBy('line')->pluck('line');

        $selectedLine = $request->get('line', $lines->first());
        $selectedMesin = $request->get('mesin');

        // Ambil semua mesin berdasarkan line yang dipilih
        $mesins = LineConfig::where('line', $selectedLine)
            ->orderBy('mesin')
            ->pluck('mesin');

        // Default ke mesin pertama kalau belum dipilih
        if (!$selectedMesin && $mesins->isNotEmpty()) {
            $selectedMesin = $mesins->first();
        }

        // Ambil parts berdasarkan mesin yang dipilih
        $parts = collect();
        if ($selectedMesin) {
            $parts = Part::where('line', $selectedMesin)
                ->orderBy('part_no_child')
                ->get();
        }

        return view('report-produksi.index', compact(
            'lines',
            'selectedLine',
            'mesins',
            'selectedMesin',
            'parts'
        ));
    }
}