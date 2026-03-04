@extends('layouts.app')

@section('title', 'Konfigurasi Break Time')

@push('styles')
<style>
    * { box-sizing: border-box; }
    .page-wrapper { padding: 16px; background: #f0f0f0; min-height: 100vh; }
    .page-title { font-size: 16px; font-weight: 700; color: #1565C0; margin-bottom: 14px; }

    .card { background: #fff; border: 1.5px solid #ddd; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,.08); margin-bottom: 20px; }
    .card-title { font-size: 13px; font-weight: 700; color: #333; margin-bottom: 14px; border-bottom: 1.5px solid #eee; padding-bottom: 8px; }

    .form-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: 10px; align-items: end; }
    .form-group label { display: block; font-size: 11px; font-weight: 600; color: #555; margin-bottom: 4px; }
    .form-control {
        width: 100%; padding: 7px 10px; border: 1.5px solid #ccc; border-radius: 6px;
        font-size: 13px; color: #333; background: #fff;
    }
    .form-control:focus { outline: none; border-color: #1565C0; box-shadow: 0 0 0 3px rgba(21,101,192,.12); }

    .btn { padding: 7px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; }
    .btn-primary { background: #1565C0; color: #fff; }
    .btn-primary:hover { background: #0d47a1; }
    .btn-danger  { background: #e53935; color: #fff; padding: 4px 10px; font-size: 12px; }
    .btn-danger:hover  { background: #b71c1c; }
    .btn-warning { background: #f9a825; color: #fff; padding: 4px 10px; font-size: 12px; }
    .btn-warning:hover { background: #f57f17; }

    .bt-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .bt-table th { background: #BBDEFB; color: #0d47a1; font-weight: 700; padding: 8px 10px; border: 1px solid #90CAF9; text-align: center; }
    .bt-table td { padding: 7px 10px; border: 1px solid #ddd; text-align: center; vertical-align: middle; }
    .bt-table tr:nth-child(even) td { background: #f5f9ff; }
    .bt-table tr:hover td { background: #E3F2FD; }

    .badge-shift { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }
    .badge-1   { background: #C8E6C9; color: #1b5e20; }
    .badge-2   { background: #FFE0B2; color: #e65100; }
    .badge-all { background: #E1BEE7; color: #4a148c; }

    .badge-active   { background: #C8E6C9; color: #1b5e20; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }
    .badge-inactive { background: #FFCDD2; color: #b71c1c; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }

    .alert { padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 14px; }
    .alert-success { background: #E8F5E9; color: #1b5e20; border: 1px solid #A5D6A7; }

    /* Modal edit */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 999; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: #fff; border-radius: 10px; padding: 24px; width: 420px; box-shadow: 0 8px 32px rgba(0,0,0,.2); }
    .modal-title { font-size: 14px; font-weight: 700; color: #1565C0; margin-bottom: 16px; }
    .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px; }
    .btn-secondary { background: #eee; color: #333; }
    .btn-secondary:hover { background: #ddd; }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-title">⚙️ Konfigurasi Break Time</div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── FORM TAMBAH ── --}}
    <div class="card">
        <div class="card-title">Tambah Break Time</div>
        <form method="POST" action="{{ route('break-times.store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama Istirahat</label>
                    <input type="text" name="break_name" class="form-control" placeholder="e.g. Istirahat Pagi" required>
                </div>
                <div class="form-group">
                    <label>Shift</label>
                    <select name="shift" class="form-control">
                        <option value="">Semua Shift</option>
                        <option value="1">Shift 1</option>
                        <option value="2">Shift 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jam Mulai</label>
                    <input type="time" name="break_start" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Durasi (menit)</label>
                    <input type="number" name="duration" class="form-control" min="1" max="480" placeholder="e.g. 30" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width:100%;">+ Tambah</button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── TABEL DATA ── --}}
    <div class="card">
        <div class="card-title">Daftar Break Time</div>
        @if($breakTimes->isEmpty())
            <p style="color:#999;font-size:13px;text-align:center;padding:30px 0;">Belum ada konfigurasi break time.</p>
        @else
        <table class="bt-table">
            <thead>
                <tr>
                    <th style="width:40px;">No</th>
                    <th>Nama Istirahat</th>
                    <th>Shift</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Durasi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($breakTimes as $i => $bt)
                    @php
                        [$bh, $bm] = array_map('intval', explode(':', $bt->break_start));
                        $endMin = $bh * 60 + $bm + $bt->duration;
                        $endStr = sprintf('%02d:%02d', intdiv($endMin, 60) % 24, $endMin % 60);
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="text-align:left;font-weight:600;">{{ $bt->break_name }}</td>
                        <td>
                            @if($bt->shift == 1)
                                <span class="badge-shift badge-1">Shift 1</span>
                            @elseif($bt->shift == 2)
                                <span class="badge-shift badge-2">Shift 2</span>
                            @else
                                <span class="badge-shift badge-all">Semua</span>
                            @endif
                        </td>
                        <td>{{ \Str::substr($bt->break_start, 0, 5) }}</td>
                        <td>{{ $endStr }}</td>
                        <td>{{ $bt->duration }} menit</td>
                        <td>
                            @if($bt->is_active)
                                <span class="badge-active">Aktif</span>
                            @else
                                <span class="badge-inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td style="display:flex;gap:6px;justify-content:center;">
                            <button class="btn btn-warning"
                                onclick="openEdit({{ $bt->id }}, '{{ $bt->break_name }}', '{{ $bt->shift }}', '{{ \Str::substr($bt->break_start,0,5) }}', {{ $bt->duration }}, {{ $bt->is_active ? 1 : 0 }})">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('break-times.destroy', $bt) }}" onsubmit="return confirm('Hapus break time ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- ── MODAL EDIT ── --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-title">Edit Break Time</div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-grid">
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Nama Istirahat</label>
                    <input type="text" name="break_name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Shift</label>
                    <select name="shift" id="edit_shift" class="form-control">
                        <option value="">Semua Shift</option>
                        <option value="1">Shift 1</option>
                        <option value="2">Shift 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" id="edit_active" class="form-control">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jam Mulai</label>
                    <input type="time" name="break_start" id="edit_start" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Durasi (menit)</label>
                    <input type="number" name="duration" id="edit_duration" class="form-control" min="1" max="480" required>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeEdit()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEdit(id, name, shift, start, duration, isActive) {
        document.getElementById('editForm').action = '/break-times/' + id;
        document.getElementById('edit_name').value     = name;
        document.getElementById('edit_shift').value    = shift;
        document.getElementById('edit_start').value    = start;
        document.getElementById('edit_duration').value = duration;
        document.getElementById('edit_active').value   = isActive;
        document.getElementById('editModal').classList.add('open');
    }
    function closeEdit() {
        document.getElementById('editModal').classList.remove('open');
    }
    // Tutup modal jika klik overlay
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEdit();
    });
</script>
@endpush
