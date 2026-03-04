<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MesinController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\LineConfigController;
use App\Http\Controllers\ReportProduksiController;
use App\Http\Controllers\EPlanningImportController;
use App\Http\Controllers\BreakTimeController;

// Auth
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth.check')->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Mesin
    Route::get('/mesin', [MesinController::class, 'index'])->name('mesin.index');
    Route::post('/mesin', [MesinController::class, 'store'])->name('mesin.store');
    Route::get('/mesin/group/{lineMachine}', [MesinController::class, 'showGroup'])->name('mesin.showGroup');
    Route::get('/mesin/{id}/edit', [MesinController::class, 'edit'])->name('mesin.edit');
    Route::put('/mesin/{id}', [MesinController::class, 'update'])->name('mesin.update');
    Route::delete('/mesin/{id}', [MesinController::class, 'destroy'])->name('mesin.destroy');
    Route::post('/mesin/import', [MesinController::class, 'import'])->name('mesin.import');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::prefix('parts')->name('parts.')->middleware(['auth'])->group(function () {
        Route::get('/',            [PartController::class, 'index'])->name('index');
        Route::post('/',           [PartController::class, 'store'])->name('store');
        Route::get('/{part}/edit', [PartController::class, 'edit'])->name('edit');
        Route::put('/{part}',      [PartController::class, 'update'])->name('update');
        Route::delete('/{part}',   [PartController::class, 'destroy'])->name('destroy');
        Route::post('/import',     [PartController::class, 'import'])->name('import');
        Route::post('/import-cavity',  [PartController::class, 'importCavity'])->name('import.cavity');
    });

    Route::prefix('line-configs')->name('line-configs.')->group(function () {
        Route::get('/',                 [LineConfigController::class, 'index'])->name('index');
        Route::post('/',                [LineConfigController::class, 'store'])->name('store');
        Route::post('/import',          [LineConfigController::class, 'import'])->name('import');
        Route::post('/destroy-line',    [LineConfigController::class, 'destroyLine'])->name('destroyLine');
        Route::get('/{lineConfig}/edit',[LineConfigController::class, 'edit'])->name('edit');
        Route::put('/{lineConfig}',     [LineConfigController::class, 'update'])->name('update');
        Route::delete('/{lineConfig}',  [LineConfigController::class, 'destroy'])->name('destroy');
    });

    Route::get('/report-produksi', [ReportProduksiController::class, 'index'])
        ->name('report-produksi.index');

        Route::prefix('e-planning')->name('e-planning.')->group(function () {
        Route::get('/import', [EPlanningImportController::class, 'index'])->name('import');
        Route::post('/import', [EPlanningImportController::class, 'import']);
    });

    Route::resource('break-times', BreakTimeController::class)
    ->only(['index', 'store', 'update', 'destroy']);


});
