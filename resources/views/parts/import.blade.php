<!-- IMPORT Parts Modal -->
<div id="importModal" class="modal-backdrop fixed inset-0 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Import Parts</h2>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="importForm" class="p-6 space-y-4">
            @csrf
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800 space-y-1">
                <p class="font-semibold">File: <span class="font-normal">data_part_prodreport.xlsx</span></p>
                <p class="font-semibold mt-2">Kolom yang diimport:</p>
                <ul class="list-disc list-inside space-y-0.5 text-blue-700">
                    <li><span class="font-medium">Kolom F</span> → PART_NO_CHILD</li>
                    <li><span class="font-medium">Kolom I</span> → LINE</li>
                    <li><span class="font-medium">Kolom L</span> → QTY_KBN</li>
                </ul>
                <p class="text-blue-600 text-xs mt-2">CATEGORY & QTY_CATEGORY diisi lewat "Import Cavity" terpisah.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pilih File Excel <span class="text-red-500">*</span></label>
                <input type="file" name="file" accept=".xlsx,.xls" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-900 file:text-white hover:file:bg-gray-700 cursor-pointer">
            </div>

            <div class="flex justify-end space-x-3 pt-2 border-t border-gray-200">
                <button type="button" onclick="closeImportModal()"
                    class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" id="importBtn"
                    class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition disabled:opacity-50">
                    Import
                </button>
            </div>
        </form>
    </div>
</div>
