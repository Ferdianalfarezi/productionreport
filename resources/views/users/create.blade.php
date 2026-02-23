<!-- CREATE USER Modal -->
<div id="createModal" class="modal-backdrop fixed inset-0 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Add New User</h2>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="createForm" class="p-6 space-y-4">
            @csrf

            <!-- Avatar -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Avatar (Opsional)</label>
                <input type="file" name="avatar" accept="image/*" onchange="previewImage(event, 'createPreview')"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm transition file:mr-3 file:py-1 file:px-3 file:border-0 file:bg-gray-100 file:text-sm file:rounded-md file:font-medium">
                <span class="text-red-500 text-xs" id="error-create-avatar"></span>
                <div id="createPreviewContainer" class="mt-2 hidden">
                    <img id="createPreview" class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                </div>
            </div>

            <!-- Username -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition"
                    placeholder="Masukkan username">
                <span class="text-red-500 text-xs" id="error-create-username"></span>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="password" id="createPassword" name="password" required
                        class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition"
                        placeholder="Min 6 karakter">
                    <button type="button" onclick="togglePassword('createPassword')"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <span class="text-red-500 text-xs" id="error-create-password"></span>
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="password" id="createPasswordConfirm" name="password_confirmation" required
                        class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition"
                        placeholder="Ulangi password">
                    <button type="button" onclick="togglePassword('createPasswordConfirm')"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <span class="text-red-500 text-xs" id="error-create-password_confirmation"></span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Role -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select name="role_id" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition">
                        <option value="">Pilih Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ ucfirst($role->nama) }}</option>
                        @endforeach
                    </select>
                    <span class="text-red-500 text-xs" id="error-create-role_id"></span>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Non-Aktif</option>
                    </select>
                    <span class="text-red-500 text-xs" id="error-create-status"></span>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-2 border-t border-gray-200">
                <button type="button" onclick="closeCreateModal()"
                    class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>