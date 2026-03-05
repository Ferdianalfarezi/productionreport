<?php

namespace App\Http\Controllers;

use App\Imports\CavityImport;
use App\Imports\PartsImport;
use App\Models\Part;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PartController extends Controller
{
    /** Index — server-side pagination + filter */
    public function index(Request $request)
    {
        $query = Part::query();

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no_child', 'like', "%{$search}%")
                  ->orWhere('line', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter line
        if ($line = $request->get('line')) {
            $query->where('line', $line);
        }

        // Filter category
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $perPage = in_array($request->get('per_page'), [20, 50, 100]) ? $request->get('per_page') : 20;

        $parts      = $query->orderBy('part_no_child')->paginate($perPage)->withQueryString();
        $lines      = Part::distinct()->whereNotNull('line')->orderBy('line')->pluck('line');
        $categories = Part::distinct()->whereNotNull('category')->orderBy('category')->pluck('category');

        return view('parts.index', compact('parts', 'lines', 'categories', 'perPage'));
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

    /** Import data_part_prodreport.xlsx */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $import = new PartsImport();
            Excel::import($import, $request->file('file'));

            $count   = $import->getImportedCount();
            $skipped = $import->getSkippedCount();

            $message = "Import berhasil! {$count} data diimpor.";
            if ($skipped > 0) {
                $message .= " {$skipped} data dilewati karena duplikat.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => ['imported' => $count, 'skipped' => $skipped],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Import gagal: ' . $e->getMessage()], 500);
        }
    }

    /** Import data_cavity.xlsx */
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