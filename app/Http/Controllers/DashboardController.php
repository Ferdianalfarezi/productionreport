<?php

namespace App\Http\Controllers;

use App\Models\Mesin;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalMesin      = Mesin::count();
        $totalLineMachine = Mesin::distinct('line_machine')->count('line_machine');
        $totalLine        = Mesin::distinct('line')->count('line');
        $totalUser        = User::count();

        // Stats per line (top lines by machine count)
        $lineStats = Mesin::selectRaw('line, COUNT(*) as total, AVG(gsph_theory) as avg_gsph')
            ->groupBy('line')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Recent updates
        $recentMesin = Mesin::orderByDesc('update_time')->limit(5)->get();

        return view('dashboard.index', compact(
            'totalMesin', 'totalLineMachine', 'totalLine', 'totalUser',
            'lineStats', 'recentMesin'
        ));
    }
}
