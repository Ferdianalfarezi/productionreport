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
                    {{-- DANDORI DAN CAVITY: rowspan=3 menempati row1, row2, row3 --}}
                    <th colspan="2" class="th-yellow-hdr" style="font-size:10.5px;line-height:1.4;" rowspan="3">DANDORI<br>DAN CAVITY</th>
                </tr>

                {{-- ══ ROW 2 ══ --}}
                <tr>
                    <th class="bg-w" colspan="3" style="font-weight:700;">Machine</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th colspan="4" class="th-light-green" rowspan="2" style="vertical-align:middle;">Shift 1</th>
                    <th colspan="3" class="th-light-green" rowspan="2" style="vertical-align:middle;">Shift 2</th>
                    {{-- kolom DANDORI sudah di-rowspan dari row1, tidak perlu tambah cell di sini --}}
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
                    {{-- Shift 1 & 2 sudah di-rowspan dari row2 --}}
                    {{-- DANDORI sudah di-rowspan dari row1 --}}
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
                @php $totalRows = max($parts->count(), 30); @endphp
                @for($i = 0; $i < $totalRows; $i++)
                    @php
                        $part = $parts->get($i);

                        /* ─── Plan ─── */
                        $plan = null;
                        if ($part && $stockMap->has($part->part_no_child)) {
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
                        if ($part && $stockMap->has($part->part_no_child)) {
                            $ltProd = (float) ($stockMap[$part->part_no_child]->lt_prod ?? 0);
                            $qtyKbn = (float) ($stockMap[$part->part_no_child]->qty_kbn ?? 0);
                            $qtyCat = (float) ($part->qty_category ?? 0);
                            if ($ltProd > 0) {
                                $gsph = round((60 / $ltProd) * $qtyKbn * $qtyCat);
                            }
                        }

                        /* ─── Working Time ─── */
                        $wt = ($plan !== null && $gsph !== null && $gsph > 0)
                            ? round($plan / $gsph, 2)
                            : null;

                        /* ─── Actual dari reportMap ─── */
                        $actualShift1 = null;
                        $startShift1  = null;
                        $finishShift1 = null;

                        $actualShift2 = null;
                        $startShift2  = null;
                        $finishShift2 = null;

                        if ($part && $stockMap->has($part->part_no_child)) {
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

                        /* ─── Warna actual ─── */
                        $classA1 = $actualShift1 !== null
                            ? ($plan !== null && $actualShift1 >= $plan ? 'c-green' : 'c-yellow')
                            : 'c-white';
                        $classA2 = $actualShift2 !== null
                            ? ($plan !== null && $actualShift2 >= $plan ? 'c-green' : 'c-yellow')
                            : 'c-white';
                    @endphp

                    <tr class="{{ $part ? 'row-data' : '' }}">
                        {{-- [0] No --}}
                        <td class="col-no">{{ $i + 1 }}</td>
                        {{-- [1] Part No --}}
                        <td class="col-part">{{ $part ? $part->part_no_child : '' }}</td>
                        {{-- [2] Stock NP --}}
                        <td>{{ $part && $stockMap->has($part->part_no_child) ? $stockMap[$part->part_no_child]->stock_store : '' }}</td>

                        {{-- [3][4] BOX Plan / Actual --}}
                        <td>{{ $part && $stockMap->has($part->part_no_child) ? (int) $stockMap[$part->part_no_child]->calc_prod : '' }}</td>
                        <td></td>

                        {{-- [5][6] Stroke Plan / Actual --}}
                        <td>{{ $plan ?? '' }}</td>
                        <td></td>

                        {{-- [7][8] Dandori Plan / Actual --}}
                        <td>{{ $dandori ?? '' }}</td>
                        <td></td>

                        {{-- [9][10] GSPH Plan / Actual --}}
                        <td>{{ $gsph ?? '' }}</td>
                        <td></td>

                        {{-- [11][12] Working Time Plan / Actual --}}
                        <td>{{ $wt ?? '' }}</td>
                        <td></td>

                        {{-- [13][14][15][16] Shift 1 : Qty, Start, Finish, D --}}
                        <td class="{{ $classA1 }}">{{ $actualShift1 !== null ? $actualShift1 : '' }}</td>
                        <td class="c-white">{{ $startShift1 ?? '' }}</td>
                        <td class="c-white">{{ $finishShift1 ?? '' }}</td>
                        <td class="c-green">{{ $part ? '1' : '' }}</td>

                        {{-- [17][18][19] Shift 2 : Qty, Start, Finish --}}
                        <td class="{{ $classA2 }}">{{ $actualShift2 !== null ? $actualShift2 : '' }}</td>
                        <td class="c-white">{{ $startShift2 ?? '' }}</td>
                        <td class="c-white">{{ $finishShift2 ?? '' }}</td>

                        {{-- [20][21] DANDORI DAN CAVITY : D, Shoot --}}
                        <td class="c-green">{{ $part ? '0' : '' }}</td>
                        <td class="c-yellow">{{ $part ? '1' : '' }}</td>
                    </tr>
                @endfor
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