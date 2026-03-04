{{-- resources/views/break-times/index.blade.php --}}
{{-- Dirender sebagai modal overlay, di-include dari view manapun --}}

<!-- ══ MODAL BREAK TIME ══ -->
<div class="bt-modal-overlay" id="btModal">
    <div class="bt-modal-box">

        <div class="bt-modal-hdr">
            <span>⚙ Konfigurasi Break Time</span>
            <button class="bt-modal-close" onclick="closeBtModal()" type="button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        @if(session('success'))
            <div class="bt-alert-success">{{ session('success') }}</div>
        @endif

        {{-- ── FORM TAMBAH ── --}}
        <div class="bt-section-title">Tambah Break Time</div>
        <form method="POST" action="{{ route('break-times.store') }}">
            @csrf
            <div class="bt-form-grid" style="grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;">
                <div class="bt-form-group">
                    <label>Nama Istirahat</label>
                    <input type="text" name="break_name" class="bt-input" placeholder="e.g. Istirahat Pagi" required>
                </div>
                <div class="bt-form-group">
                    <label>Shift</label>
                    <select name="shift" class="bt-input">
                        <option value="">Semua Shift</option>
                        <option value="1">Shift 1</option>
                        <option value="2">Shift 2</option>
                    </select>
                </div>
                <div class="bt-form-group">
                    <label>Status</label>
                    <select name="is_active" class="bt-input">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="bt-form-group">
                    <label>Jam Mulai</label>
                    <input type="time" name="break_start" class="bt-input" required>
                </div>
                <div class="bt-form-group">
                    <label>Durasi (menit)</label>
                    <input type="number" name="duration" class="bt-input" min="1" max="480" placeholder="30" required>
                </div>
                <div class="bt-form-group" style="display:flex; flex-direction:column; justify-content:flex-end;">
                    <label style="visibility:hidden;">_</label>
                    <button type="submit" class="bt-btn bt-btn-primary" style="width:100%;">+ Tambah</button>
                </div>
            </div>
        </form>

        {{-- ── TABEL ── --}}
        <div class="bt-section-title" style="margin-top:16px;">Daftar Break Time</div>
        @if($breakTimes->isEmpty())
            <p class="bt-empty">Belum ada konfigurasi break time.</p>
        @else
        <div class="bt-table-wrap">
            <table class="bt-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Shift</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
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
                                    <span class="bt-badge bt-badge-s1">Shift 1</span>
                                @elseif($bt->shift == 2)
                                    <span class="bt-badge bt-badge-s2">Shift 2</span>
                                @else
                                    <span class="bt-badge bt-badge-all">Semua</span>
                                @endif
                            </td>
                            <td>{{ \Str::substr($bt->break_start, 0, 5) }}</td>
                            <td>{{ $endStr }}</td>
                            <td>{{ $bt->duration }}m</td>
                            <td>
                                @if($bt->is_active)
                                    <span class="bt-badge bt-badge-active">Aktif</span>
                                @else
                                    <span class="bt-badge bt-badge-inactive">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:5px;justify-content:center;">
                                    <button class="bt-btn bt-btn-sm bt-btn-warning"
                                        onclick="openBtEdit({{ $bt->id }}, '{{ addslashes($bt->break_name) }}', '{{ $bt->shift }}', '{{ \Str::substr($bt->break_start,0,5) }}', {{ $bt->duration }}, {{ $bt->is_active ? 1 : 0 }})">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('break-times.destroy', $bt) }}" onsubmit="return confirm('Hapus break time ini?')" style="margin:0;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="bt-btn bt-btn-sm bt-btn-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- ── MODAL EDIT (nested) ── --}}
<div class="bt-modal-overlay" id="btEditModal" style="z-index:10001;">
    <div class="bt-modal-box" style="max-width:440px;">
        <div class="bt-modal-hdr">
            <span>Edit Break Time</span>
            <button class="bt-modal-close" onclick="closeBtEdit()" type="button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST" id="btEditForm">
            @csrf @method('PUT')
            <div class="bt-form-grid">
                <div class="bt-form-group" style="grid-column: span 4;">
                    <label>Nama Istirahat</label>
                    <input type="text" name="break_name" id="bte_name" class="bt-input" required>
                </div>
                <div class="bt-form-group">
                    <label>Shift</label>
                    <select name="shift" id="bte_shift" class="bt-input">
                        <option value="">Semua Shift</option>
                        <option value="1">Shift 1</option>
                        <option value="2">Shift 2</option>
                    </select>
                </div>
                <div class="bt-form-group">
                    <label>Status</label>
                    <select name="is_active" id="bte_active" class="bt-input">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="bt-form-group">
                    <label>Jam Mulai</label>
                    <input type="time" name="break_start" id="bte_start" class="bt-input" required>
                </div>
                <div class="bt-form-group">
                    <label>Durasi (menit)</label>
                    <input type="number" name="duration" id="bte_duration" class="bt-input" min="1" max="480" required>
                </div>
                <div class="bt-form-group" style="grid-column: span 4; display:flex; gap:8px; justify-content:flex-end; margin-top:4px;">
                    <button type="button" class="bt-btn bt-btn-secondary" onclick="closeBtEdit()">Batal</button>
                    <button type="submit" class="bt-btn bt-btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>