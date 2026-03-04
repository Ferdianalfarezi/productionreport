<?php

namespace App\Providers;

use App\Models\BreakTime;
use App\Models\EPlanningStock;
use App\Models\ReportProduction;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ── Break Times modal ──
        View::composer('break-times.index', function ($view) {
            $view->with('breakTimes', BreakTime::orderBy('shift')->orderBy('break_start')->get());
        });

        // ── Inject lastImport ke report-produksi ──
        View::composer('report-produksi.index', function ($view) {
            $view->with('lastImport', \App\Models\EPlanningStock::max('updated_at'));
        });

        // ── Import E-Planning modal ──
        View::composer('e-planning.import', function ($view) {
            $view->with([
                'totalRows'       => EPlanningStock::count(),
                'lastImport'      => EPlanningStock::max('updated_at'),
                'totalReportRows' => ReportProduction::count(),
                'lastReportImport'=> ReportProduction::max('updated_at'),
            ]);
        });
    }
}