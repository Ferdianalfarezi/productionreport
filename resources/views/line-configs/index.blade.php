@extends('layouts.app')

@section('title', 'Line Config')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Line Config</h1>
            <p class="text-gray-600 mt-1">Konfigurasi mesin per line produksi</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="openImportModal()"
                class="flex items-center space-x-2 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Import Excel</span>
            </button>
            <button onclick="openCreateModal()"
                class="flex items-center space-x-2 bg-gray-900 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-gray-700 transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Tambah Config</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" id="searchInput" oninput="applyFilters()"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 text-sm transition"
                    placeholder="Cari line atau mesin...">
            </div>

            <select id="filterLine" onchange="applyFilters()"
                class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition min-w-[180px]">
                <option value="">Semua Line</option>
                @foreach($lines as $line)
                    <option value="{{ strtolower($line) }}">{{ $line }}</option>
                @endforeach
            </select>

            <button onclick="resetFilters()"
                class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition whitespace-nowrap">
                Reset Filter
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-12">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">Line</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mesin</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">Update By</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="tableBody">
                    @forelse($grouped as $lineName => $data)
                        @php $mesinStr = implode(', ', $data['mesins']); @endphp
                        <tr class="hover:bg-gray-50 transition config-row"
                            data-search="{{ strtolower($lineName . ' ' . $mesinStr) }}"
                            data-line="{{ strtolower($lineName) }}"
                            data-ids="{{ json_encode($data['ids']) }}"
                            data-line-name="{{ $lineName }}">
                            <td class="px-6 py-4 text-sm text-gray-500 row-no">{{ $loop->iteration }}</td>

                            {{-- Kolom LINE – hanya tampil 1 kali --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold tracking-wide whitespace-nowrap">
                                    {{ $lineName }}
                                </span>
                            </td>

                            {{-- Kolom MESIN – semua mesin dalam 1 baris sebagai badge --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5 mesin-badges">
                                    @foreach($data['mesins'] as $idx => $mesin)
                                        <span class="group relative inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold hover:bg-red-50 hover:text-red-700 transition cursor-default"
                                              data-id="{{ $data['ids'][$idx] }}"
                                              data-mesin="{{ $mesin }}">
                                            {{ $mesin }}
                                            {{-- Tombol hapus mesin individual --}}
                                            <button type="button"
                                                onclick="deleteMesin({{ $data['ids'][$idx] }}, '{{ addslashes($mesin) }}')"
                                                class="hidden group-hover:inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-red-200 hover:bg-red-500 hover:text-white text-red-600 transition ml-0.5"
                                                title="Hapus mesin ini">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </span>
                                    @endforeach
                                    {{-- Tombol tambah mesin ke line ini --}}
                                    <button onclick="openAddMesinModal('{{ addslashes($lineName) }}')"
                                        class="inline-flex items-center px-2 py-1 border border-dashed border-gray-300 text-gray-400 rounded-full text-xs hover:border-gray-500 hover:text-gray-600 transition"
                                        title="Tambah mesin ke line ini">
                                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Tambah
                                    </button>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-xs text-gray-400">{{ $data['update_by'] ?? '-' }}</td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center">
                                    <button onclick="deleteLineGroup('{{ addslashes($lineName) }}')" title="Hapus semua mesin pada line ini"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-xs font-semibold hover:bg-red-100 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus Line
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V9m-7-6v6h6M9 3l6 6"/>
                                </svg>
                                <p class="text-gray-600 font-semibold">Belum ada data line config</p>
                                <p class="text-gray-400 text-sm mt-1">Klik "Tambah Config" atau import dari Excel</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
            <p class="text-sm text-gray-600">
                Total <span id="totalCount" class="font-semibold text-gray-900">{{ count($grouped) }}</span> line,
                <span class="font-semibold text-gray-900">{{ $all->count() }}</span> mesin terdaftar
            </p>
        </div>
    </div>
</div>

{{-- ==================== MODAL CREATE ==================== --}}
<div id="createModal" class="modal-backdrop fixed inset-0 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Tambah Line Config</h2>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="createForm" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Line <span class="text-red-500">*</span></label>
                <input type="text" name="line" id="createLine" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition"
                    placeholder="Contoh: SW LINE 1 A">
                <span class="text-red-500 text-xs mt-1 block" id="err-create-line"></span>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mesin <span class="text-red-500">*</span></label>
                <input type="text" name="mesin" id="createMesin" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition"
                    placeholder="Contoh: SW 1.0">
                <span class="text-red-500 text-xs mt-1 block" id="err-create-mesin"></span>
            </div>
            <div class="flex justify-end space-x-3 pt-2 border-t border-gray-200">
                <button type="button" onclick="closeCreateModal()"
                    class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Batal</button>
                <button type="submit"
                    class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ==================== MODAL ADD MESIN (ke line existing) ==================== --}}
<div id="addMesinModal" class="modal-backdrop fixed inset-0 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Tambah Mesin</h2>
                <p class="text-sm text-blue-600 font-medium mt-0.5" id="addMesinLineName"></p>
            </div>
            <button onclick="closeAddMesinModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="addMesinForm" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="addMesinLineValue" name="line">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Mesin <span class="text-red-500">*</span></label>
                <input type="text" name="mesin" id="addMesinInput" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition"
                    placeholder="Contoh: SW 3.0">
                <span class="text-red-500 text-xs mt-1 block" id="err-addmesin-mesin"></span>
            </div>
            <div class="flex justify-end space-x-3 pt-2 border-t border-gray-200">
                <button type="button" onclick="closeAddMesinModal()"
                    class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Batal</button>
                <button type="submit"
                    class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ==================== MODAL IMPORT ==================== --}}
