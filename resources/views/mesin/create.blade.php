<!-- CREATE Modal -->
<div id="createModal" class="modal-backdrop fixed inset-0 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Tambah Mesin</h2>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="createForm" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Line Machine <span class="text-red-500">*</span></label>
                    <input type="text" name="line_machine" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition"
                        placeholder="Contoh: LINE 10, PG 01">
                    <span class="text-red-500 text-xs mt-1" id="err-create-line_machine"></span>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Machine No <span class="text-red-500">*</span></label>
                    <input type="text" name="machine_no" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition"
                        placeholder="Contoh: PT 101, PG 01">
                    <span class="text-red-500 text-xs mt-1" id="err-create-machine_no"></span>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tonage</label>
                    <input type="text" name="tonage"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition"
                        placeholder="Contoh: 200t, 110t">
                    <span class="text-red-500 text-xs mt-1" id="err-create-tonage"></span>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Line</label>
                    <input type="text" name="line"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition"
                        placeholder="Contoh: PG, Robot, ASSY">
                    <span class="text-red-500 text-xs mt-1" id="err-create-line"></span>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">GSPH Theory</label>
                    <input type="number" name="gsph_theory" value="0" min="0"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition">
                    <span class="text-red-500 text-xs mt-1" id="err-create-gsph_theory"></span>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Remarks</label>
                    <input type="text" name="remarks"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition"
                        placeholder="Opsional">
                    <span class="text-red-500 text-xs mt-1" id="err-create-remarks"></span>
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