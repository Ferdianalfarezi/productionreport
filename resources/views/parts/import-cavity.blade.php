<!-- IMPORT Cavity Modal -->
<div id="importCavityModal" class="modal-backdrop fixed inset-0 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Import Cavity</h2>
            </div>
            <button onclick="closeImportCavityModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="importCavityForm" class="p-6 space-y-4">
            @csrf

            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-sm text-purple-800 space-y-1">
                <p class="font-semibold">File: <span class="font-normal">data_cavity.xlsx</span></p>
                <p class="font-semibold mt-2">Cara kerja import ini:</p>
                <ul class="list-disc list-inside space-y-0.5 text-purple-700">
                    <li><span class="font-medium">Kolom E</span> (PART_NO) dipakai sebagai key lookup</li>
                    <li><span class="font-medium">Kolom I</span> (CATEGORY) → update kolom <code class="bg-purple-100 px-1 rounded">category</code></li>
                    <li><span class="font-medium">Kolom J</span> (QTY_CATEGORY) → update kolom <code class="bg-purple-100 px-1 rounded">qty_category</code></li>
                </ul>
                <p class="text-purple-600 text-xs mt-2">Data di-update berdasarkan kecocokan PART_NO_CHILD. Baris yang tidak cocok akan dilewati.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pilih File Excel <span class="text-red-500">*</span></label>
                <input type="file" name="file" accept=".xlsx,.xls" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-600 file:text-white hover:file:bg-purple-700 cursor-pointer">
            </div>

            <div class="flex justify-end space-x-3 pt-2 border-t border-gray-200">
                <button type="button" onclick="closeImportCavityModal()"
                    class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" id="importCavityBtn"
                    class="px-5 py-2.5 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700 transition disabled:opacity-50">
                    Import
                </button>
            </div>
        </form>
    </div>
</div>
