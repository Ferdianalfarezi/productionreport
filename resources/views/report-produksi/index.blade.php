{{-- resources/views/report-produksi/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Report Produksi')

@push('styles')
<style>
    * { box-sizing: border-box; }
    .page-wrapper { padding: 16px; background: #f0f0f0; min-height: 100vh; }

    /* ── Toolbar ── */
    .toolbar { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; flex-wrap: wrap; }
    .toolbar label { font-size: 13px; font-weight: 600; color: #333; }
    .select-line {
        appearance: none;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23555' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center;
        border: 1.5px solid #aaa; border-radius: 6px; padding: 6px 30px 6px 12px;
        font-size: 13px; font-weight: 600; color: #1565C0; cursor: pointer; min-width: 180px;
    }
    .select-line:focus { outline: none; border-color: #1565C0; box-shadow: 0 0 0 3px rgba(21,101,192,.15); }

    .btn-break-cfg {
        margin-left: auto;
        padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600;
        background: #fff; color: #1565C0; border: 1.5px solid #1565C0; cursor: pointer;
        text-decoration: none; transition: all .15s;
    }
    .btn-break-cfg:hover { background: #e3f2fd; text-decoration: none; }

    /* ── Mesin Bar ── */
    .mesin-bar {
        display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
        background: #fff; border: 1.5px solid #ddd; border-radius: 10px;
        padding: 8px 14px; margin-bottom: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,.08);
    }
    .line-badge { background: #1565C0; color: #fff; font-size: 13px; font-weight: 700; padding: 5px 14px; border-radius: 20px; margin-right: 4px; }
    .mesin-pill {
        display: inline-flex; align-items: center; padding: 5px 14px;
        border-radius: 20px; font-size: 13px; font-weight: 500;
        background: #f1f5f9; color: #334155; border: 1.5px solid #e2e8f0;
        cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap;
    }
    .mesin-pill:hover { background: #e2e8f0; text-decoration: none; }
    .mesin-pill.active { background: #1565C0; color: #fff; border-color: #0d47a1; font-weight: 700; }
    .mesin-pill.tambah { background: transparent; border: 1.5px dashed #aaa; color: #888; font-size: 12px; }
    .mesin-pill.tambah:hover { border-color: #1565C0; color: #1565C0; background: #e3f2fd; }

    /* ── Break Info Bar ── */
    .break-info-bar {
        display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        background: #FFF8E1; border: 1.5px solid #FFD54F; border-radius: 8px;
        padding: 8px 14px; margin-bottom: 14px; font-size: 12px;
    }
    .break-info-bar .label { font-weight: 700; color: #E65100; white-space: nowrap; }
    .break-chip {
        display: inline-flex; align-items: center; gap: 4px;
        background: #fff; border: 1px solid #FFD54F; border-radius: 14px;
        padding: 3px 10px; font-size: 11.5px; color: #555;
    }
    .break-chip .shift-badge {
        background: #FFD54F; color: #333; border-radius: 10px;
        padding: 1px 6px; font-size: 10px; font-weight: 700;
    }

    /* ── Report Card ── */
    .report-card { background: #fff; border: 2px solid #999; border-radius: 2px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.12); }
    .table-wrapper { overflow-x: auto; }

    /* ── Table ── */
    .report-table { width: 100%; border-collapse: collapse; font-size: 11.5px; min-width: 1400px; }
    .report-table th, .report-table td { border: 1px solid #aaa; padding: 3px 5px; text-align: center; white-space: nowrap; }

    .bg-w            { background: #fff !important; color: #111; }
    .th-blue         { background: #90CAF9; font-weight: 700; color: #0d47a1; }
    .th-yellow-hdr   { background: #FFF176; font-weight: 700; color: #555; }
    .th-light-blue   { background: #BBDEFB; font-weight: 600; color: #0d47a1; }
    .th-light-green  { background: #C8E6C9; font-weight: 600; color: #1b5e20; }
    .th-light-yellow { background: #FFF9C4; font-weight: 600; color: #555; }

    .td-mesin { background: #FFFF00 !important; font-size: 26px; font-weight: 900; color: #111; letter-spacing: 1px; vertical-align: middle; text-align: center; }

    .col-no    { width: 30px; }
    .col-part  { text-align: left !important; min-width: 130px; padding-left: 8px !important; }
    .col-stock { width: 58px; }
    td.col-part { font-weight: 600; }

    .report-table tbody tr td         { background: #fff; color: #333; }
    .report-table tbody tr.row-data td { background: #E8F5E9; }
    .report-table tbody tr.row-data:nth-child(even) td { background: #DCEDC8; }
    .report-table tbody tr:hover td   { background: #E3F2FD !important; }

    td.c-green  { background: #A5D6A7 !important; font-weight: 700; color: #1b5e20; }
    td.c-yellow { background: #FFFF00 !important; font-weight: 700; color: #333; }
    td.c-red    { background: #FFCDD2 !important; font-weight: 700; color: #b71c1c; }
    td.c-white  { background: #fff !important; }

    /* ── Total row ── */
    .tr-total td { background: #E3F2FD !important; font-weight: 700; color: #0d47a1; border-top: 2px solid #1565C0; }
    .tr-total td.c-green  { background: #A5D6A7 !important; color: #1b5e20; }
    .tr-total td.c-yellow { background: #FFFF00 !important; color: #333; }

    /* ── Pct cell ── */
    .pct-good { color: #1b5e20; font-weight: 700; }
    .pct-bad  { color: #b71c1c; font-weight: 700; }

    .empty-state { padding: 60px; text-align: center; color: #999; font-size: 13px; }
</style>
@endpush

@section('content')
<div class="page-wrapper">

    {{-- ══ TOOLBAR ══ --}}
    <div class="toolbar">
        <label for="lineSelect">Line:</label>
        <select class="select-line" id="lineSelect" onchange="changeLine(this.value)">
            @foreach($lines as $line)
                <option value="{{ $line }}" {{ $selectedLine == $line ? 'selected' : '' }}>{{ $line }}</option>
            @endforeach
        </select>
        <a href="{{ route('break-times.index') }}" class="btn-break-cfg">⏱ Konfigurasi Break Time</a>
    </div>

    {{-- ══ BREAK INFO BAR ══ --}}
    @php
        $allBreaks = collect($breakByShift[1] ?? [])->merge($breakByShift[2] ?? [])
            ->unique('id')->sortBy('break_start');
    @endphp
    @if($allBreaks->isNotEmpty())
    <div class="break-info-bar">
        <span class="label">⏸ Break aktif:</span>
        @foreach($allBreaks as $br)
            @php
                [$bh, $bm] = array_map('intval', explode(':', $br->break_start));
                $endMin = $bh * 60 + $bm + $br->duration;
                $endStr = sprintf('%02d:%02d', intdiv($endMin, 60) % 24, $endMin % 60);
                $shiftLabel = $br->shift ? 'S'.$br->shift : 'ALL';
            @endphp
            <span class="break-chip">
                <span class="shift-badge">{{ $shiftLabel }}</span>
                {{ $br->break_name }} &nbsp;
                {{ \Str::substr($br->break_start, 0, 5) }} – {{ $endStr }}
                ({{ $br->duration }}m)
            </span>
        @endforeach
    </div>
    @endif

    {{-- ══ MESIN BAR ══ --}}
    <div class="mesin-bar">
        <span class="line-badge">{{ $selectedLine }}</span>
        @forelse($mesins as $mesin)
            <a href="{{ route('report-produksi.index', ['line' => $selectedLine, 'mesin' => $mesin]) }}"
               class="mesin-pill {{ $selectedMesin == $mesin ? 'active' : '' }}">{{ $mesin }}</a>
        @empty
            <span style="font-size:13px;color:#999;">Belum ada mesin.</span>
        @endforelse
        <a href="#" class="mesin-pill tambah" onclick="tambahMesin();return false;">+ Tambah</a>
    </div>

    @if($selectedMesin)
    <div class="report-card">
        <div class="table-wrapper">
        <table class="report-table">
            <thead>
                {{-- ══ ROW 1 ══ --}}
                <tr>
                    <th class="bg-w" colspan="3" style="text-align:left;padding-left:6px;font-weight:700;">
                        Line &nbsp;<span style="color:#1565C0;">{{ $selectedLine }}</span>
                    </th>
                    <th colspan="2" class="th-blue">BOX</th>
                    <th colspan="2" class="th-blue">Stroke</th>
                    <th colspan="2" class="th-blue">Dandori</th>
                    <th colspan="2" class="th-blue">GSPH</th>
                    <th colspan="2" class="th-blue">Working Time</th>
                    <th colspan="7" class="bg-w" style="font-size:18px;font-weight:800;letter-spacing:1px;">DETAIL REPORT PRODUKSI</th>
                    <th colspan="2" class="th-yellow-hdr" style="font-size:10.5px;line-height:1.4;" rowspan="3">DANDORI<br>DAN CAVITY</th>
                </tr>

                {{-- ══ ROW 2 ══ --}}
                <tr>
                    <th class="bg-w" colspan="3" style="font-weight:700;">Machine</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-green">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-green">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-green">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-green">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-green">Actual</th>
                    <th colspan="4" class="th-light-green" rowspan="2" style="vertical-align:middle;">Shift 1</th>
                    <th colspan="3" class="th-light-green" rowspan="2" style="vertical-align:middle;">Shift 2</th>
                </tr>

                {{-- ══ ROW 3 ══ --}}
                <tr>
                    <td class="td-mesin" colspan="3">{{ $selectedMesin }}</td>
                    <td class="bg-w" style="font-weight:700;">{{ $summaryData['box_plan']       ?? '' }}</td>
                    <td class="bg-w" style="font-weight:700;">{{ $summaryData['box_actual']     ?? '' }}</td>
                    <td class="bg-w" style="font-weight:700;">{{ $summaryData['stroke_plan']    ?? '' }}</td>
                    <td class="bg-w" style="font-weight:700;">{{ $summaryData['stroke_actual']  ?? '' }}</td>
                    <td class="bg-w" style="font-weight:700;">{{ $summaryData['dandori_plan']   ?? '' }}</td>
                    <td class="bg-w" style="font-weight:700;">{{ $summaryData['dandori_actual'] ?? '' }}</td>
                    <td class="bg-w" style="font-weight:700;">{{ $summaryData['gsph_plan']      ?? '' }}</td>
                    <td class="bg-w" style="font-weight:700;">{{ $summaryData['gsph_actual']    ?? '' }}</td>
                    <td class="bg-w" style="font-weight:700;">{{ $summaryData['wt_plan']        ?? '' }}</td>
                    <td class="bg-w" style="font-weight:700;">{{ $summaryData['wt_actual']      ?? '' }}</td>
                </tr>

                {{-- ══ ROW 4 ══ --}}
                <tr>
                    <th class="col-no bg-w" style="font-weight:700;">No</th>
                    <th class="col-part bg-w" style="font-weight:700;text-align:left;">Part_No</th>
                    <th class="col-stock bg-w" style="font-weight:700;">Stock_NP</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-green">Qty</th>
                    <th class="th-light-green">Start</th>
                    <th class="th-light-green">Finish</th>
                    <th class="th-light-green">D</th>
                    <th class="th-light-green">Qty</th>
                    <th class="th-light-green">Start</th>
                    <th class="th-light-green">Finish</th>
                    <th class="th-light-yellow">D</th>
                    <th class="th-light-yellow">Shoot</th>
                </tr>
            </thead>
            <tbody>
                @php
                    /* ══════════════════════════════════════════════════════════════
                       HELPER: hitung selisih menit dengan memotong break times
                       ══════════════════════════════════════════════════════════════ */
                    $calcDiff = function (?string $start, ?string $finish, \Illuminate\Support\Collection $breaks): int {
                        if (!$start || !$finish) return 0;

                        try {
                            $toMin = function (string $t): int {
                                $p = array_map('intval', explode(':', $t));
                                return $p[0] * 60 + ($p[1] ?? 0);
                            };

                            $startMin  = $toMin($start);
                            $finishMin = $toMin($finish);

                            // lintas tengah malam
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

                    /* ── Break collections per shift ── */
                    $breaksS1 = $breakByShift[1] ?? collect();
                    $breaksS2 = $breakByShift[2] ?? collect();

                    /* ── Akumulator Total ── */
                    $totBoxPlan          = 0;
                    $totBoxActual        = 0;
                    $totStrokePlan       = 0;
                    $totStrokeActual     = 0;
                    $totDandoriPlan      = 0;
                    $totDandoriActual    = 0;
                    $totGsphPlanSum      = 0; $totGsphPlanCount    = 0;
                    $totGsphActualSum    = 0; $totGsphActualCount  = 0;
                    $totWtPlan           = 0;
                    $totWtActualMin      = 0;
                    $totShift1Qty        = 0;
                    $totShift2Qty        = 0;
                    $totDShift1          = 0;
                    $totDShift2          = 0;
                    $totShoot            = 0;
                @endphp

                @forelse($parts as $i => $part)
                    @php
                        /* ─── Plan (BOX) ─── */
                        $plan = null;
                        if ($stockMap->has($part->part_no_child)) {
                            $sm       = $stockMap[$part->part_no_child];
                            $calcProd = (int) $sm->calc_prod;
                            $plan     = $calcProd * (int) ($sm->qty_kbn ?? 1);

                            $category = strtolower(trim($part->category ?? ''));
                            $qtyCat   = (float) ($part->qty_category ?? 0);

                            if ($category === 'shoot' && $qtyCat > 0) {
                                $plan = (int) round($plan * $qtyCat);
                            } elseif ($category === 'cavity' && $qtyCat > 0) {
                                $plan = (int) round($plan / $qtyCat);
                            }
                        }

                        /* ─── Dandori Plan ─── */
                        $dandori = ($plan !== null && $plan >= 1) ? 1 : null;

                        /* ─── GSPH Plan ─── */
                        $gsph = null;
                        if ($stockMap->has($part->part_no_child)) {
                            $sm     = $stockMap[$part->part_no_child];
                            $ltProd = (float) ($sm->lt_prod ?? 0);
                            $qtyKbn = (float) ($sm->qty_kbn ?? 0);
                            $qtyCat = (float) ($part->qty_category ?? 0);
                            if ($ltProd > 0) {
                                $gsph = round((60 / $ltProd) * $qtyKbn * $qtyCat);
                            }
                        }

                        /* ─── Working Time Plan ─── */
                        $wt = ($plan !== null && $gsph !== null && $gsph > 0)
                            ? round($plan / $gsph, 2)
                            : null;

                        /* ─── Actual Shift 1 & 2 ─── */
                        $actualShift1 = null; $startShift1 = null; $finishShift1 = null;
                        $actualShift2 = null; $startShift2 = null; $finishShift2 = null;

                        if ($stockMap->has($part->part_no_child)) {
                            $kbn   = (int) ($stockMap[$part->part_no_child]->qty_kbn ?? 1);
                            $dataS1 = $reportMap[$part->part_no_child][1] ?? null;
                            $dataS2 = $reportMap[$part->part_no_child][2] ?? null;

                            if ($dataS1 !== null && $kbn > 0) {
                                $actualShift1 = round($dataS1['qty_ok'] / $kbn, 2);
                                $startShift1  = $dataS1['prod_start'];
                                $finishShift1 = $dataS1['prod_finish'];
                            }
                            if ($dataS2 !== null && $kbn > 0) {
                                $actualShift2 = round($dataS2['qty_ok'] / $kbn, 2);
                                $startShift2  = $dataS2['prod_start'];
                                $finishShift2 = $dataS2['prod_finish'];
                            }
                        }

                        /* ─── Total Actual (BOX) ─── */
                        $actualTotal = null;
                        if ($actualShift1 !== null || $actualShift2 !== null) {
                            $actualTotal = round(($actualShift1 ?? 0) + ($actualShift2 ?? 0), 2);
                        }

                        /* ─── Stroke Actual ─── */
                        $strokeActual = null;
                        if ($actualTotal !== null && $stockMap->has($part->part_no_child)) {
                            $kbn = (int) ($stockMap[$part->part_no_child]->qty_kbn ?? 1);
                            if ($kbn > 0) {
                                $strokeActual = round($actualTotal * $kbn, 2);
                            }
                        }

                        /* ─── Dandori Actual ─── */
                        $dandoriActual = ($finishShift1 ? 1 : 0) + ($finishShift2 ? 1 : 0);

                        /* ─── WT Actual (dengan break cut) ─── */
                        $diff1        = $calcDiff($startShift1, $finishShift1, $breaksS1);
                        $diff2        = $calcDiff($startShift2, $finishShift2, $breaksS2);
                        $totalMinutes = $diff1 + $diff2;

                        // format HH.MM — 135 menit → 2.15, 10 menit → 0.10
                        if ($totalMinutes > 0) {
                            $_wtJam   = intdiv((int) $totalMinutes, 60);
                            $_wtMnt   = (int) $totalMinutes % 60;
                            $wtActual = (float) ($_wtJam . '.' . str_pad($_wtMnt, 2, '0', STR_PAD_LEFT));
                        } else {
                            $wtActual = null;
                        }

                        /* ─── GSPH Actual — pakai menit real bukan HH.MM ─── */
                        $gsphActual = null;
                        if ($strokeActual !== null && $totalMinutes > 0) {
                            $gsphActual = round($strokeActual / ($totalMinutes / 60), 2);
                        }

                        /* ─── Stroke Plan ─── */
                        $strokePlan = null;
                        if ($plan !== null && $stockMap->has($part->part_no_child)) {
                            $kbn = (int) ($stockMap[$part->part_no_child]->qty_kbn ?? 1);
                            $strokePlan = $plan * $kbn;
                        }

                        /* ─── Warna Actual ─── */
                        $classA1 = $actualShift1 !== null
                            ? ($plan !== null && $actualShift1 >= $plan ? 'c-green' : 'c-yellow')
                            : 'c-white';
                        $classA2 = $actualShift2 !== null
                            ? ($plan !== null && $actualShift2 >= $plan ? 'c-green' : 'c-yellow')
                            : 'c-white';

                        /* ─── Akumulasi Total ─── */
                        if ($stockMap->has($part->part_no_child)) {
                            $totBoxPlan += (int) $stockMap[$part->part_no_child]->calc_prod;
                        }
                        $totBoxActual     += $actualTotal   ?? 0;
                        $totStrokePlan    += $strokePlan    ?? 0;
                        $totStrokeActual  += $strokeActual  ?? 0;
                        $totDandoriPlan   += $dandori       ?? 0;
                        $totDandoriActual += $dandoriActual;

                        if ($gsph !== null && $gsph > 0) {
                            $totGsphPlanSum += $gsph;
                            $totGsphPlanCount++;
                        }
                        if ($gsphActual !== null && $gsphActual > 0) {
                            $totGsphActualSum += $gsphActual;
                            $totGsphActualCount++;
                        }

                        $totWtPlan      += $wt             ?? 0;
                        $totWtActualMin += $totalMinutes;
                        $totShift1Qty   += $actualShift1   ?? 0;
                        $totShift2Qty   += $actualShift2   ?? 0;
                        $totDShift1     += ($finishShift1 ? 1 : 0);
                        $totDShift2     += ($finishShift2 ? 1 : 0);
                        $totShoot       += 1;
                    @endphp

                    <tr class="row-data">
                        <td class="col-no">{{ $loop->iteration }}</td>
                        <td class="col-part">{{ $part->part_no_child }}</td>
                        <td class="col-stock">
                            {{ $stockMap->has($part->part_no_child) ? $stockMap[$part->part_no_child]->stock_store : '' }}
                        </td>

                        {{-- BOX --}}
                        <td>{{ $plan ?? '' }}</td>
                        <td>{{ $actualTotal ?? '' }}</td>

                        {{-- Stroke --}}
                        <td>{{ $strokePlan ?? '' }}</td>
                        <td>{{ $strokeActual ?? '' }}</td>

                        {{-- Dandori --}}
                        <td>{{ $dandori ?? '' }}</td>
                        <td>{{ $dandoriActual ?: '' }}</td>

                        {{-- GSPH --}}
                        <td>{{ $gsph ?? '' }}</td>
                        <td>{{ $gsphActual ?? '' }}</td>

                        {{-- WT --}}
                        <td>{{ $wt ?? '' }}</td>
                        <td>{{ $wtActual ?? '' }}</td>

                        {{-- Shift 1 --}}
                        <td class="{{ $classA1 }}">{{ $actualShift1 !== null ? $actualShift1 : '' }}</td>
                        <td class="c-white">{{ $startShift1 ?? '' }}</td>
                        <td class="c-white">{{ $finishShift1 ?? '' }}</td>
                        <td class="c-green">{{ $finishShift1 ? '1' : '0' }}</td>

                        {{-- Shift 2 --}}
                        <td class="{{ $classA2 }}">{{ $actualShift2 !== null ? $actualShift2 : '' }}</td>
                        <td class="c-white">{{ $startShift2 ?? '' }}</td>
                        <td class="c-white">{{ $finishShift2 ?? '' }}</td>
                        <td class="c-green">{{ $finishShift2 ? '1' : '0' }}</td>

                        {{-- Shoot / Cavity --}}
                        <td class="c-yellow">{{ $part->qty_category ?? '' }}</td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="22" class="empty-state">Tidak ada data untuk mesin ini.</td>
                    </tr>
                @endforelse

                {{-- ══ BARIS TOTAL ══ --}}
                @php
                    if ($totWtActualMin > 0) {
                        $_tJam          = intdiv((int) $totWtActualMin, 60);
                        $_tMnt          = (int) $totWtActualMin % 60;
                        $totWtActualHours = (float) ($_tJam . '.' . str_pad($_tMnt, 2, '0', STR_PAD_LEFT));
                    } else {
                        $totWtActualHours = null;
                    }
                    $totGsphPlanAvg   = $totGsphPlanCount   > 0 ? round($totGsphPlanSum   / $totGsphPlanCount,   2) : null;
                    $totGsphActualAvg = $totGsphActualCount > 0 ? round($totGsphActualSum / $totGsphActualCount, 2) : null;

                    // Achievement %
                    $pctBox = ($totBoxPlan > 0 && $totBoxActual > 0)
                        ? round(($totBoxActual / $totBoxPlan) * 100, 1) : null;
                    $pctStroke = ($totStrokePlan > 0 && $totStrokeActual > 0)
                        ? round(($totStrokeActual / $totStrokePlan) * 100, 1) : null;

                    $pctBoxClass    = $pctBox    !== null ? ($pctBox    >= 100 ? 'c-green' : 'c-yellow') : '';
                    $pctStrokeClass = $pctStroke !== null ? ($pctStroke >= 100 ? 'c-green' : 'c-yellow') : '';
                @endphp
                <tr class="tr-total">
                    <td colspan="3" style="text-align:center;">TOTAL</td>

                    {{-- BOX --}}
                    <td>{{ $totBoxPlan    ?: '' }}</td>
                    <td>{{ $totBoxActual  ? round($totBoxActual, 2)  : '' }}</td>

                    {{-- Stroke --}}
                    <td>{{ $totStrokePlan    ?: '' }}</td>
                    <td>{{ $totStrokeActual  ? round($totStrokeActual, 2) : '' }}</td>

                    {{-- Dandori --}}
                    <td>{{ $totDandoriPlan   ?: '' }}</td>
                    <td>{{ $totDandoriActual ?: '' }}</td>

                    {{-- GSPH avg --}}
                    <td>{{ $totGsphPlanAvg   ?? '' }}</td>
                    <td>{{ $totGsphActualAvg ?? '' }}</td>

                    {{-- WT --}}
                    <td>{{ $totWtPlan         ? round($totWtPlan, 2) : '' }}</td>
                    <td>{{ $totWtActualHours  ?? '' }}</td>

                    {{-- Shift 1 summary --}}
                    <td>{{ $totShift1Qty ? round($totShift1Qty, 2) : '' }}</td>
                    <td class="{{ $pctBoxClass }}">
                        {{ $pctBox !== null ? $pctBox . '%' : '' }}
                    </td>
                    <td></td>
                    <td class="c-green">{{ $totDShift1 ?: '' }}</td>

                    {{-- Shift 2 summary --}}
                    <td>{{ $totShift2Qty ? round($totShift2Qty, 2) : '' }}</td>
                    <td class="{{ $pctStrokeClass }}">
                        {{ $pctStroke !== null ? $pctStroke . '%' : '' }}
                    </td>
                    <td></td>
                    <td class="c-green">{{ $totDShift2 ?: '' }}</td>

                    {{-- Shoot total --}}
                    <td class="c-yellow">{{ $totShoot ?: '' }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>

    @else
    <div class="report-card">
        <div class="empty-state">Pilih mesin dari pill bar di atas untuk melihat report produksi.</div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    function changeLine(line) {
        window.location.href = '{{ route("report-produksi.index") }}?line=' + encodeURIComponent(line);
    }
    function tambahMesin() {
        const nama = prompt('Nama mesin baru untuk line: {{ $selectedLine }}');
        if (nama) alert('Implement store mesin: ' + nama);
    }
</script>
@endpush