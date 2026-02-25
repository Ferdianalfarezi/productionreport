{{-- resources/views/e-planning/import.blade.php --}}
@extends('layouts.app')

@section('title', 'Import E-Planning')

@push('styles')
<style>
    .import-wrapper {
        padding: 24px;
        max-width: 600px;
        margin: 0 auto;
    }
    .import-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0,0,0,.1);
        overflow: hidden;
    }
    .import-header {
        background: #1565C0;
        color: #fff;
        padding: 16px 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .import-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
    }
    .import-body {
        padding: 24px;
    }
    .info-box {
        background: #E3F2FD;
        border: 1px solid #90CAF9;
        border-radius: 8px;
        padding: 14px 16px;
        margin-bottom: 20px;
        font-size: 13px;
        color: #0d47a1;
    }
    .info-box .info-item {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 4px;
    }
    .info-box .info-item:last-child { margin-bottom: 0; }
    .stat-row {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
    }
    .stat-box {
        flex: 1;
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    .stat-box .stat-label {
        font-size: 11px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .stat-box .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #1565C0;
        margin-top: 2px;
    }

    /* Drop zone */
    .drop-zone {
        border: 2.5px dashed #90CAF9;
        border-radius: 10px;
        padding: 32px;
        text-align: center;
        cursor: pointer;
        transition: all .2s;
        background: #F8FBFF;
        margin-bottom: 20px;
        position: relative;
    }
    .drop-zone:hover, .drop-zone.dragover {
        border-color: #1565C0;
        background: #E3F2FD;
    }
    .drop-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }
    .drop-zone .dz-icon {
        font-size: 36px;
        margin-bottom: 8px;
    }
    .drop-zone .dz-text {
        font-size: 14px;
        color: #555;
        font-weight: 600;
    }
    .drop-zone .dz-sub {
        font-size: 12px;
        color: #999;
        margin-top: 4px;
    }
    .drop-zone .dz-selected {
        margin-top: 10px;
        font-size: 13px;
        color: #1565C0;
        font-weight: 700;
        display: none;
    }

    .btn-import {
        width: 100%;
        background: #1565C0;
        color: #fff;
        border: none;
        padding: 12px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background .2s;
    }
    .btn-import:hover { background: #0d47a1; }
    .btn-import:disabled { background: #90CAF9; cursor: not-allowed; }

    /* Alert */
    .alert-success {
        background: #E8F5E9;
        border: 1px solid #A5D6A7;
        border-radius: 8px;
        padding: 12px 16px;
        color: #1b5e20;
        font-size: 13px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .alert-error {
        background: #FFEBEE;
        border: 1px solid #EF9A9A;
        border-radius: 8px;
        padding: 12px 16px;
        color: #b71c1c;
        font-size: 13px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Warning box */
    .warn-box {
        background: #FFF8E1;
        border: 1px solid #FFD54F;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 12px;
        color: #6d4c00;
    }
    .warn-box strong { color: #e65100; }
</style>
@endpush

@section('content')
<div class="import-wrapper">

    <div class="import-card">
        <div class="import-header">
            <span>📊</span>
            <h5>Import Data E-Planning</h5>
        </div>

        <div class="import-body">

            {{-- Alert --}}
            @if(session('success'))
                <div class="alert-success">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert-error">❌ {{ session('error') }}</div>
            @endif

            {{-- Statistik data saat ini --}}
            <div class="stat-row">
                <div class="stat-box">
                    <div class="stat-label">Total Data</div>
                    <div class="stat-value">{{ number_format($totalRows) }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Import Terakhir</div>
                    <div class="stat-value" style="font-size:13px;margin-top:6px;">
                        {{ $lastImport ? \Carbon\Carbon::parse($lastImport)->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>
            </div>

            {{-- Info format --}}
            <div class="info-box">
                <div class="info-item">📋 <strong>Format file:</strong> .xlsx atau .xls</div>
                <div class="info-item">📌 <strong>Kolom wajib:</strong> ID, PART_NO_CHILD, LINE, STOCK_STORE</div>
                <div class="info-item">📌 <strong>Baris header</strong> harus mengandung kolom "ID"</div>
                <div class="info-item">📦 <strong>Maks ukuran:</strong> 10 MB</div>
            </div>

            {{-- Warning --}}
            <div class="warn-box">
                ⚠️ <strong>Perhatian:</strong> Import akan <strong>menghapus semua data lama</strong> dan menggantinya dengan data dari file yang baru.
            </div>

            {{-- Form --}}
            <form method="POST"
                  action="{{ route('e-planning.import') }}"
                  enctype="multipart/form-data"
                  id="importForm">
                @csrf

                <div class="drop-zone" id="dropZone">
                    <input type="file" name="file" id="fileInput" accept=".xlsx,.xls">
                    <div class="dz-icon">📂</div>
                    <div class="dz-text">Klik atau drag & drop file Excel di sini</div>
                    <div class="dz-sub">Format: .xlsx / .xls — Maks 10 MB</div>
                    <div class="dz-selected" id="fileName"></div>
                </div>

                @error('file')
                    <div class="alert-error" style="margin-top:-12px;margin-bottom:16px;">❌ {{ $message }}</div>
                @enderror

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
    const fileInput = document.getElementById('fileInput');
    const dropZone  = document.getElementById('dropZone');
    const fileNameEl= document.getElementById('fileName');
    const btnImport = document.getElementById('btnImport');
    const btnText   = document.getElementById('btnText');
    const form      = document.getElementById('importForm');

    fileInput.addEventListener('change', function () {
        if (this.files.length) {
            fileNameEl.textContent = '📄 ' + this.files[0].name;
            fileNameEl.style.display = 'block';
            btnImport.disabled = false;
        }
    });

    ['dragover','dragleave','drop'].forEach(evt => {
        dropZone.addEventListener(evt, e => {
            e.preventDefault();
            dropZone.classList.toggle('dragover', evt === 'dragover');
        });
    });

    form.addEventListener('submit', function () {
        btnImport.disabled = true;
        btnText.textContent = '⏳ Sedang mengimpor...';
    });
</script>
@endpush