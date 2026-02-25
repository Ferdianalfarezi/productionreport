{{-- resources/views/report-produksi/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Report Produksi')

@push('styles')
<style>
    * { box-sizing: border-box; }
    .page-wrapper { padding: 16px; background: #f0f0f0; min-height: 100vh; }

    .toolbar { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .toolbar label { font-size: 13px; font-weight: 600; color: #333; }
    .select-line {
        appearance: none;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23555' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center;
        border: 1.5px solid #aaa; border-radius: 6px; padding: 6px 30px 6px 12px;
        font-size: 13px; font-weight: 600; color: #1565C0; cursor: pointer; min-width: 180px;
    }
    .select-line:focus { outline: none; border-color: #1565C0; box-shadow: 0 0 0 3px rgba(21,101,192,.15); }

    .mesin-bar { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; background: #fff; border: 1.5px solid #ddd; border-radius: 10px; padding: 8px 14px; margin-bottom: 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
    .line-badge { background: #1565C0; color: #fff; font-size: 13px; font-weight: 700; padding: 5px 14px; border-radius: 20px; margin-right: 4px; }
    .mesin-pill { display: inline-flex; align-items: center; padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 500; background: #f1f5f9; color: #334155; border: 1.5px solid #e2e8f0; cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap; }
    .mesin-pill:hover { background: #e2e8f0; text-decoration: none; }
    .mesin-pill.active { background: #1565C0; color: #fff; border-color: #0d47a1; font-weight: 700; }
    .mesin-pill.tambah { background: transparent; border: 1.5px dashed #aaa; color: #888; font-size: 12px; }
    .mesin-pill.tambah:hover { border-color: #1565C0; color: #1565C0; background: #e3f2fd; text-decoration: none; }

    .report-card { background: #fff; border: 2px solid #999; border-radius: 2px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.12); }
    .table-wrapper { overflow-x: auto; }

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

    .report-table tbody tr td    { background: #fff; color: #333; }
    .report-table tbody tr.row-data td { background: #E8F5E9; }
    .report-table tbody tr.row-data:nth-child(even) td { background: #DCEDC8; }
    .report-table tbody tr:hover td { background: #E3F2FD !important; }

    td.c-green  { background: #A5D6A7 !important; font-weight: 700; color: #1b5e20; }
    td.c-yellow { background: #FFFF00 !important; font-weight: 700; color: #333; }
    td.c-white  { background: #fff !important; }

    /* Total row */
    .tr-total td { background: #E3F2FD !important; font-weight: 700; color: #0d47a1; border-top: 2px solid #1565C0; }
    .tr-total td.c-green  { background: #A5D6A7 !important; color: #1b5e20; }
    .tr-total td.c-yellow { background: #FFFF00 !important; color: #333; }

    .empty-state { padding: 60px; text-align: center; color: #999; font-size: 13px; }
</style>
@endpush

@section('content')
<div class="page-wrapper">

    <div class="toolbar">
        <label for="lineSelect">Line:</label>
        <select class="select-line" id="lineSelect" onchange="changeLine(this.value)">
            @foreach($lines as $line)
                <option value="{{ $line }}" {{ $selectedLine == $line ? 'selected' : '' }}>{{ $line }}</option>
            @endforeach
        </select>
    </div>

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
                    /* ── Akumulator untuk baris Total ── */
                    $totBoxPlan      = 0; $totBoxActual    = 0;
                    $totStrokePlan   = 0; $totStrokeActual = 0;
                    $totDandoriPlan  = 0; $totDandoriActual= 0;
                    $totGsphPlan     = 0;
                    $totWtPlan       = 0; $totWtActualMin  = 0;
                    $totShift1Qty    = 0; $totShift2Qty    = 0;
                    $totDShift1      = 0; $totDShift2      = 0;
                    $totShoot        = 0;
                @endphp

                @foreach($parts as $i => $part)
                    @php
                        /* ─── Plan ─── */
                        $plan = null;
                        if ($stockMap->has($part->part_no_child)) {
                            $calcProd = (int) $stockMap[$part->part_no_child]->calc_prod;
                            $plan     = $calcProd * (int) $part->qty_kbn;
                            $category = strtolower(trim($part->category ?? ''));
                            if ($category === 'shoot' && $part->qty_category > 0) {
                                $plan = $plan * (int) $part->qty_category;
                            } elseif ($category === 'cavity' && $part->qty_category > 0) {
                                $plan = (int) round($plan / $part->qty_category);
                            }
                        }

                        /* ─── Dandori ─── */
                        $dandori = ($plan !== null && $plan >= 1) ? 1 : null;

                        /* ─── GSPH ─── */
                        $gsph = null;
                        if ($stockMap->has($part->part_no_child)) {
                            $ltProd = (float) ($stockMap[$part->part_no_child]->lt_prod ?? 0);
                            $qtyKbn = (float) ($stockMap[$part->part_no_child]->qty_kbn ?? 0);
                            $qtyCat = (float) ($part->qty_category ?? 0);
                            if ($ltProd > 0) {
                                $gsph = round((60 / $ltProd) * $qtyKbn * $qtyCat);
                            }
                        }

                        /* ─── Working Time ─── */
                        $wt = ($plan !== null && $gsph !== null && $gsph > 0)
                            ? round($plan / $gsph, 2) : null;

                        /* ─── Actual ─── */
                        $actualShift1 = null; $startShift1 = null; $finishShift1 = null;
                        $actualShift2 = null; $startShift2 = null; $finishShift2 = null;

                        if ($stockMap->has($part->part_no_child)) {
                            $kbnForActual = (int) ($stockMap[$part->part_no_child]->qty_kbn ?? 0);
                            $dataS1 = $reportMap[$part->part_no_child][1] ?? null;
                            $dataS2 = $reportMap[$part->part_no_child][2] ?? null;

                            if ($dataS1 !== null && $kbnForActual > 0) {
                                $actualShift1 = round($dataS1['qty_ok'] / $kbnForActual, 2);
                                $startShift1  = $dataS1['prod_start'];
                                $finishShift1 = $dataS1['prod_finish'];
                            }
                            if ($dataS2 !== null && $kbnForActual > 0) {
                                $actualShift2 = round($dataS2['qty_ok'] / $kbnForActual, 2);
                                $startShift2  = $dataS2['prod_start'];
                                $finishShift2 = $dataS2['prod_finish'];
                            }
                        }

                        /* ─── Total Actual ─── */
                        $actualTotal = null;
                        if ($actualShift1 !== null || $actualShift2 !== null) {
                            $actualTotal = round(($actualShift1 ?? 0) + ($actualShift2 ?? 0), 2);
                        }

                        /* ─── Stroke Actual ─── */
                        $strokeActual = null;
                        if ($actualTotal !== null && $stockMap->has($part->part_no_child)) {
                            $qtyKbnForStroke = (int) ($stockMap[$part->part_no_child]->qty_kbn ?? 0);
                            if ($qtyKbnForStroke > 0) {
                                $strokeActual = round($actualTotal * $qtyKbnForStroke, 2);
                            }
                        }

                        /* ─── Dandori Actual ─── */
                        $dandoriActual = ($finishShift1 ? 1 : 0) + ($finishShift2 ? 1 : 0);

                        /* ─── WT Actual ─── */
                        $wtActual = null;
                        $calcDiff = function($start, $finish) {
                            if (!$start || !$finish) return 0;
                            try {
                                [$sh, $sm] = array_map('intval', explode(':', $start));
                                [$fh, $fm] = array_map('intval', explode(':', $finish));
                                $diff = ($fh * 60 + $fm) - ($sh * 60 + $sm);
                                return $diff > 0 ? $diff : 0;
                            } catch (\Throwable $e) { return 0; }
                        };
                        $diff1 = $calcDiff($startShift1, $finishShift1);
                        $diff2 = $calcDiff($startShift2, $finishShift2);
                        $totalMinutes = $diff1 + $diff2;
                        if ($totalMinutes > 0) {
                            $wtActual = round($totalMinutes / 60, 2);
                        }

                        /* ─── GSPH Actual ─── */
                        $gsphActual = null;
                        if ($strokeActual !== null && $wtActual !== null && $wtActual > 0) {
                            $gsphActual = round($strokeActual / $wtActual, 2);
                        }

                        /* ─── Warna ─── */
                        $classA1 = $actualShift1 !== null
                            ? ($plan !== null && $actualShift1 >= $plan ? 'c-green' : 'c-yellow') : 'c-white';
                        $classA2 = $actualShift2 !== null
                            ? ($plan !== null && $actualShift2 >= $plan ? 'c-green' : 'c-yellow') : 'c-white';

                        /* ─── Akumulasi Total ─── */
                        if ($stockMap->has($part->part_no_child)) {
                            $totBoxPlan      += (int) $stockMap[$part->part_no_child]->calc_prod;
                        }
                        $totBoxActual    += $actualTotal    ?? 0;
                        $totStrokePlan   += $plan           ?? 0;
                        $totStrokeActual += $strokeActual   ?? 0;
                        $totDandoriPlan  += $dandori        ?? 0;
                        $totDandoriActual+= $dandoriActual;
                        $totGsphPlan     += $gsph           ?? 0;
                        $totWtPlan       += $wt             ?? 0;
                        $totWtActualMin  += $totalMinutes;
                        $totShift1Qty    += $actualShift1   ?? 0;
                        $totShift2Qty    += $actualShift2   ?? 0;
                        $totDShift1      += ($finishShift1 ? 1 : 0);
                        $totDShift2      += ($finishShift2 ? 1 : 0);
                        $totShoot        += 1;
                    @endphp

                    <tr class="row-data">
                        <td class="col-no">{{ $loop->iteration }}</td>
                        <td class="col-part">{{ $part->part_no_child }}</td>
                        <td>{{ $stockMap->has($part->part_no_child) ? $stockMap[$part->part_no_child]->stock_store : '' }}</td>
                        <td>{{ $stockMap->has($part->part_no_child) ? (int) $stockMap[$part->part_no_child]->calc_prod : '' }}</td>
                        <td>{{ $actualTotal ?? '' }}</td>
                        <td>{{ $plan ?? '' }}</td>
                        <td>{{ $strokeActual ?? '' }}</td>
                        <td>{{ $dandori ?? '' }}</td>
                        <td>{{ $dandoriActual }}</td>
                        <td>{{ $gsph ?? '' }}</td>
                        <td>{{ $gsphActual ?? '' }}</td>
                        <td>{{ $wt ?? '' }}</td>
                        <td>{{ $wtActual ?? '' }}</td>
                        <td class="{{ $classA1 }}">{{ $actualShift1 !== null ? $actualShift1 : '' }}</td>
                        <td class="c-white">{{ $startShift1 ?? '' }}</td>
                        <td class="c-white">{{ $finishShift1 ?? '' }}</td>
                        <td class="c-green">{{ $finishShift1 ? '1' : '0' }}</td>
                        <td class="{{ $classA2 }}">{{ $actualShift2 !== null ? $actualShift2 : '' }}</td>
                        <td class="c-white">{{ $startShift2 ?? '' }}</td>
                        <td class="c-white">{{ $finishShift2 ?? '' }}</td>
                        <td class="c-green">{{ $finishShift2 ? '1' : '0' }}</td>
                        <td class="c-yellow">1</td>
                    </tr>
                @endforeach

                {{-- ══ BARIS TOTAL ══ --}}
                @php
                    $totWtActualHours  = $totWtActualMin > 0 ? round($totWtActualMin / 60, 2) : null;
                    $totGsphActual     = ($totStrokeActual > 0 && $totWtActualHours > 0)
                                        ? round($totStrokeActual / $totWtActualHours, 2) : null;

                    // % untuk kolom Shift Qty (persentase actual vs plan)
                    $pctShift1 = ($totBoxPlan > 0 && $totBoxActual > 0)
                                ? number_format(($totBoxActual / $totBoxPlan) * 100, 1) . '%' : '';
                    $pctShift2 = ($totStrokePlan > 0 && $totStrokeActual > 0)
                                ? number_format(($totStrokeActual / $totStrokePlan) * 100, 1) . '%' : '';
                @endphp
                <tr class="tr-total">
                    <td colspan="3" style="text-align:center;font-weight:700;">Total</td>
                    <td>{{ $totBoxPlan      ?: '' }}</td>
                    <td>{{ $totBoxActual    ? round($totBoxActual, 2)    : '' }}</td>
                    <td>{{ $totStrokePlan   ?: '' }}</td>
                    <td>{{ $totStrokeActual ? round($totStrokeActual, 2) : '' }}</td>
                    <td>{{ $totDandoriPlan  ?: '' }}</td>
                    <td>{{ $totDandoriActual ?: '' }}</td>
                    <td>{{ $totGsphPlan     ?: '' }}</td>
                    <td>{{ $totGsphActual   ?? '' }}</td>
                    <td>{{ $totWtPlan       ? round($totWtPlan, 2)       : '' }}</td>
                    <td>{{ $totWtActualHours ?? '' }}</td>
                    <td></td>
                    <td>{{ $pctShift1 }}</td>
                    <td></td>
                    <td class="c-green">{{ $totDShift1 ?: '' }}</td>
                    <td></td>
                    <td>{{ $pctShift2 }}</td>
                    <td></td>
                    
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
        alert('Tambah mesin untuk line: {{ $selectedLine }}');
    }
</script>
@endpush