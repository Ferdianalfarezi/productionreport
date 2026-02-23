@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Users</h1>
            <p class="text-gray-600 mt-1">Manage system users and access control</p>
        </div>
        <button onclick="openCreateModal()"
            class="flex items-center space-x-2 bg-gray-900 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-gray-700 transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Add User</span>
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" id="searchInput" onkeyup="searchTable()"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition"
                    placeholder="Cari username, role...">
            </div>
            <select id="perPageSelect" onchange="changePerPage()"
                class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 transition">
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">All</option>
            </select>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Avatar</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Login</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="usersTableBody">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition user-row"
                            data-search="{{ strtolower($user->username . ' ' . $user->role->nama) }}">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/users/'.$user->avatar) }}"
                                        class="w-10 h-10 rounded-full object-cover border-2 border-gray-200">
                                @else
                                    <div class="w-10 h-10 bg-gray-900 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $user->username }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $user->role->nama === 'superadmin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($user->role->nama) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $user->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $user->last_login ? $user->last_login->format('d M Y H:i') : 'Never' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <button onclick="openEditModal({{ $user->id }})"
                                        class="bg-orange-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-orange-600 transition">
                                        Edit
                                    </button>
                                    @if(!$user->isSuperAdmin())
                                    <button onclick="deleteUser({{ $user->id }})"
                                        class="bg-red-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-red-600 transition">
                                        Delete
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                                <p class="font-semibold">No users found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-gray-50 border-t border-gray-200 px-6 py-3">
            <p class="text-sm text-gray-600">
                Showing <span id="showCount" class="font-medium">{{ $users->count() }}</span> of
                <span id="totalCount" class="font-medium">{{ $users->count() }}</span> entries
            </p>
        </div>
    </div>
</div>

@include('users.create')
@include('users.edit')
@endsection

@push('scripts')
<script>
let allRows = [];

document.addEventListener('DOMContentLoaded', function() {
    allRows = Array.from(document.querySelectorAll('.user-row'));
    initForms();
});

function searchTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    allRows.forEach(row => row.classList.toggle('hidden', !row.dataset.search.includes(q)));
    updateFooter();
}

function changePerPage() {
    const val = document.getElementById('perPageSelect').value;
    const limit = val === 'all' ? Infinity : parseInt(val);
    const visible = allRows.filter(r => !r.classList.contains('hidden'));
    visible.forEach((r, i) => r.style.display = i < limit ? '' : 'none');
    updateFooter();
}

function updateFooter() {
    const showing = allRows.filter(r => !r.classList.contains('hidden') && r.style.display !== 'none');
    document.getElementById('showCount').textContent = showing.length;
    document.getElementById('totalCount').textContent = allRows.length;
}

function openCreateModal() {
    document.getElementById('createForm').reset();
    document.getElementById('createPreviewContainer').classList.add('hidden');
    clearErrors('create');
    showModal('createModal');
}
function closeCreateModal() { hideModal('createModal'); }

async function openEditModal(id) {
    try {
        const res = await fetch(`/users/${id}`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success) {
            const u = data.data;
            document.getElementById('editUserId').value = u.id;
            document.getElementById('editUsername').value = u.username;
            document.getElementById('editRoleId').value = u.role_id;
            document.getElementById('editStatus').value = u.status;
            const prev = document.getElementById('editPreview');
            const noImg = document.getElementById('noImageText');
            if (u.avatar) { prev.src = `/storage/users/${u.avatar}`; prev.style.display='block'; noImg.style.display='none'; }
            else { prev.style.display='none'; noImg.style.display='block'; }
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

function initForms() {
    document.getElementById('createForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors('create');
        try {
            const res = await fetch('/users', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                body: new FormData(this)
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
        const id = document.getElementById('editUserId').value;
        const fd = new FormData(this);
        fd.append('_method', 'PUT');
        try {
            const res = await fetch(`/users/${id}`, {
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
            }
        } catch(e) { Swal.fire('Error!', 'Terjadi kesalahan.', 'error'); }
    });
}

async function deleteUser(id) {
    const result = await Swal.fire({
        title: 'Yakin hapus?', text: 'User akan dihapus permanen!',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#1f2937', cancelButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
    });
    if (result.isConfirmed) {
        try {
            const res = await fetch(`/users/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf() } });
            const data = await res.json();
            if (data.success) {
                await Swal.fire({ icon: 'success', title: 'Terhapus!', text: data.message, showConfirmButton: false, timer: 1500 });
                location.reload();
            } else { Swal.fire('Error!', data.message, 'error'); }
        } catch(e) { Swal.fire('Error!', 'Gagal menghapus!', 'error'); }
    }
}

function previewImage(e, id) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = ev => {
            document.getElementById(id).src = ev.target.result;
            document.getElementById(id+'Container').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}
function previewEditImage(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = ev => {
            document.getElementById('editPreview').src = ev.target.result;
            document.getElementById('editPreview').style.display = 'block';
            document.getElementById('noImageText').style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
}
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
function showErrors(errors, prefix) {
    Object.keys(errors).forEach(k => {
        const el = document.getElementById(`error-${prefix}-${k}`);
        if (el) el.textContent = errors[k][0];
    });
}
function clearErrors(prefix) {
    document.querySelectorAll(`[id^="error-${prefix}-"]`).forEach(el => el.textContent = '');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeCreateModal(); closeEditModal(); }
});
</script>
@endpush