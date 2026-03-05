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

        {{-- ── CALENDAR DATE PICKER ── --}}
        @php
            $calAvailSet = $availableDates->flip(); // set untuk O(1) lookup
            $calLatest   = $availableDates->first();
            $calIsArchive = $selectedDate && $selectedDate !== $calLatest;
        @endphp
        <div class="cal-wrap" id="calWrap">
            <button class="btn-cal-trigger {{ $calIsArchive ? 'is-archive' : '' }}"
                    type="button" onclick="toggleCal(event)">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                @if($selectedDate)
                    @if(!$calIsArchive)
                        <span>Terbaru &mdash; {{ $selectedDateLabel }}</span>
                    @else
                        <span>Arsip &mdash; {{ $selectedDateLabel }}</span>
                    @endif
                @else
                    <span>Pilih Tanggal</span>
                @endif
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
            </button>

            <div class="cal-popup" id="calPopup">
                <div class="cal-popup-hdr">
                    <button class="cal-nav" type="button" id="calPrev">&#8249;</button>
                    <span class="cal-month-label" id="calMonthLabel"></span>
                    <button class="cal-nav" type="button" id="calNext">&#8250;</button>
                </div>
                <div class="cal-dow-row">
                    <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span>
                    <span>Kam</span><span>Jum</span><span>Sab</span>
                </div>
                <div class="cal-grid" id="calGrid"></div>
                @if($calLatest)
                <div class="cal-footer">
                    <a href="{{ route('report-produksi.index', array_merge(request()->except('import_date'), ['import_date' => $calLatest])) }}"
                       class="cal-footer-latest">
                        ↑ Ke data terbaru ({{ \Carbon\Carbon::parse($calLatest)->format('d/m/Y') }})
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Pass available dates & URL template to JS --}}
        <script id="calData" type="application/json">
        {
            "dates": @json($availableDates->values()),
            "selected": @json($selectedDate),
            "latest": @json($calLatest),
            "baseUrl": "{{ route('report-produksi.index') }}",
            "currentParams": @json(request()->except('import_date'))
        }
        </script>
        {{-- ── END CALENDAR ── --}}

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
            <a href="{{ route('report-produksi.index', array_merge(request()->except('mesin'), ['line' => $selectedLine, 'mesin' => $mesin])) }}"
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

@push('styles')
<style>
/* ── Calendar Date Picker ── */
.cal-wrap {
    position: relative;
}

.btn-cal-trigger {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 10px;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    background: #fff;
    font-size: 12px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.btn-cal-trigger:hover {
    border-color: #2563EB;
    color: #2563EB;
}
.btn-cal-trigger.is-archive {
    border-color: #F59E0B;
    background: #FFFBEB;
    color: #92400E;
}

/* ── Popup ── */
.cal-popup {
    display: none;
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    z-index: 999;
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(0,0,0,.14);
    width: 272px;
    padding-bottom: 4px;
    animation: calFadeIn .15s ease;
}
.cal-popup.open { display: block; }

@keyframes calFadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Header nav ── */
.cal-popup-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px 8px;
    border-bottom: 1px solid #F3F4F6;
}
.cal-month-label {
    font-size: 13px;
    font-weight: 700;
    color: #111827;
    letter-spacing: .01em;
}
.cal-nav {
    width: 26px; height: 26px;
    border: 1px solid #E5E7EB;
    border-radius: 6px;
    background: #fff;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    color: #6B7280;
    display: flex; align-items: center; justify-content: center;
    transition: all .12s;
}
.cal-nav:hover {
    border-color: #2563EB;
    color: #2563EB;
    background: #EFF6FF;
}

/* ── Day-of-week row ── */
.cal-dow-row {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    padding: 6px 10px 2px;
}
.cal-dow-row span {
    text-align: center;
    font-size: 10px;
    font-weight: 700;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: .05em;
}

/* ── Grid ── */
.cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    padding: 2px 10px 8px;
    gap: 2px;
}
.cal-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-size: 12px;
    color: #D1D5DB;
    cursor: default;
    position: relative;
    font-weight: 400;
    transition: all .1s;
}
/* Has data */
.cal-day.has-data {
    color: #1E40AF;
    font-weight: 700;
    background: #DBEAFE;
    cursor: pointer;
    text-decoration: none;
}
.cal-day.has-data:hover {
    background: #BFDBFE;
    transform: scale(1.08);
}
/* Latest dot */
.cal-day.is-latest::after {
    content: '';
    position: absolute;
    bottom: 3px;
    left: 50%;
    transform: translateX(-50%);
    width: 4px; height: 4px;
    border-radius: 50%;
    background: #16A34A;
}
/* Selected */
.cal-day.is-selected {
    background: #2563EB !important;
    color: #fff !important;
    box-shadow: 0 2px 8px rgba(37,99,235,.35);
}
.cal-day.is-selected::after {
    background: #fff;
}

