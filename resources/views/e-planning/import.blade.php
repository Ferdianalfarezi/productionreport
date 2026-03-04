{{-- resources/views/e-planning/import.blade.php --}}
{{-- Di-include dari layouts/app.blade.php sebagai modal --}}

<div class="bt-modal-overlay" id="importModal">
    <div class="bt-modal-box" style="max-width:560px;">

        <div class="bt-modal-hdr">
            <span>📊 Import E-Planning & Report</span>
            <button class="bt-modal-close" onclick="closeImportModal()" type="button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        @if(session('import_success'))
            <div class="bt-alert-success" style="margin:12px 20px 0;">✅ {{ session('import_success') }}</div>
        @endif
        @if(session('import_error'))
            <div style="margin:12px 20px 0;padding:10px 14px;border-radius:8px;font-size:13px;background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;">❌ {{ session('import_error') }}</div>
        @endif

        {{-- Stats strip --}}
        <div style="display:flex;gap:10px;padding:14px 20px 0;">
            <div class="imp-stat">
                <div class="imp-stat-label">E-Planning</div>
                <div class="imp-stat-val">{{ number_format($totalRows ?? 0) }}</div>
            </div>
            <div class="imp-stat">
                <div class="imp-stat-label">Last Import</div>
                <div class="imp-stat-val" style="font-size:11px;line-height:1.4;">{{ isset($lastImport) && $lastImport ? \Carbon\Carbon::parse($lastImport)->format('d/m H:i') : '—' }}</div>
            </div>
            <div class="imp-stat">
                <div class="imp-stat-label">Report</div>
                <div class="imp-stat-val">{{ number_format($totalReportRows ?? 0) }}</div>
            </div>
            <div class="imp-stat">
                <div class="imp-stat-label">Last Import</div>
                <div class="imp-stat-val" style="font-size:11px;line-height:1.4;">{{ isset($lastReportImport) && $lastReportImport ? \Carbon\Carbon::parse($lastReportImport)->format('d/m H:i') : '—' }}</div>
            </div>
        </div>

        <div style="margin:10px 20px;padding:8px 12px;background:#FFFBEB;border:1px solid #FCD34D;border-radius:8px;font-size:11.5px;color:#92400E;">
            ⚠️ Import akan <strong>menghapus semua data lama</strong> dan menggantinya dengan data baru.
        </div>

        <form method="POST" action="{{ route('e-planning.import') }}" enctype="multipart/form-data" id="importModalForm">
            @csrf
            <div style="padding:0 20px 20px;display:flex;flex-direction:column;gap:14px;">

                {{-- File 1 --}}
                <div>
                    <div class="imp-file-label">📋 History E-Planning <span style="color:#991B1B;font-size:10px;font-weight:700;"></span></div>
                    <div class="imp-drop" id="impDrop1" onclick="document.getElementById('impFile1').click()">
                        <input type="file" name="file_eplanning" id="impFile1" accept=".xlsx,.xls" style="display:none;" onchange="impFileChanged(this,'impDrop1','impName1')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8A90A2" stroke-width="1.8"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <span class="imp-drop-text" id="impName1">Klik untuk pilih file .xlsx / .xls</span>
                    </div>
                </div>

                {{-- File 2 --}}
                <div>
                    <div class="imp-file-label">📊 Report Production <span style="color:#8A90A2;font-size:10px;"></span></div>
                    <div class="imp-drop imp-drop-opt" id="impDrop2" onclick="document.getElementById('impFile2').click()">
                        <input type="file" name="file_report" id="impFile2" accept=".xlsx,.xls" style="display:none;" onchange="impFileChanged(this,'impDrop2','impName2')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8A90A2" stroke-width="1.8"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <span class="imp-drop-text" id="impName2">Klik untuk pilih file .xlsx / .xls</span>
                    </div>
                </div>

                <button type="submit" class="bt-btn bt-btn-primary" id="impSubmitBtn" disabled style="width:100%;justify-content:center;padding:10px;">
                    <span id="impBtnText">⬆ Upload & Import</span>
                </button>
            </div>
        </form>
    </div>
</div>