<div id="importModal" class="modal-backdrop fixed inset-0 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Import Excel</h2>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="importForm" class="p-6 space-y-4">
            @csrf
            <!-- Info format -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-sm font-semibold text-blue-800 mb-2">Format Excel yang diharapkan:</p>
                <div class="overflow-x-auto">
                    <table class="text-xs text-blue-700 w-full border border-blue-200 rounded-lg overflow-hidden">
                        <thead class="bg-blue-100">
                            <tr>
                                <th class="px-3 py-2 text-left border-r border-blue-200">Kolom A (Line)</th>
                                <th class="px-3 py-2 text-left">Kolom B (Mesin)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-t border-blue-200">
                                <td class="px-3 py-1.5 border-r border-blue-200">SW LINE 1 A</td>
                                <td class="px-3 py-1.5">SW 1.0</td>
                            </tr>
                            <tr class="border-t border-blue-200 bg-blue-50">
                                <td class="px-3 py-1.5 border-r border-blue-200">SW LINE 1 A</td>
                                <td class="px-3 py-1.5">SW 1.1</td>
                            </tr>
                            <tr class="border-t border-blue-200">
                                <td class="px-3 py-1.5 border-r border-blue-200">SW LINE 2 A</td>
                                <td class="px-3 py-1.5">SW 3.0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-blue-600 mt-2">⚠ Tanpa baris header. Duplikat otomatis dilewati.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pilih File <span class="text-red-500">*</span></label>
                <input type="file" name="file" accept=".xlsx,.xls" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-900 file:text-white hover:file:bg-gray-700">
            </div>

            <div class="flex justify-end space-x-3 pt-2 border-t border-gray-200">
                <button type="button" onclick="closeImportModal()"
                    class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Batal</button>
                <button type="submit" id="importBtn"
                    class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition">Import</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ===================== FILTER =====================
let allRows = [];

document.addEventListener('DOMContentLoaded', function () {
    allRows = Array.from(document.querySelectorAll('.config-row'));
    // Update nomor baris awal
    updateRowNumbers();
});

function applyFilters() {
    const q     = document.getElementById('searchInput').value.toLowerCase().trim();
    const fLine = document.getElementById('filterLine').value;

    allRows.forEach(r => {
        const matchSearch = !q     || r.dataset.search.includes(q);
        const matchLine   = !fLine || r.dataset.line === fLine;
        r.style.display = (matchSearch && matchLine) ? '' : 'none';
    });

    updateRowNumbers();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterLine').value  = '';
    allRows.forEach(r => r.style.display = '');
    updateRowNumbers();
}

function updateRowNumbers() {
    let no = 1;
    allRows.forEach(r => {
        if (r.style.display !== 'none') {
            r.querySelector('.row-no').textContent = no++;
        }
    });
}

// ===================== MODAL HELPERS =====================
function showModal(id) {
    const m = document.getElementById(id);
    m.classList.remove('hidden');
    m.classList.add('flex');
    requestAnimationFrame(() => m.classList.add('modal-open'));
    document.body.style.overflow = 'hidden';
}
function hideModal(id) {
    const m = document.getElementById(id);
    m.classList.remove('modal-open');
    m.classList.add('modal-closing');
    setTimeout(() => {
        m.classList.add('hidden');
        m.classList.remove('flex', 'modal-closing');
        document.body.style.overflow = '';
    }, 280);
}

