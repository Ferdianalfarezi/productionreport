@extends('layouts.app')

@section('title', 'Data Parts')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Data Parts</h1>
            <p class="text-gray-600 mt-1">Manajemen data part produksi</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="openImportCavityModal()"
                class="flex items-center space-x-2 px-4 py-2.5 border border-purple-300 bg-purple-50 rounded-lg text-sm font-semibold text-purple-700 hover:bg-purple-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Import Cavity</span>
            </button>
            <button onclick="openImportModal()"
                class="flex items-center space-x-2 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Import Parts</span>
            </button>
            <button onclick="openCreateModal()"
                class="flex items-center space-x-2 bg-gray-900 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-gray-700 transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Tambah Part</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('parts.index') }}" id="filterForm">
            <div class="flex flex-col md:flex-row md:items-center gap-3">

                <!-- Search -->
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" id="searchInput"
                        value="{{ request('search') }}"
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900 text-sm transition"
                        placeholder="Cari part no, line, atau category...">
                </div>

                <!-- Filter Line -->
                <select name="line" id="filterLine" onchange="submitFilter()"
                    class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition min-w-[150px]">
                    <option value="">Semua Line</option>
                    @foreach($lines as $line)
                        <option value="{{ $line }}" {{ request('line') == $line ? 'selected' : '' }}>{{ $line }}</option>
                    @endforeach
                </select>

                <!-- Filter Category -->
                <select name="category" id="filterCategory" onchange="submitFilter()"
                    class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition min-w-[150px]">
                    <option value="">Semua Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>

                <!-- Submit Search -->
                <button type="submit"
                    class="px-4 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-700 transition whitespace-nowrap">
                    Cari
                </button>

                <!-- Reset -->
                <a href="{{ route('parts.index') }}"
                    class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition whitespace-nowrap text-center">
                    Reset
                </a>

                <!-- Per Page -->
                <select name="per_page" id="perPageSelect" onchange="submitFilter()"
                    class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition">
                    <option value="20"  {{ $perPage == 20  ? 'selected' : '' }}>20 per halaman</option>
                    <option value="50"  {{ $perPage == 50  ? 'selected' : '' }}>50 per halaman</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 per halaman</option>
                </select>

            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Part No Child</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Line</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Qty KBN</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Qty Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Update By</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($parts as $part)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ ($parts->currentPage() - 1) * $parts->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900 text-sm">{{ $part->part_no_child ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                    {{ $part->line ?: '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">
                                    {{ $part->qty_kbn !== null ? number_format($part->qty_kbn, 0) : '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($part->category)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                        {{ $part->category === 'Cavity' ? 'bg-green-50 text-green-700' : 'bg-orange-50 text-orange-700' }}">
                                        {{ $part->category }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 bg-purple-50 text-purple-700 rounded-full text-xs font-semibold">
                                    {{ $part->qty_category ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $part->update_by ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center space-x-1">
                                    <button onclick="openEditModal({{ $part->id }})" title="Edit"
                                        class="w-8 h-8 flex items-center justify-center bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button onclick="deletePart({{ $part->id }})" title="Hapus"
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
                            <td colspan="8" class="px-6 py-16 text-center">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                                <p class="text-gray-600 font-semibold">Tidak ada data ditemukan</p>
                                <p class="text-gray-400 text-sm mt-1">Coba ubah filter atau kata kunci pencarian</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer + Pagination -->
        <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                <!-- Info -->
                <p class="text-sm text-gray-600">
                    Menampilkan
                    <span class="font-semibold text-gray-900">{{ $parts->firstItem() ?? 0 }}</span>–<span class="font-semibold text-gray-900">{{ $parts->lastItem() ?? 0 }}</span>
                    dari <span class="font-semibold text-gray-900">{{ $parts->total() }}</span> data
                </p>

                <!-- Laravel Pagination Links -->
                @if($parts->hasPages())
                    <div class="flex items-center space-x-1">

                        {{-- Prev --}}
                        @if($parts->onFirstPage())
                            <span class="w-8 h-8 flex items-center justify-center border border-gray-200 text-gray-300 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </span>
                        @else
                            <a href="{{ $parts->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach($parts->getUrlRange(max(1, $parts->currentPage() - 2), min($parts->lastPage(), $parts->currentPage() + 2)) as $page => $url)
                            @if($page == $parts->currentPage())
                                <span class="w-8 h-8 flex items-center justify-center bg-gray-900 text-white rounded-lg text-sm font-semibold">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 transition text-sm">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next --}}
                        @if($parts->hasMorePages())
                            <a href="{{ $parts->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @else
                            <span class="w-8 h-8 flex items-center justify-center border border-gray-200 text-gray-300 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        @endif

                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
@include('parts.create')
@include('parts.edit')
@include('parts.import')
@include('parts.import-cavity')

@endsection

@push('scripts')
<script>
// ── Submit filter form (dipanggil saat select berubah) ──
function submitFilter() {
    document.getElementById('filterForm').submit();
}

// ── Search: submit saat tekan Enter ──
document.getElementById('searchInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') submitFilter();
});

// ── MODALS ──
function openCreateModal()        { document.getElementById('createForm').reset(); clearErrors('create'); showModal('createModal'); }
function closeCreateModal()       { hideModal('createModal'); }
function openImportModal()        { showModal('importModal'); }
function closeImportModal()       { hideModal('importModal'); }
function openImportCavityModal()  { showModal('importCavityModal'); }
function closeImportCavityModal() { hideModal('importCavityModal'); }

async function openEditModal(id) {
    try {
        const res  = await fetch(`/parts/${id}/edit`);
        const data = await res.json();
        if (data.success) {
            const p = data.data;
            document.getElementById('editId').value          = p.id;
            document.getElementById('editPartNoChild').value = p.part_no_child || '';
            document.getElementById('editLine').value        = p.line || '';
            document.getElementById('editQtyKbn').value      = p.qty_kbn ?? '';
            document.getElementById('editCategory').value    = p.category || '';
            document.getElementById('editQtyCategory').value = p.qty_category ?? '';
            clearErrors('edit');
            showModal('editModal');
        }
    } catch(e) { Swal.fire('Error!', 'Gagal memuat data.', 'error'); }
}
function closeEditModal() { hideModal('editModal'); }

function showModal(id) {
    const m = document.getElementById(id);
    m.classList.remove('hidden');
    m.classList.add('flex');
    m.offsetHeight;
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

// ── CRUD ──
document.addEventListener('DOMContentLoaded', function () {

    // Create
    document.getElementById('createForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors('create');
        const fd = new FormData(this);
        try {
            const res  = await fetch('/parts', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }, body: fd });
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

    // Edit
    document.getElementById('editForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors('edit');
        const id = document.getElementById('editId').value;
        const fd = new FormData(this);
        fd.append('_method', 'PUT');
        try {
            const res  = await fetch(`/parts/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }, body: fd });
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

    // Import Parts
    document.getElementById('importForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const fd  = new FormData(this);
        const btn = document.getElementById('importBtn');
        btn.disabled = true; btn.textContent = 'Mengimport...';
        try {
            const res  = await fetch('/parts/import', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }, body: fd });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({ icon: 'success', title: 'Import Berhasil!', text: data.message, showConfirmButton: false, timer: 2000 });
                location.reload();
            } else { Swal.fire('Gagal!', data.message, 'error'); }
        } catch(e) { Swal.fire('Error!', 'Terjadi kesalahan.', 'error'); }
        finally { btn.disabled = false; btn.textContent = 'Import'; }
    });

    // Import Cavity
    document.getElementById('importCavityForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const fd  = new FormData(this);
        const btn = document.getElementById('importCavityBtn');
        btn.disabled = true; btn.textContent = 'Mengimport...';
        try {
            const res  = await fetch('/parts/import-cavity', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }, body: fd });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({ icon: 'success', title: 'Import Cavity Berhasil!', text: data.message, showConfirmButton: false, timer: 2000 });
                location.reload();
            } else { Swal.fire('Gagal!', data.message, 'error'); }
        } catch(e) { Swal.fire('Error!', 'Terjadi kesalahan.', 'error'); }
        finally { btn.disabled = false; btn.textContent = 'Import'; }
    });
});

// ── Delete ──
async function deletePart(id) {
    const result = await Swal.fire({
        title: 'Yakin hapus?',
        text: 'Data part akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1f2937',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
    });
    if (result.isConfirmed) {
        try {
            const res  = await fetch(`/parts/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({ icon: 'success', title: 'Terhapus!', text: data.message, showConfirmButton: false, timer: 1500 });
                location.reload();
            } else { Swal.fire('Error!', data.message, 'error'); }
        } catch(e) { Swal.fire('Error!', 'Gagal menghapus!', 'error'); }
    }
}

// ── Helpers ──
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
    if (e.key === 'Escape') {
        closeCreateModal(); closeEditModal(); closeImportModal(); closeImportCavityModal();
    }
});
</script>
@endpush