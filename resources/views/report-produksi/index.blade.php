{{-- resources/views/report-produksi/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Report Produksi')

@push('styles')
<style>
    * { box-sizing: border-box; }

    .page-wrapper {
        padding: 16px;
        background: #f0f0f0;
        min-height: 100vh;
    }

    /* ── TOOLBAR ── */
    .toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    .toolbar label {
        font-size: 13px;
        font-weight: 600;
        color: #333;
    }
    .select-line {
        appearance: none;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23555' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center;
        border: 1.5px solid #aaa;
        border-radius: 6px;
        padding: 6px 30px 6px 12px;
        font-size: 13px;
        font-weight: 600;
        color: #1565C0;
        cursor: pointer;
        min-width: 180px;
        transition: border-color .2s;
    }
    .select-line:focus {
        outline: none;
        border-color: #1565C0;
        box-shadow: 0 0 0 3px rgba(21,101,192,.15);
    }

    /* ── MESIN PILL BAR ── */
    .mesin-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        background: #fff;
        border: 1.5px solid #ddd;
        border-radius: 10px;
        padding: 8px 14px;
        margin-bottom: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,.08);
    }
    .line-badge {
        background: #1565C0;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        padding: 5px 14px;
        border-radius: 20px;
        margin-right: 4px;
        white-space: nowrap;
    }
    .mesin-pill {
        display: inline-flex;
        align-items: center;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        background: #f1f5f9;
        color: #334155;
        border: 1.5px solid #e2e8f0;
        cursor: pointer;
        text-decoration: none;
        transition: all .15s;
        white-space: nowrap;
    }
    .mesin-pill:hover {
        background: #e2e8f0;
        color: #1e293b;
        text-decoration: none;
    }
    .mesin-pill.active {
        background: #1565C0;
        color: #fff;
        border-color: #0d47a1;
        font-weight: 700;
    }
    .mesin-pill.tambah {
        background: transparent;
        border: 1.5px dashed #aaa;
        color: #888;
        font-size: 12px;
    }
    .mesin-pill.tambah:hover {
        border-color: #1565C0;
        color: #1565C0;
        background: #e3f2fd;
        text-decoration: none;
    }

    /* ── REPORT CARD ── */
    .report-card {
        background: #fff;
        border: 2px solid #999;
        border-radius: 2px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,.12);
    }

    /* ── EXCEL HEADER ── */
    .excel-header {
        display: grid;
        grid-template-columns: 220px 1fr;
        border-bottom: 2px solid #999;
    }
    .hdr-left {
        border-right: 2px solid #999;
    }
    .hdr-line-row {
        display: flex;
        align-items: center;
        padding: 4px 10px;
        border-bottom: 1px solid #ccc;
        gap: 8px;
        font-size: 12px;
    }
    .hdr-line-row .lbl {
        font-weight: 700;
        color: #333;
        min-width: 60px;
    }
    .hdr-line-row .val {
        font-weight: 700;
        color: #1565C0;
    }
    .hdr-mesin-box {
        background: #FFF176;
        border: 2px solid #F9A825;
        margin: 5px 8px 8px;
        border-radius: 3px;
        text-align: center;
        padding: 8px;
        font-size: 22px;
        font-weight: 900;
        color: #1a1a1a;
        letter-spacing: 1px;
    }
    .hdr-right {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hdr-right h2 {
        font-size: 24px;
        font-weight: 800;
        color: #1a1a1a;
        margin: 0;
        letter-spacing: 2px;
    }

    /* ── TABLE ── */
    .table-wrapper { overflow-x: auto; }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11.5px;
        min-width: 1300px;
    }
    .report-table th,
    .report-table td {
        border: 1px solid #aaa;
        padding: 3px 5px;
        text-align: center;
        white-space: nowrap;
    }

    /* Header colors */
    .th-gray   { background: #B0BEC5; font-weight: 700; color: #111; }
    .th-blue   { background: #90CAF9; font-weight: 700; color: #0d47a1; }
    .th-green  { background: #A5D6A7; font-weight: 700; color: #1b5e20; }
    .th-yellow { background: #FFF176; font-weight: 700; color: #555; }
    .th-light-blue  { background: #BBDEFB; font-weight: 600; color: #0d47a1; }
    .th-light-green { background: #C8E6C9; font-weight: 600; color: #1b5e20; }
    .th-light-yellow{ background: #FFF9C4; font-weight: 600; color: #555; }

    /* Special left cols */
    th.col-no, td.col-no   { width: 32px; }
    th.col-part, td.col-part { text-align: left; min-width: 145px; padding-left: 8px; }
    th.col-stock, td.col-stock { width: 58px; }
    td.col-part { font-weight: 600; }

    /* Body */
    .report-table tbody tr td { background: #fff; color: #333; }
    .report-table tbody tr:nth-child(even) td { background: #f9f9f9; }
    .report-table tbody tr.row-data td { background: #E8F5E9; }
    .report-table tbody tr.row-data:nth-child(even) td { background: #DCEDC8; }
    .report-table tbody tr:hover td { background: #E3F2FD !important; }

    /* Cell colors */
    td.c-green  { background: #A5D6A7 !important; font-weight: 700; color: #1b5e20; }
    td.c-yellow { background: #FFF176 !important; font-weight: 700; color: #555; }
    td.c-white  { background: #fff !important; }

    .empty-state {
        padding: 60px;
        text-align: center;
        color: #999;
        font-size: 13px;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">

    {{-- Dropdown Line --}}
    <div class="toolbar">
        <label for="lineSelect">Line:</label>
        <select class="select-line" id="lineSelect" onchange="changeLine(this.value)">
            @foreach($lines as $line)
                <option value="{{ $line }}" {{ $selectedLine == $line ? 'selected' : '' }}>{{ $line }}</option>
            @endforeach
        </select>
    </div>

    {{-- Mesin Pill Bar --}}
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

        {{-- Excel Header --}}
        <div class="excel-header">
            <div class="hdr-left">
                <div class="hdr-line-row">
                    <span class="lbl">Line</span>
                    <span class="val">{{ $selectedLine }}</span>
                </div>
                <div class="hdr-line-row" style="border-bottom:none;">
                    <span class="lbl">Machine</span>
                </div>
                <div class="hdr-mesin-box">{{ $selectedMesin }}</div>
            </div>
            <div class="hdr-right">
                <h2>Summary</h2>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrapper">
        <table class="report-table">
            <thead>
                {{-- Row 1: Group labels --}}
                <tr>
                    <th class="col-no th-gray" rowspan="3">No</th>
                    <th class="col-part th-gray" rowspan="3">Part_No</th>
                    <th class="col-stock th-gray" rowspan="3">Stock_NP</th>
                    <th colspan="2" class="th-blue">BOX</th>
                    <th colspan="2" class="th-blue">Stroke</th>
                    <th colspan="2" class="th-blue">Dandori</th>
                    <th colspan="2" class="th-blue">GSPH</th>
                    <th colspan="2" class="th-blue">Working Time</th>
                    <th colspan="9" class="th-green">DETAIL REPORT PRODUKSI</th>
                    <th colspan="2" class="th-yellow">DANDORI DAN CAVITY</th>
                </tr>
                {{-- Row 2: Plan/Actual + Shift labels --}}
                <tr>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th colspan="4" class="th-light-green">Shift 1</th>
                    <th colspan="4" class="th-light-green">Shift 2</th>
                    <th class="th-light-green" rowspan="2"></th>
                    <th class="th-light-yellow" rowspan="2">-</th>
                    <th class="th-light-yellow" rowspan="2">Shoot</th>
                </tr>
                {{-- Row 3: Shift sub-columns --}}
                <tr>
                    <th colspan="10"></th>
                    <th class="th-light-green">Qty</th>
                    <th class="th-light-green">Start</th>
                    <th class="th-light-green">Finish</th>
                    <th class="th-light-green">D</th>
                    <th class="th-light-green">Qty</th>
                    <th class="th-light-green">Start</th>
                    <th class="th-light-green">Finish</th>
                    <th class="th-light-green">D</th>
                </tr>
            </thead>
            <tbody>
                @php $totalRows = max($parts->count(), 30); @endphp
                @for($i = 0; $i < $totalRows; $i++)
                    @php $part = $parts->get($i); @endphp
                    <tr class="{{ $part ? 'row-data' : '' }}">
                        <td class="col-no">{{ $i + 1 }}</td>
                        <td class="col-part">{{ $part ? $part->part_no_child : '' }}</td>
                        <td></td>
                        {{-- BOX --}}
                        <td></td><td></td>
                        {{-- Stroke --}}
                        <td></td><td></td>
                        {{-- Dandori --}}
                        <td></td><td></td>
                        {{-- GSPH --}}
                        <td></td><td></td>
                        {{-- Working Time --}}
                        <td></td><td></td>
                        {{-- Shift 1 --}}
                        <td class="c-green">0</td>
                        <td class="c-white"></td>
                        <td class="c-white"></td>
                        <td class="c-green">{{ $part ? '1' : '' }}</td>
                        {{-- Shift 2 --}}
                        <td class="c-green">0</td>
                        <td class="c-white"></td>
                        <td class="c-white"></td>
                        <td class="c-green">{{ $part ? '1' : '' }}</td>
                        {{-- empty --}}
                        <td></td>
                        {{-- Dandori Cavity --}}
                        <td class="c-green">0</td>
                        <td class="c-yellow">1</td>
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