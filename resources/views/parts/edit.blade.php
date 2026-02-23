<!-- EDIT Modal -->
<div id="editModal" class="modal-backdrop fixed inset-0 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Edit Part</h2>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="editForm" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="editId" name="id">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Part No Child <span class="text-red-500">*</span></label>
                <input type="text" id="editPartNoChild" name="part_no_child" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition">
                <span class="text-red-500 text-xs mt-1" id="err-edit-part_no_child"></span>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Line</label>
                <input type="text" id="editLine" name="line"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition">
                <span class="text-red-500 text-xs mt-1" id="err-edit-line"></span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Qty KBN</label>
                    <input type="number" id="editQtyKbn" name="qty_kbn" min="0" step="0.01"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition">
                    <span class="text-red-500 text-xs mt-1" id="err-edit-qty_kbn"></span>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Qty Category</label>
                    <input type="number" id="editQtyCategory" name="qty_category" min="0"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition">
                    <span class="text-red-500 text-xs mt-1" id="err-edit-qty_category"></span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Category</label>
                <select id="editCategory" name="category"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition">
                    <option value="">-- Pilih Category --</option>
                    <option value="Cavity">Cavity</option>
                    <option value="Shoot">Shoot</option>
                </select>
                <span class="text-red-500 text-xs mt-1" id="err-edit-category"></span>
            </div>

            <div class="flex justify-end space-x-3 pt-2 border-t border-gray-200">
                <button type="button" onclick="closeEditModal()"
                    class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
