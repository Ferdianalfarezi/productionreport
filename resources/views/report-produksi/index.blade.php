{{-- resources/views/report-produksi/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Report Produksi')

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

        @php
            $allBreaks = collect($breakByShift[1] ?? [])->merge($breakByShift[2] ?? [])
                ->unique('id')->sortBy('break_start');
        @endphp
        @if($allBreaks->isNotEmpty())
        <div class="break-float-wrap" id="breakWrap">
            <button class="btn-break-float" onclick="toggleBreakMenu(event)" type="button">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#92400E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                Break Aktif
                <span class="break-dot"></span>
            </button>
            <div class="break-dropdown" id="breakDropdown">
                <div class="break-dropdown-hdr">⏸ Break Time Aktif</div>
                @foreach($allBreaks as $br)
                    @php
                        [$bh, $bm] = array_map('intval', explode(':', $br->break_start));
                        $endMin = $bh * 60 + $bm + $br->duration;
                        $endStr = sprintf('%02d:%02d', intdiv($endMin, 60) % 24, $endMin % 60);
                        $shiftLabel = $br->shift ? 'S'.$br->shift : 'ALL';
                    @endphp
                    <div class="break-item">
                        <span class="break-shift-badge">{{ $shiftLabel }}</span>
                        <span class="break-item-name">{{ $br->break_name }}</span>
                        <span class="break-item-time">{{ \Str::substr($br->break_start, 0, 5) }}–{{ $endStr }}</span>
                        <span class="break-item-dur">{{ $br->duration }}m</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <button type="button" class="btn-break-cfg" onclick="openBtModal()">⚙ Konfigurasi Break Time</button>
        <button type="button" class="btn-break-cfg" onclick="openImportModal()">⬆ Import</button>

        <div style="margin-left:auto;display:flex;align-items:center;gap:5px;font-size:11.5px;color:#8A90A2;font-family:'DM Mono',monospace;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span style="font-weight:500;color:#4A5168;">Last update:</span>
            <span>
                @if(isset($lastImport) && $lastImport)
                    {{ \Carbon\Carbon::parse($lastImport)->format('d/m/Y H:i') }}
                @else
                    —
                @endif
            </span>
        </div>
    </div>

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
                <tr>
                    <th class="bg-w" colspan="3" style="text-align:left;padding-left:6px;font-weight:700;">
                        Line &nbsp;<span style="color:#2563EB;">{{ $selectedLine }}</span>
                    </th>
                    <th colspan="2" class="th-blue">BOX</th>
                    <th colspan="2" class="th-blue">Stroke</th>
                    <th colspan="2" class="th-blue">Dandori</th>
                    <th colspan="2" class="th-blue">GSPH</th>
                    <th colspan="2" class="th-blue">Working Time</th>
                    <th colspan="7" class="bg-w" style="font-size:16px;font-weight:800;letter-spacing:2px;">DETAIL REPORT PRODUKSI</th>
                    <th colspan="2" class="th-yellow-hdr" style="font-size:10.5px;line-height:1.4;" rowspan="3">DANDORI<br>DAN CAVITY</th>
                </tr>
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
                    $calcDiff = function (?string $start, ?string $finish, \Illuminate\Support\Collection $breaks): int {
                        if (!$start || !$finish) return 0;
                        try {
                            $toMin = function (string $t): int {
                                $p = array_map('intval', explode(':', $t));
                                return $p[0] * 60 + ($p[1] ?? 0);
                            };
                            $startMin  = $toMin($start);
                            $finishMin = $toMin($finish);
                            if ($finishMin < $startMin) $finishMin += 1440;
                            $raw = $finishMin - $startMin;
                            if ($raw <= 0) return 0;
                            $cut = 0;
                            foreach ($breaks as $br) {
                                $bS = $br->break_start_min;
                                $bE = $br->break_end_min;
                                $overlapStart = max($startMin, $bS);
                                $overlapEnd   = min($finishMin, $bE);
                                if ($overlapEnd > $overlapStart) $cut += ($overlapEnd - $overlapStart);
                            }
                            return max(0, $raw - $cut);
                        } catch (\Throwable $e) { return 0; }
                    };

                    $breaksS1 = $breakByShift[1] ?? collect();
                    $breaksS2 = $breakByShift[2] ?? collect();

                    $totBoxPlan=0;$totBoxActual=0;$totStrokePlan=0;$totStrokeActual=0;
                    $totDandoriPlan=0;$totDandoriActual=0;
                    $totGsphPlanSum=0;$totGsphPlanCount=0;$totGsphActualSum=0;$totGsphActualCount=0;
                    $totWtPlan=0;$totWtActualMin=0;
                    $totShift1Qty=0;$totShift2Qty=0;$totDShift1=0;$totDShift2=0;$totShoot=0;
                @endphp

                @forelse($parts as $i => $part)
                    @php
                        $plan = null;
                        if ($stockMap->has($part->part_no_child)) {
                            $sm=$stockMap[$part->part_no_child];
                            $calcProd=(int)$sm->calc_prod;
                            $plan=$calcProd*(int)($sm->qty_kbn??1);
                            $category=strtolower(trim($part->category??''));
                            $qtyCat=(float)($part->qty_category??0);
                            if($category==='shoot'&&$qtyCat>0) $plan=(int)round($plan*$qtyCat);
                            elseif($category==='cavity'&&$qtyCat>0) $plan=(int)round($plan/$qtyCat);
                        }
                        $dandori=($plan!==null&&$plan>=1)?1:null;
                        $gsph=null;
                        if($stockMap->has($part->part_no_child)){
                            $sm=$stockMap[$part->part_no_child];
                            $ltProd=(float)($sm->lt_prod??0);$qtyKbn=(float)($sm->qty_kbn??0);$qtyCat=(float)($part->qty_category??0);
                            if($ltProd>0) $gsph=round((60/$ltProd)*$qtyKbn*$qtyCat);
                        }
                        $wt=($plan!==null&&$gsph!==null&&$gsph>0)?round($plan/$gsph,2):null;
                        $actualShift1=null;$startShift1=null;$finishShift1=null;
                        $actualShift2=null;$startShift2=null;$finishShift2=null;
                        if($stockMap->has($part->part_no_child)){
                            $kbn=(int)($stockMap[$part->part_no_child]->qty_kbn??1);
                            $dataS1=$reportMap[$part->part_no_child][1]??null;
                            $dataS2=$reportMap[$part->part_no_child][2]??null;
                            if($dataS1!==null&&$kbn>0){$actualShift1=round($dataS1['qty_ok']/$kbn,2);$startShift1=$dataS1['prod_start'];$finishShift1=$dataS1['prod_finish'];}
                            if($dataS2!==null&&$kbn>0){$actualShift2=round($dataS2['qty_ok']/$kbn,2);$startShift2=$dataS2['prod_start'];$finishShift2=$dataS2['prod_finish'];}
                        }
                        $actualTotal=null;
                        if($actualShift1!==null||$actualShift2!==null) $actualTotal=round(($actualShift1??0)+($actualShift2??0),2);
                        $strokeActual=null;
                        if($actualTotal!==null&&$stockMap->has($part->part_no_child)){
                            $kbn=(int)($stockMap[$part->part_no_child]->qty_kbn??1);
                            if($kbn>0) $strokeActual=round($actualTotal*$kbn,2);
                        }
                        $dandoriActual=($finishShift1?1:0)+($finishShift2?1:0);
                        $diff1=$calcDiff($startShift1,$finishShift1,$breaksS1);
                        $diff2=$calcDiff($startShift2,$finishShift2,$breaksS2);
                        $totalMinutes=$diff1+$diff2;
                        if($totalMinutes>0){$_wtJam=intdiv((int)$totalMinutes,60);$_wtMnt=(int)$totalMinutes%60;$wtActual=(float)($_wtJam.'.'.str_pad($_wtMnt,2,'0',STR_PAD_LEFT));}
                        else $wtActual=null;
                        $gsphActual=null;
                        if($strokeActual!==null&&$totalMinutes>0) $gsphActual=round($strokeActual/($totalMinutes/60),2);
                        $strokePlan=null;
                        if($plan!==null&&$stockMap->has($part->part_no_child)){$kbn=(int)($stockMap[$part->part_no_child]->qty_kbn??1);$strokePlan=$plan*$kbn;}
                        $classA1=$actualShift1!==null?($plan!==null&&$actualShift1>=$plan?'c-green':'c-yellow'):'c-white';
                        $classA2=$actualShift2!==null?($plan!==null&&$actualShift2>=$plan?'c-green':'c-yellow'):'c-white';
                        if($stockMap->has($part->part_no_child)) $totBoxPlan+=(int)$stockMap[$part->part_no_child]->calc_prod;
                        $totBoxActual+=$actualTotal??0;$totStrokePlan+=$strokePlan??0;$totStrokeActual+=$strokeActual??0;
                        $totDandoriPlan+=$dandori??0;$totDandoriActual+=$dandoriActual;
                        if($gsph!==null&&$gsph>0){$totGsphPlanSum+=$gsph;$totGsphPlanCount++;}
                        if($gsphActual!==null&&$gsphActual>0){$totGsphActualSum+=$gsphActual;$totGsphActualCount++;}
                        $totWtPlan+=$wt??0;$totWtActualMin+=$totalMinutes;
                        $totShift1Qty+=$actualShift1??0;$totShift2Qty+=$actualShift2??0;
                        $totDShift1+=($finishShift1?1:0);$totDShift2+=($finishShift2?1:0);$totShoot+=1;
                    @endphp

                    <tr class="row-data">
                        <td class="col-no">{{ $loop->iteration }}</td>
                        <td class="col-part">{{ $part->part_no_child }}</td>
                        <td class="col-stock">{{ $stockMap->has($part->part_no_child) ? $stockMap[$part->part_no_child]->stock_store : '' }}</td>
                        <td>{{ $plan ?? '' }}</td>
                        <td>{{ $actualTotal ?? '' }}</td>
                        <td>{{ $strokePlan ?? '' }}</td>
                        <td>{{ $strokeActual ?? '' }}</td>
                        <td>{{ $dandori ?? '' }}</td>
                        <td>{{ $dandoriActual ?: '' }}</td>
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
                        <td class="c-yellow">{{ $part->qty_category ?? '' }}</td>
                    </tr>

                @empty
                    <tr><td colspan="22" class="empty-state">Tidak ada data untuk mesin ini.</td></tr>
                @endforelse

                @php
                    if($totWtActualMin>0){$_tJam=intdiv((int)$totWtActualMin,60);$_tMnt=(int)$totWtActualMin%60;$totWtActualHours=(float)($_tJam.'.'.str_pad($_tMnt,2,'0',STR_PAD_LEFT));}
                    else $totWtActualHours=null;
                    $totGsphPlanAvg=$totGsphPlanCount>0?round($totGsphPlanSum/$totGsphPlanCount,2):null;
                    $totGsphActualAvg=$totGsphActualCount>0?round($totGsphActualSum/$totGsphActualCount,2):null;
                    $pctBox=($totBoxPlan>0&&$totBoxActual>0)?round(($totBoxActual/$totBoxPlan)*100,1):null;
                    $pctStroke=($totStrokePlan>0&&$totStrokeActual>0)?round(($totStrokeActual/$totStrokePlan)*100,1):null;
                    $pctBoxClass=$pctBox!==null?($pctBox>=100?'c-green':'c-yellow'):'';
                    $pctStrokeClass=$pctStroke!==null?($pctStroke>=100?'c-green':'c-yellow'):'';
                @endphp
                <tr class="tr-total">
                    <td colspan="3" style="text-align:center;">TOTAL</td>
                    <td>{{ $totBoxPlan ?: '' }}</td>
                    <td>{{ $totBoxActual ? round($totBoxActual, 2) : '' }}</td>
                    <td>{{ $totStrokePlan ?: '' }}</td>
                    <td>{{ $totStrokeActual ? round($totStrokeActual, 2) : '' }}</td>
                    <td>{{ $totDandoriPlan ?: '' }}</td>
                    <td>{{ $totDandoriActual ?: '' }}</td>
                    <td>{{ $totGsphPlanAvg ?? '' }}</td>
                    <td>{{ $totGsphActualAvg ?? '' }}</td>
                    <td>{{ $totWtPlan ? round($totWtPlan, 2) : '' }}</td>
                    <td>{{ $totWtActualHours ?? '' }}</td>
                    <td>{{ $totShift1Qty ? round($totShift1Qty, 2) : '' }}</td>
                    <td class="{{ $pctBoxClass }}">{{ $pctBox !== null ? $pctBox . '%' : '' }}</td>
                    <td></td>
                    <td class="c-green">{{ $totDShift1 ?: '' }}</td>
                    <td>{{ $totShift2Qty ? round($totShift2Qty, 2) : '' }}</td>
                    <td class="{{ $pctStrokeClass }}">{{ $pctStroke !== null ? $pctStroke . '%' : '' }}</td>
                    <td></td>
                    <td class="c-green">{{ $totDShift2 ?: '' }}</td>
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
    function toggleBreakMenu(e) {
        e.stopPropagation();
        document.getElementById('breakDropdown').classList.toggle('open');
    }
    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('breakWrap');
        if (wrap && !wrap.contains(e.target)) {
            document.getElementById('breakDropdown').classList.remove('open');
        }
    });
</script>
@endpush