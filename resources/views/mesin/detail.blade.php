<!-- DETAIL Modal -->
<div id="detailModal" class="modal-backdrop fixed inset-0 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl flex flex-col" style="max-height:90vh">
        <div class="flex-shrink-0 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900" id="detailTitle">Detail Mesin</h2>
                <p class="text-sm text-gray-500 mt-0.5" id="detailCount"></p>
            </div>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Machine No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Tonage</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Line</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">GSPH Theory</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Remarks</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Update By</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="detailTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>