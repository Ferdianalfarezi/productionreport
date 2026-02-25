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

    .report-table { width: 100%; border-collapse: collapse; font-size: 11.5px; min-width: 1500px; }
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

        {{--
        ═══════════════════════════════════════════════════════════════
        24 KOLOM:
          1-3   : No, Part_No, Stock_NP
          4-13  : BOX(2) Stroke(2) Dandori(2) GSPH(2) WT(2)
          14-17 : Shift1 Qty/Start/Finish/D
          18-21 : Shift2 Qty/Start/Finish/D
          22    : empty
          23-24 : "-" / Shoot

        HEADER 4 ROWS — Shift1/Shift2 TIDAK ada row terpisah,
        langsung masuk sebagai teks di dalam sel Qty/Start/Finish/D
        dengan format "Shift 1\nQty" menggunakan line-height trick,
        atau: Shift1 label di row yg sama dengan Qty (colspan=4),
        lalu sub-label Qty/St/Fi/D di row berikutnya.

        CARA TERBAIK: pakai 2 baris untuk area kanan
          Baris kanan-atas  : Shift1 colspan=4 | Shift2 colspan=4 | empty | - | Shoot
          Baris kanan-bawah : Qty/St/Fi/D | Qty/St/Fi/D | (sama)

        Dan baris kiri sudah punya:
          Baris A: Line + BOX..WT headers
          Baris B: Machine + Plan/Actual×5
          Baris C: Mesin + totals
          Baris D: "Detial.." + No/Part/Stock + Plan/Actual×5

        Jadi total kiri butuh 4 baris, kanan butuh 2 baris.
        → Shift 1/2 label: rowspan=2 (baris C dan D kiri = baris 1 dan 2 kanan)
          tapi itu tetap 2 baris kosong di kanan.

        SOLUSI FINAL: tulis header kiri di dalam satu blok div di luar tabel,
        dan header kanan di dalam tabel normal.
        
        ATAU: gunakan CSS untuk menyatukan visual —
        Shift 1 label di row C dengan rowspan=2,
        dan di row D tulis Qty/St/Fi/D.
        Row C kanan = Shift1(rowspan=2 via display trick) → tidak bisa HTML table.

        CARA PALING BERSIH:
        Shift1 + Qty/Start/Finish/D gabung dalam 1 sel per kolom,
        dengan teks baris pertama "Shift 1" dan baris kedua "Qty" dll.
        Gunakan <br> dan style.

        Final: 1 sel per kolom dengan header 2-baris:
        ┌──────┬───────┬────────┬───┬──────┬───────┬────────┬───┐
        │Shift1│       │        │   │Shift2│       │        │   │
        │ Qty  │ Start │ Finish │ D │ Qty  │ Start │ Finish │ D │
        └──────┴───────┴────────┴───┴──────┴───────┴────────┴───┘
        ═══════════════════════════════════════════════════════════
        --}}

        <table class="report-table">
            <thead>

                {{-- ══ ROW 1: Line + group headers + DRP title + DANDORI ══ --}}
                <tr>
                    <th class="bg-w" colspan="3" style="text-align:left;padding-left:6px;font-weight:700;">
                        Line &nbsp;<span style="color:#1565C0;">{{ $selectedLine }}</span>
                    </th>
                    <th colspan="2" class="th-blue">BOX</th>
                    <th colspan="2" class="th-blue">Stroke</th>
                    <th colspan="2" class="th-blue">Dandori</th>
                    <th colspan="2" class="th-blue">GSPH</th>
                    <th colspan="2" class="th-blue">Working Time</th>
                    <th colspan="8" class="bg-w" style="font-size:18px;font-weight:800;letter-spacing:1px;">DETAIL REPORT PRODUKSI</th>
                    <th class="bg-w"></th>
                    <th colspan="2" class="th-yellow-hdr" style="font-size:10.5px;line-height:1.4;">DANDORI<br>DAN CAVITY</th>
                </tr>

                {{-- ══ ROW 2: Machine + Plan/Actual + Shift1/2 group labels + "-"/Shoot ══ --}}
                <tr>
                    <th class="bg-w" colspan="3" style="font-weight:700;">Machine</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    {{-- Shift 1 label rowspan=2 → menutupi row 2 dan row 3 --}}
                    <th colspan="4" class="th-light-green" rowspan="2" style="vertical-align:middle;">Shift 1</th>
                    {{-- Shift 2 label rowspan=2 --}}
                    <th colspan="4" class="th-light-green" rowspan="2" style="vertical-align:middle;">Shift 2</th>
                    <th class="bg-w" rowspan="2"></th>
                    <th class="th-light-yellow" rowspan="2">-</th>
                    <th class="th-light-yellow" rowspan="2">Shoot</th>
                </tr>

                {{-- ══ ROW 3: Mesin + totals (kanan tertutup rowspan) ══ --}}
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
                    {{-- col 14-24 tertutup rowspan dari row 2 --}}
                </tr>

                {{-- ══ ROW 4: "Detial Plan vs Actual" + Qty/St/Fi/D labels ══ --}}
                <tr>
                    <td colspan="13" style="background:#fff;font-weight:700;font-size:12px;text-align:center;border:1px solid #aaa;">
                        Detial Plan vs Actual (Item)
                    </td>
                    <th class="th-light-green">Qty</th>
                    <th class="th-light-green">Start</th>
                    <th class="th-light-green">Finish</th>
                    <th class="th-light-green">D</th>
                    <th class="th-light-green">Qty</th>
                    <th class="th-light-green">Start</th>
                    <th class="th-light-green">Finish</th>
                    <th class="th-light-green">D</th>
                    <th class="bg-w"></th>
                    <th class="th-light-yellow">-</th>
                    <th class="th-light-yellow">Shoot</th>
                </tr>

                {{-- ══ ROW 5: No/Part/Stock + Plan/Actual labels ══ --}}
                <tr>
                    <th class="col-no    bg-w" style="font-weight:700;">No</th>
                    <th class="col-part  bg-w" style="font-weight:700;text-align:left;">Part_No</th>
                    <th class="col-stock bg-w" style="font-weight:700;">Stock_NP</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <th class="th-light-blue">Plan</th><th class="th-light-blue">Actual</th>
                    <td colspan="11" class="bg-w"></td>
                </tr>

            </thead>
            <tbody>
    @php $totalRows = max($parts->count(), 30); @endphp
    @for($i = 0; $i < $totalRows; $i++)
        @php
            $part = $parts->get($i);

            $plan = null;
            if ($part && $stockMap->has($part->part_no_child)) {
                $calcProd = (int) $stockMap[$part->part_no_child]->calc_prod;
                $plan = $calcProd * (int) $part->qty_kbn;

                $category = strtolower(trim($part->category ?? ''));

                if ($category === 'shoot' && $part->qty_category > 0) {
                    $plan = $plan * (int) $part->qty_category;
                } elseif ($category === 'cavity' && $part->qty_category > 0) {
                    $plan = (int) round($plan / $part->qty_category);
                }
            }

            $dandori = null;
            if ($plan !== null && $plan >= 1) {
                $dandori = 1;
            }

            $gsph = null;
            if ($part && $stockMap->has($part->part_no_child)) {
                $ltProd = (float) ($stockMap[$part->part_no_child]->lt_prod ?? 0);
                $qtyKbn = (float) ($stockMap[$part->part_no_child]->qty_kbn ?? 0);
                $qtyCat = (float) ($part->qty_category ?? 0);

                if ($ltProd > 0) {
                    $gsph = round((60 / $ltProd) * $qtyKbn * $qtyCat);
                }
            }

            $wt = null;
            if ($plan !== null && $gsph !== null && $gsph > 0) {
                $wt = round($plan / $gsph, 2);
            }
        @endphp
       
        <tr class="{{ $part ? 'row-data' : '' }}">
            <td class="col-no">{{ $i + 1 }}</td>
            <td class="col-part">{{ $part ? $part->part_no_child : '' }}</td>
            <td>{{ $part && $stockMap->has($part->part_no_child) ? $stockMap[$part->part_no_child]->stock_store : '' }}</td>
            <td>{{ $part && $stockMap->has($part->part_no_child) ? (int) $stockMap[$part->part_no_child]->calc_prod : '' }}</td><td>s</td>
            <td>{{ $plan ?? '' }}</td><td></td>
            <td>{{ $dandori ?? '' }}</td><td></td>
            <td>{{ $gsph ?? '' }}</td><td></td>
            <td>{{ $wt ?? '' }}</td><td></td>
            <td class="c-green">0</td>
            <td class="c-white"></td>
            <td class="c-white"></td>
            <td class="c-green">{{ $part ? '1' : '' }}</td>
            <td class="c-green">0</td>
            <td class="c-white"></td>
            <td class="c-white"></td>
            <td class="c-green">{{ $part ? '1' : '' }}</td>
            <td></td>
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