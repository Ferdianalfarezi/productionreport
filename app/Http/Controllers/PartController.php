<?php

namespace App\Http\Controllers;

use App\Imports\CavityImport;
use App\Imports\PartsImport;
use App\Models\Part;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PartController extends Controller
{
    /** Index */
    public function index()
    {
        $parts = Part::orderBy('part_no_child')->get();
        return view('parts.index', compact('parts'));
    }

    /** Store */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_no_child' => 'required|string|max:100',
            'line'          => 'nullable|string|max:100',
            'qty_kbn'       => 'nullable|numeric|min:0',
            'category'      => 'nullable|string|max:100',
            'qty_category'  => 'nullable|integer|min:0',
        ]);

        $validated['update_by'] = auth()->user()->name ?? 'system';
        Part::create($validated);

        return response()->json(['success' => true, 'message' => 'Part berhasil ditambahkan.']);
    }

    /** Edit – ambil data untuk modal */
    public function edit(Part $part)
    {
        return response()->json(['success' => true, 'data' => $part]);
    }

    /** Update */
    public function update(Request $request, Part $part)
    {
        $validated = $request->validate([
            'part_no_child' => 'required|string|max:100',
            'line'          => 'nullable|string|max:100',
            'qty_kbn'       => 'nullable|numeric|min:0',
            'category'      => 'nullable|string|max:100',
            'qty_category'  => 'nullable|integer|min:0',
        ]);

        $validated['update_by'] = auth()->user()->name ?? 'system';
        $part->update($validated);

        return response()->json(['success' => true, 'message' => 'Part berhasil diperbarui.']);
    }

    /** Destroy */
    public function destroy(Part $part)
    {
        $part->delete();
        return response()->json(['success' => true, 'message' => 'Part berhasil dihapus.']);
    }

    /**
     * Import data_part_prodreport.xlsx
     * Kolom yang diambil: F (PART_NO_CHILD), I (LINE), L (QTY_KBN)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $import = new PartsImport();
            Excel::import($import, $request->file('file'));

            $count = $import->getImportedCount();
            return response()->json([
                'success' => true,
                'message' => "Import berhasil! {$count} data parts berhasil diimpor.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Import gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Import data_cavity.xlsx
     * Lookup berdasarkan PART_NO (col E) → update CATEGORY & QTY_CATEGORY di tabel parts
     */
    public function importCavity(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $import = new CavityImport();
            Excel::import($import, $request->file('file'));

            $updated = $import->getUpdatedCount();
            $skipped = $import->getSkippedCount();

            return response()->json([
                'success' => true,
                'message' => "Import cavity berhasil! {$updated} baris diperbarui, {$skipped} baris tidak cocok.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Import gagal: ' . $e->getMessage()], 500);
        }
    }
}
