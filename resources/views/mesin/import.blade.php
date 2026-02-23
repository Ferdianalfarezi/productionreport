<!-- IMPORT Modal -->
<div id="importModal" class="fixed inset-0 hidden items-center justify-center z-50 p-4 bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="border-b border-gray-200 px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Import Data Mesin</h2>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="importForm" class="p-6 space-y-5">
            @csrf
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-700">
                <p class="font-semibold mb-1">Format Excel yang diperlukan:</p>
                <ul class="space-y-0.5 text-xs list-disc list-inside">
                    <li>Row 2: Header (LINE Machine, MACHINE_NO, TONAGE, LINE, GSPH_THEORY, REMARKS, UPDATE_BY, UPDATE_TIME)</li>
                    <li>Row 3+: Data mesin</li>
                    <li>Format file: .xlsx atau .xls</li>
                </ul>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih File Excel</label>
                <input type="file" name="file" accept=".xlsx,.xls" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition file:mr-3 file:py-1 file:px-3 file:border-0 file:bg-gray-100 file:text-sm file:rounded-md file:font-medium">
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-xs text-yellow-700">
                <strong>Catatan:</strong> Data dengan machine_no yang sudah ada akan dilewati (skip). Data baru akan langsung ditambahkan.
            </div>

            <div class="flex justify-end space-x-3 pt-2 border-t border-gray-200">
                <button type="button" onclick="closeImportModal()"
                    class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" id="importBtn"
                    class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition">
                    Import
                </button>
            </div>
        </form>
    </div>
</div>
