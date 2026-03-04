{{-- resources/views/e-planning/import.blade.php --}}
@extends('layouts.app')

@section('title', 'Import E-Planning')

@push('styles')
<style>
    .import-wrapper { padding: 24px; max-width: 680px; margin: 0 auto; }
    .import-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,.1); overflow: hidden; }
    .import-header { background: #1565C0; color: #fff; padding: 16px 24px; display: flex; align-items: center; gap: 10px; }
    .import-header h5 { margin: 0; font-size: 16px; font-weight: 700; }
    .import-body { padding: 24px; }

    .info-box { background: #E3F2FD; border: 1px solid #90CAF9; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; font-size: 13px; color: #0d47a1; }
    .info-box .info-item { display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
    .info-box .info-item:last-child { margin-bottom: 0; }

    .stat-row { display: flex; gap: 12px; margin-bottom: 20px; }
    .stat-box { flex: 1; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px; text-align: center; }
    .stat-box .stat-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: .5px; }
    .stat-box .stat-value { font-size: 20px; font-weight: 700; color: #1565C0; margin-top: 2px; }

    /* File section divider */
    .file-section { margin-bottom: 20px; }
    .file-section-title {
        font-size: 13px; font-weight: 700; color: #1565C0;
        border-left: 3px solid #1565C0; padding-left: 10px;
        margin-bottom: 10px;
    }
    .file-section-title span { font-weight: 400; color: #888; font-size: 12px; margin-left: 6px; }
    .file-section-title.optional { color: #388E3C; border-color: #388E3C; }
    .file-section-title.optional span { color: #888; }

    /* Drop zone */
    .drop-zone { border: 2.5px dashed #90CAF9; border-radius: 10px; padding: 24px; text-align: center; cursor: pointer; transition: all .2s; background: #F8FBFF; position: relative; }
    .drop-zone:hover, .drop-zone.dragover { border-color: #1565C0; background: #E3F2FD; }
    .drop-zone.optional-zone { border-color: #A5D6A7; background: #F1F8F1; }
    .drop-zone.optional-zone:hover, .drop-zone.optional-zone.dragover { border-color: #388E3C; background: #E8F5E9; }
    .drop-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .drop-zone .dz-icon { font-size: 30px; margin-bottom: 6px; }
    .drop-zone .dz-text { font-size: 13px; color: #555; font-weight: 600; }
    .drop-zone .dz-sub { font-size: 12px; color: #999; margin-top: 3px; }
    .drop-zone .dz-selected { margin-top: 8px; font-size: 13px; color: #1565C0; font-weight: 700; display: none; }
    .drop-zone.optional-zone .dz-selected { color: #388E3C; }

    .btn-import { width: 100%; background: #1565C0; color: #fff; border: none; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background .2s; }
    .btn-import:hover { background: #0d47a1; }
    .btn-import:disabled { background: #90CAF9; cursor: not-allowed; }

    .alert-success { background: #E8F5E9; border: 1px solid #A5D6A7; border-radius: 8px; padding: 12px 16px; color: #1b5e20; font-size: 13px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .alert-error { background: #FFEBEE; border: 1px solid #EF9A9A; border-radius: 8px; padding: 12px 16px; color: #b71c1c; font-size: 13px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .warn-box { background: #FFF8E1; border: 1px solid #FFD54F; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 12px; color: #6d4c00; }
    .warn-box strong { color: #e65100; }

    .divider { border: none; border-top: 1px solid #e0e0e0; margin: 20px 0; }
</style>
@endpush

@section('content')
<div class="import-wrapper">
    <div class="import-card">
        <div class="import-header">
            <span>📊</span>
            <h5>Import Data E-Planning &amp; Report Production</h5>
        </div>

        <div class="import-body">

            @if(session('success'))
                <div class="alert-success">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert-error">❌ {{ session('error') }}</div>
            @endif

            {{-- Statistik --}}
            <div class="stat-row">
                <div class="stat-box">
                    <div class="stat-label">Data E-Planning</div>
                    <div class="stat-value">{{ number_format($totalRows) }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Import E-Planning</div>
                    <div class="stat-value" style="font-size:13px;margin-top:6px;">
                        {{ $lastImport ? \Carbon\Carbon::parse($lastImport)->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Data Report</div>
                    <div class="stat-value">{{ number_format($totalReportRows) }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Import Report</div>
                    <div class="stat-value" style="font-size:13px;margin-top:6px;">
                        {{ $lastReportImport ? \Carbon\Carbon::parse($lastReportImport)->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>
            </div>

            <div class="warn-box">
                ⚠️ <strong>Perhatian:</strong> Import akan <strong>menghapus semua data lama</strong> dan menggantinya dengan data dari file yang baru.
            </div>

            {{-- Form --}}
            <form method="POST"
                  action="{{ route('e-planning.import') }}"
                  enctype="multipart/form-data"
                  id="importForm">
                @csrf

                {{-- ── FILE 1: History E-Planning ── --}}
                <div class="file-section">
                    <div class="file-section-title">
                        📋 File 1 — History E-Planning <span>(Wajib)</span>
                    </div>
                    <div class="info-box" style="margin-bottom:10px;">
                        <div class="info-item">📌 <strong>Kolom wajib:</strong> ID, PART_NO_CHILD, LINE, STOCK_STORE, QTY_KBN</div>
                        <div class="info-item">📌 <strong>Baris header</strong> harus mengandung kolom "ID"</div>
                    </div>
                    <div class="drop-zone" id="dropZone1">
                        <input type="file" name="file_eplanning" id="fileEplanning" accept=".xlsx,.xls">
                        <div class="dz-icon">📂</div>
                        <div class="dz-text">Klik atau drag & drop file History E-Planning</div>
                        <div class="dz-sub">Format: .xlsx / .xls — Maks 10 MB</div>
                        <div class="dz-selected" id="fileName1"></div>
                    </div>
                    @error('file_eplanning')
                        <div class="alert-error" style="margin-top:8px;margin-bottom:0;">❌ {{ $message }}</div>
                    @enderror
                </div>

                <hr class="divider">

                {{-- ── FILE 2: Report Production ── --}}
                <div class="file-section">
                    <div class="file-section-title optional">
                        📊 File 2 — Report Production <span></span>
                    </div>
                    <div class="info-box" style="margin-bottom:10px;background:#F1F8F1;border-color:#A5D6A7;color:#1b5e20;">
                        <div class="info-item">📌 <strong>Kolom wajib:</strong> PART_NO, MACHINE_NO, QTY_OK, SHIFT</div>
                        <div class="info-item">🔢 <strong>Rumus Actual:</strong> QTY_OK ÷ QTY_KBN (dari E-Planning)</div>
                        <div class="info-item">📅 Dikelompokkan per Part + Machine + Shift</div>
                    </div>
                    <div class="drop-zone optional-zone" id="dropZone2">
                        <input type="file" name="file_report" id="fileReport" accept=".xlsx,.xls">
                        <div class="dz-icon">📂</div>
                        <div class="dz-text">Klik atau drag & drop file Report Production</div>
                        <div class="dz-sub">Format: .xlsx / .xls </div>
                        <div class="dz-selected" id="fileName2"></div>
                    </div>
                    @error('file_report')
                        <div class="alert-error" style="margin-top:8px;margin-bottom:0;">❌ {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-import" id="btnImport" disabled>
                    <span id="btnText">⬆️ Upload & Import</span>
                </button>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const zones = [
        { zone: 'dropZone1', input: 'fileEplanning', label: 'fileName1', required: true },
        { zone: 'dropZone2', input: 'fileReport',    label: 'fileName2', required: false },
    ];

    zones.forEach(({ zone, input, label }) => {
        const dz  = document.getElementById(zone);
        const inp = document.getElementById(input);
        const lbl = document.getElementById(label);

        inp.addEventListener('change', function () {
            if (this.files.length) {
                lbl.textContent     = '📄 ' + this.files[0].name;
                lbl.style.display   = 'block';
            } else {
                lbl.style.display   = 'none';
            }
            checkSubmit();
        });

        ['dragover', 'dragleave', 'drop'].forEach(evt => {
            dz.addEventListener(evt, e => {
                e.preventDefault();
                dz.classList.toggle('dragover', evt === 'dragover');
            });
        });
    });

    function checkSubmit() {
        const file1 = document.getElementById('fileEplanning');
        document.getElementById('btnImport').disabled = !(file1.files && file1.files.length > 0);
    }

    document.getElementById('importForm').addEventListener('submit', function () {
        const btn = document.getElementById('btnImport');
        btn.disabled = true;
        document.getElementById('btnText').textContent = '⏳ Sedang mengimpor...';
    });
</script>
@endpush