/* ── Footer ── */
.cal-footer {
    border-top: 1px solid #F3F4F6;
    padding: 7px 12px 6px;
    text-align: center;
}
.cal-footer-latest {
    font-size: 11px;
    color: #16A34A;
    font-weight: 600;
    text-decoration: none;
    transition: color .1s;
}
.cal-footer-latest:hover { color: #15803D; text-decoration: underline; }
</style>
@endpush

@push('scripts')
<script>
    function changeLine(line) {
        const params = new URLSearchParams(window.location.search);
        params.set('line', line);
        params.delete('mesin');
        window.location.href = '{{ route("report-produksi.index") }}?' + params.toString();
    }
    function tambahMesin() {
        const nama = prompt('Nama mesin baru untuk line: {{ $selectedLine }}');
        if (nama) alert('Implement store mesin: ' + nama);
    }
    function toggleBreakMenu(e) {
        e.stopPropagation();
        document.getElementById('breakDropdown').classList.toggle('open');
        document.getElementById('calPopup').classList.remove('open');
    }

    // ── Calendar ──────────────────────────────────────────────
    (function () {
        const raw    = JSON.parse(document.getElementById('calData').textContent);
        const dates  = new Set(raw.dates);          // Set<'YYYY-MM-DD'>
        const latest = raw.latest;
        const sel    = raw.selected;
        const base   = raw.baseUrl;
        const params = raw.currentParams;

        let viewYear, viewMonth;

        // init: mulai dari bulan tanggal terpilih / terbaru
        if (sel) {
            const d = new Date(sel);
            viewYear  = d.getFullYear();
            viewMonth = d.getMonth();           // 0-based
        } else {
            const now = new Date();
            viewYear  = now.getFullYear();
            viewMonth = now.getMonth();
        }

        const MONTHS_ID = ['Januari','Februari','Maret','April','Mei','Juni',
                           'Juli','Agustus','September','Oktober','November','Desember'];

        function pad(n) { return String(n).padStart(2, '0'); }

        function buildUrl(dateStr) {
            const p = new URLSearchParams({ ...params, import_date: dateStr });
            return base + '?' + p.toString();
        }

        function render() {
            document.getElementById('calMonthLabel').textContent =
                MONTHS_ID[viewMonth] + ' ' + viewYear;

            const grid    = document.getElementById('calGrid');
            grid.innerHTML = '';

            // hari pertama bulan (0=Sun)
            const firstDay = new Date(viewYear, viewMonth, 1).getDay();
            // total hari dalam bulan
            const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

            // blank cells sebelum hari pertama
            for (let b = 0; b < firstDay; b++) {
                const blank = document.createElement('div');
                blank.className = 'cal-day';
                grid.appendChild(blank);
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = viewYear + '-' + pad(viewMonth + 1) + '-' + pad(d);
                const hasData = dates.has(dateStr);
                const isLatest   = dateStr === latest;
                const isSelected = dateStr === sel;

                const el = hasData ? document.createElement('a') : document.createElement('div');
                el.className = 'cal-day';
                el.textContent = d;

                if (hasData) {
                    el.classList.add('has-data');
                    el.href = buildUrl(dateStr);
                    el.title = dateStr;
                }
                if (isLatest)   el.classList.add('is-latest');
                if (isSelected) el.classList.add('is-selected');

                grid.appendChild(el);
            }
        }

        document.getElementById('calPrev').addEventListener('click', function (e) {
            e.stopPropagation();
            viewMonth--;
            if (viewMonth < 0) { viewMonth = 11; viewYear--; }
            render();
        });
        document.getElementById('calNext').addEventListener('click', function (e) {
            e.stopPropagation();
            viewMonth++;
            if (viewMonth > 11) { viewMonth = 0; viewYear++; }
            render();
        });

        render();
    })();

    function toggleCal(e) {
        e.stopPropagation();
        document.getElementById('calPopup').classList.toggle('open');
        const bd = document.getElementById('breakDropdown');
        if (bd) bd.classList.remove('open');
    }

    document.addEventListener('click', function (e) {
        const calWrap = document.getElementById('calWrap');
        if (calWrap && !calWrap.contains(e.target))
            document.getElementById('calPopup').classList.remove('open');

        const breakWrap = document.getElementById('breakWrap');
        if (breakWrap && !breakWrap.contains(e.target)) {
            const bd = document.getElementById('breakDropdown');
            if (bd) bd.classList.remove('open');
        }
    });
</script>
@endpush