function openCreateModal()        { document.getElementById('createForm').reset(); clearErrors('create'); showModal('createModal'); }
function closeCreateModal()       { hideModal('createModal'); }
function openImportModal()        { document.getElementById('importForm').reset(); showModal('importModal'); }
function closeImportModal()       { hideModal('importModal'); }
function openAddMesinModal(line)  {
    document.getElementById('addMesinForm').reset();
    document.getElementById('addMesinLineName').textContent  = line;
    document.getElementById('addMesinLineValue').value       = line;
    clearErrors('addmesin');
    showModal('addMesinModal');
    setTimeout(() => document.getElementById('addMesinInput').focus(), 300);
}
function closeAddMesinModal()     { hideModal('addMesinModal'); }

// ===================== CRUD EVENTS =====================
document.addEventListener('DOMContentLoaded', function () {

    // Create
    document.getElementById('createForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors('create');
        const fd = new FormData(this);
        try {
            const res  = await fetch('/line-configs', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }, body: fd });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500 });
                location.reload();
            } else {
                if (data.errors) showErrors(data.errors, 'create');
                else Swal.fire('Error!', data.message, 'error');
            }
        } catch(e) { Swal.fire('Error!', 'Terjadi kesalahan.', 'error'); }
    });

    // Add Mesin ke line existing
    document.getElementById('addMesinForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors('addmesin');
        const fd = new FormData(this);
        try {
            const res  = await fetch('/line-configs', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }, body: fd });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500 });
                location.reload();
            } else {
                if (data.errors) showErrors(data.errors, 'addmesin');
                else Swal.fire('Error!', data.message, 'error');
            }
        } catch(e) { Swal.fire('Error!', 'Terjadi kesalahan.', 'error'); }
    });

    // Import
    document.getElementById('importForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const fd  = new FormData(this);
        const btn = document.getElementById('importBtn');
        btn.disabled = true; btn.textContent = 'Mengimport...';
        try {
            const res  = await fetch('/line-configs/import', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }, body: fd });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({ icon: 'success', title: 'Import Berhasil!', text: data.message, showConfirmButton: false, timer: 2000 });
                location.reload();
            } else { Swal.fire('Gagal!', data.message, 'error'); }
        } catch(e) { Swal.fire('Error!', 'Terjadi kesalahan.', 'error'); }
        finally { btn.disabled = false; btn.textContent = 'Import'; }
    });
});

// Hapus 1 mesin (badge individual)
async function deleteMesin(id, mesin) {
    const result = await Swal.fire({
        title: 'Hapus mesin?',
        html: `Mesin <strong>${mesin}</strong> akan dihapus dari list.`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#1f2937', cancelButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
    });
    if (!result.isConfirmed) return;
    try {
        const res  = await fetch(`/line-configs/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Terhapus!', text: data.message, showConfirmButton: false, timer: 1400 });
            location.reload();
        } else { Swal.fire('Error!', data.message, 'error'); }
    } catch(e) { Swal.fire('Error!', 'Gagal menghapus!', 'error'); }
}

// Hapus semua mesin dalam 1 line sekaligus
async function deleteLineGroup(lineName) {
    const result = await Swal.fire({
        title: 'Hapus seluruh line?',
        html: `Semua mesin pada line <strong>${lineName}</strong> akan dihapus permanen!`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus Semua!', cancelButtonText: 'Batal'
    });
    if (!result.isConfirmed) return;
    try {
        const fd = new FormData();
        fd.append('line', lineName);
        const res  = await fetch('/line-configs/destroy-line', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'X-HTTP-Method-Override': 'DELETE' }, body: fd });
        const data = await res.json();
        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Terhapus!', text: data.message, showConfirmButton: false, timer: 1500 });
            location.reload();
        } else { Swal.fire('Error!', data.message, 'error'); }
    } catch(e) { Swal.fire('Error!', 'Gagal menghapus!', 'error'); }
}

function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
function showErrors(errors, prefix) {
    Object.keys(errors).forEach(k => {
        const el = document.getElementById(`err-${prefix}-${k}`);
        if (el) el.textContent = errors[k][0];
    });
}
function clearErrors(prefix) {
    document.querySelectorAll(`[id^="err-${prefix}-"]`).forEach(el => el.textContent = '');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeCreateModal(); closeImportModal(); closeAddMesinModal(); }
});
</script>
@endpush