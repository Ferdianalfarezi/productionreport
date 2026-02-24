@extends('layouts.app')

@section('title', 'Data Mesin')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Data Mesin</h1>
            <p class="text-gray-600 mt-1">Manajemen data mesin produksi</p>
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
                <span>Tambah Mesin</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            <!-- Search -->
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" id="searchInput" onkeyup="applyFilters()"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 text-sm transition"
                    placeholder="Cari line machine atau line...">
            </div>

            <!-- Filter Line Machine -->
            <select id="filterLineMachine" onchange="applyFilters()"
                class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition min-w-[180px]">
                <option value="">Semua Line Machine</option>
                @foreach($lineMachines as $lm)
                    <option value="{{ strtolower($lm) }}">{{ $lm }}</option>
                @endforeach
            </select>

            <!-- Filter Line -->
            <select id="filterLine" onchange="applyFilters()"
                class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition min-w-[150px]">
                <option value="">Semua Line</option>
                @foreach($lines as $line)
                    <option value="{{ strtolower($line) }}">{{ $line }}</option>
                @endforeach
            </select>

            <!-- Reset Filter -->
            <button onclick="resetFilters()"
                class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition whitespace-nowrap">
                Reset Filter
            </button>

            <!-- Per Page -->
            <select id="perPageSelect" onchange="changePerPage()"
                class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition">
                <option value="20">20 per halaman</option>
                <option value="50">50 per halaman</option>
                <option value="100">100 per halaman</option>
                <option value="all">Semua</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Line Machine</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Line</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Mesin</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Avg GSPH</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Update</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="mesinTableBody">
                    @forelse($groups as $group)
                        <tr class="hover:bg-gray-50 transition mesin-row"
                            data-search="{{ strtolower($group->line_machine . ' ' . $group->line_list) }}"
                            data-line-machine="{{ strtolower($group->line_machine) }}"
                            data-line="{{ strtolower($group->line_list) }}">
    
                            <td class="px-6 py-4 text-sm text-gray-600 row-no">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900 text-sm">{{ $group->line_machine }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                    {{ $group->line_list ?: '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">
                                    {{ $group->total_mesin }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-gray-600">
                                {{ $group->total_mesin > 1 ? number_format($group->avg_gsph, 0) : number_format($group->avg_gsph, 0) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $group->last_update ? \Carbon\Carbon::parse($group->last_update)->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @php $firstId = \App\Models\Mesin::where('line_machine', $group->line_machine)->value('id'); @endphp
                                <div class="flex items-center justify-center space-x-1">
                                    {{-- Detail --}}
                                    <button onclick="openDetailModal('{{ addslashes($group->line_machine) }}')"
                                        title="Detail"
                                        class="w-8 h-8 flex items-center justify-center bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    {{-- Edit --}}
                                    <button onclick="{{ $group->total_mesin == 1 ? 'openEditModal(' . $firstId . ')' : "openDetailModal('" . addslashes($group->line_machine) . "')" }}"
                                        title="{{ $group->total_mesin == 1 ? 'Edit' : 'Edit via Detail' }}"
                                        class="w-8 h-8 flex items-center justify-center bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    {{-- Hapus --}}
                                    <button onclick="{{ $group->total_mesin == 1 ? 'deleteMesin(' . $firstId . ')' : "openDetailModal('" . addslashes($group->line_machine) . "')" }}"
                                        title="{{ $group->total_mesin == 1 ? 'Hapus' : 'Hapus via Detail' }}"
                                        class="w-8 h-8 flex items-center justify-center bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <p class="text-gray-600 font-semibold">Belum ada data mesin</p>
                                <p class="text-gray-400 text-sm mt-1">Klik "Tambah Mesin" atau import dari Excel</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer + Pagination -->
        <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-gray-600">
                    Menampilkan <span id="showFrom" class="font-semibold text-gray-900">1</span>–<span id="showTo" class="font-semibold text-gray-900">20</span>
                    dari <span id="totalCount" class="font-semibold text-gray-900">{{ $groups->count() }}</span> line machine
                </p>
                <div class="flex items-center space-x-1" id="paginationControls">
                    <!-- Dirender oleh JS -->
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
@include('mesin.create')
@include('mesin.edit')
@include('mesin.detail')
@include('mesin.import')

@endsection

@push('scripts')
<script>
let allRows = [];
let filteredRows = [];
let currentPerPage = 20;
let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {
    allRows = Array.from(document.querySelectorAll('.mesin-row'));
    filteredRows = [...allRows];
    renderTable();
});

// Ganti searchTable() → applyFilters()
function applyFilters() {
    const q         = document.getElementById('searchInput').value.toLowerCase().trim();
    const fLM       = document.getElementById('filterLineMachine').value;
    const fLine     = document.getElementById('filterLine').value;

    filteredRows = allRows.filter(r => {
        const matchSearch   = !q     || r.dataset.search.includes(q);
        const matchLM       = !fLM   || r.dataset.lineMachine === fLM;
        // line_list bisa berisi multiple values (GROUP_CONCAT), cukup pakai includes
        const matchLine     = !fLine || r.dataset.line.includes(fLine);
        return matchSearch && matchLM && matchLine;
    });

    currentPage = 1;
    renderTable();
}

function resetFilters() {
    document.getElementById('searchInput').value        = '';
    document.getElementById('filterLineMachine').value  = '';
    document.getElementById('filterLine').value         = '';
    filteredRows = [...allRows];
    currentPage  = 1;
    renderTable();
}

// changePerPage() tetap sama, tapi hapus searchTable() lama
function changePerPage() {
    const val = document.getElementById('perPageSelect').value;
    currentPerPage = val === 'all' ? 999999 : parseInt(val);
    currentPage = 1;
    renderTable();
}



function renderTable() {
    const total = filteredRows.length;
    const perPage = currentPerPage;
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * perPage;
    const end   = Math.min(start + perPage, total);

    // Show/hide rows
    allRows.forEach(r => r.style.display = 'none');
    filteredRows.forEach((r, i) => {
        r.style.display = (i >= start && i < end) ? '' : 'none';
    });

    // Update footer info
    document.getElementById('showFrom').textContent = total === 0 ? 0 : start + 1;
    document.getElementById('showTo').textContent   = end;
    document.getElementById('totalCount').textContent = total;

    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    const ctrl = document.getElementById('paginationControls');
    if (!ctrl) return;

    if (totalPages <= 1) { ctrl.innerHTML = ''; return; }

    const btnBase = 'w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition';
    const btnActive = btnBase + ' bg-gray-900 text-white';
    const btnInactive = btnBase + ' border border-gray-300 text-gray-600 hover:bg-gray-100';
    const btnDisabled = btnBase + ' border border-gray-200 text-gray-300 cursor-not-allowed';

    let html = '';

    // Prev
    if (currentPage > 1) {
        html += `<button onclick="goPage(${currentPage-1})" class="${btnInactive}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>`;
    } else {
        html += `<span class="${btnDisabled}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </span>`;
    }

    // Page numbers - show max 5 pages around current
    let pages = [];
    if (totalPages <= 7) {
        for (let i = 1; i <= totalPages; i++) pages.push(i);
    } else {
        pages.push(1);
        if (currentPage > 3) pages.push('...');
        for (let i = Math.max(2, currentPage-1); i <= Math.min(totalPages-1, currentPage+1); i++) pages.push(i);
        if (currentPage < totalPages - 2) pages.push('...');
        pages.push(totalPages);
    }

    pages.forEach(p => {
        if (p === '...') {
            html += `<span class="${btnBase} text-gray-400">…</span>`;
        } else {
            html += `<button onclick="goPage(${p})" class="${p === currentPage ? btnActive : btnInactive}">${p}</button>`;
        }
    });

    // Next
    if (currentPage < totalPages) {
        html += `<button onclick="goPage(${currentPage+1})" class="${btnInactive}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>`;
    } else {
        html += `<span class="${btnDisabled}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </span>`;
    }

    ctrl.innerHTML = html;
}

function goPage(page) {
    currentPage = page;
    renderTable();
    // Scroll to top of table smoothly
    document.getElementById('mesinTableBody').closest('.bg-white').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ======== MODALS ========
function openCreateModal() {
    document.getElementById('createForm').reset();
    clearErrors('create');
    showModal('createModal');
}
function closeCreateModal() { hideModal('createModal'); }

async function openEditModal(id) {
    try {
        const res = await fetch(`/mesin/${id}/edit`);
        const data = await res.json();
        if (data.success) {
            const m = data.data;
            document.getElementById('editId').value = m.id;
            document.getElementById('editLineMachine').value = m.line_machine;
            document.getElementById('editMachineNo').value = m.machine_no;
            document.getElementById('editTonage').value = m.tonage || '';
            document.getElementById('editLine').value = m.line || '';
            document.getElementById('editGsph').value = m.gsph_theory;
            document.getElementById('editRemarks').value = m.remarks || '';
            document.getElementById('editSwLine').value = m.sw_line || '';
            document.getElementById('editSwNo').value = m.sw_no || '';
            clearErrors('edit');
            showModal('editModal');
        }
    } catch(e) {
        Swal.fire('Error!', 'Gagal memuat data.', 'error');
    }
}
function closeEditModal() { hideModal('editModal'); }

async function openDetailModal(lineMachine) {
    try {
        const res = await fetch(`/mesin/group/${encodeURIComponent(lineMachine)}`);
        const data = await res.json();
        if (data.success) {
            renderDetailModal(data);
            showModal('detailModal');
        }
    } catch(e) {
        Swal.fire('Error!', 'Gagal memuat data.', 'error');
    }
}
function closeDetailModal() { hideModal('detailModal'); }

function openImportModal() { showModal('importModal'); }
function closeImportModal() { hideModal('importModal'); }

function showModal(id) {
    const m = document.getElementById(id);
    // Reset state
    m.classList.remove('hidden');
    m.classList.add('flex');
    // Force reflow
    m.offsetHeight;
    // Trigger animation
    requestAnimationFrame(() => {
        m.classList.add('modal-open');
    });
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

// ======== CRUD ========
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('createForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors('create');
        const fd = new FormData(this);
        try {
            const res = await fetch('/mesin', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                body: fd
            });
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

    document.getElementById('editForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors('edit');
        const id = document.getElementById('editId').value;
        const fd = new FormData(this);
        fd.append('_method', 'PUT');
        try {
            const res = await fetch(`/mesin/${id}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                body: fd
            });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500 });
                location.reload();
            } else {
                if (data.errors) showErrors(data.errors, 'edit');
                else Swal.fire('Error!', data.message, 'error');
            }
        } catch(e) { Swal.fire('Error!', 'Terjadi kesalahan.', 'error'); }
    });

    document.getElementById('importForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const btn = document.getElementById('importBtn');
        btn.disabled = true;
        btn.textContent = 'Mengimport...';
        try {
            const res = await fetch('/mesin/import', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                body: fd
            });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({ icon: 'success', title: 'Import Berhasil!', text: data.message, showConfirmButton: false, timer: 2000 });
                location.reload();
            } else {
                Swal.fire('Gagal!', data.message, 'error');
            }
        } catch(e) { Swal.fire('Error!', 'Terjadi kesalahan.', 'error'); }
        finally { btn.disabled = false; btn.textContent = 'Import'; }
    });
});

async function deleteMesin(id) {
    const result = await Swal.fire({
        title: 'Yakin hapus?', text: 'Data mesin akan dihapus permanen!',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#1f2937', cancelButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
    });
    if (result.isConfirmed) {
        try {
            const res = await fetch(`/mesin/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({ icon: 'success', title: 'Terhapus!', text: data.message, showConfirmButton: false, timer: 1500 });
                location.reload();
            } else { Swal.fire('Error!', data.message, 'error'); }
        } catch(e) { Swal.fire('Error!', 'Gagal menghapus!', 'error'); }
    }
}

function renderDetailModal(data) {
    document.getElementById('detailTitle').textContent = data.line_machine;
    document.getElementById('detailCount').textContent = data.data.length + ' mesin';
    const tbody = document.getElementById('detailTableBody');
    tbody.innerHTML = '';
    data.data.forEach((m, i) => {
        tbody.innerHTML += `
        <tr class="hover:bg-gray-50 transition">
            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">${i+1}</td>
            <td class="px-4 py-3 font-semibold text-sm text-gray-900 whitespace-nowrap">${m.machine_no}</td>
            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">${m.tonage || '-'}</td>
            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">${m.line || '-'}</td>
            <td class="px-4 py-3 text-sm text-gray-600 text-center whitespace-nowrap">${m.gsph_theory}</td>
            <td class="px-4 py-3 text-sm text-gray-600">${m.remarks || '-'}</td>
            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">${m.update_by}</td>
            <td class="px-4 py-3 whitespace-nowrap">
                <div class="flex space-x-1">
                    <button onclick="closeDetailModal(); openEditModal(${m.id})"
                        class="bg-orange-500 text-white px-2.5 py-1 rounded text-xs hover:bg-orange-600 transition">Edit</button>
                    <button onclick="deleteMesin(${m.id})"
                        class="bg-red-500 text-white px-2.5 py-1 rounded text-xs hover:bg-red-600 transition">Hapus</button>
                </div>
            </td>
        </tr>`;
    });
}

function csrf() {
    return document.querySelector('meta[name="csrf-token"]').content;
}
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
    if (e.key === 'Escape') {
        closeCreateModal(); closeEditModal(); closeDetailModal(); closeImportModal();
    }
});
</script>
@endpush