<?php

namespace App\Http\Controllers;

use App\Imports\LineConfigImport;
use App\Models\LineConfig;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LineConfigController extends Controller
{
    /** Index – group by line, mesin digabung */
    public function index()
    {
        // Ambil semua data, group by line di PHP agar fleksibel
        $all = LineConfig::orderBy('line')->orderBy('mesin')->get();

        // Grouped: ['SW LINE 1 A' => ['id' => [...], 'mesins' => [...], 'update_by' => '...']]
        $grouped = [];
        foreach ($all as $cfg) {
            $line = $cfg->line ?? '-';
            if (!isset($grouped[$line])) {
                $grouped[$line] = [
                    'ids'       => [],
                    'mesins'    => [],
                    'update_by' => $cfg->update_by,
                ];
            }
            $grouped[$line]['ids'][]    = $cfg->id;
            $grouped[$line]['mesins'][] = $cfg->mesin;
        }

        // Untuk filter dropdown line
        $lines = array_keys($grouped);
        sort($lines);

        return view('line-configs.index', compact('grouped', 'lines', 'all'));
    }

    /** Store */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'line'  => 'required|string|max:100',
            'mesin' => 'required|string|max:100',
        ]);

        $exists = LineConfig::where('line', $validated['line'])
                            ->where('mesin', $validated['mesin'])
                            ->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Kombinasi Line & Mesin sudah ada.'], 422);
        }

        $validated['update_by'] = auth()->user()->name ?? 'system';
        LineConfig::create($validated);

        return response()->json(['success' => true, 'message' => 'Line config berhasil ditambahkan.']);
    }

    /** Edit – return single record */
    public function edit(LineConfig $lineConfig)
    {
        return response()->json(['success' => true, 'data' => $lineConfig]);
    }

    /** Update */
    public function update(Request $request, LineConfig $lineConfig)
    {
        $validated = $request->validate([
            'line'  => 'required|string|max:100',
            'mesin' => 'required|string|max:100',
        ]);

        $exists = LineConfig::where('line', $validated['line'])
                            ->where('mesin', $validated['mesin'])
                            ->where('id', '!=', $lineConfig->id)
                            ->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Kombinasi Line & Mesin sudah ada.'], 422);
        }

        $validated['update_by'] = auth()->user()->name ?? 'system';
        $lineConfig->update($validated);

        return response()->json(['success' => true, 'message' => 'Line config berhasil diperbarui.']);
    }

    /** Destroy single record */
    public function destroy(LineConfig $lineConfig)
    {
        $lineConfig->delete();
        return response()->json(['success' => true, 'message' => 'Mesin berhasil dihapus.']);
    }

    /** Destroy all records for a line */
    public function destroyLine(Request $request)
    {
        $request->validate(['line' => 'required|string']);
        $deleted = LineConfig::where('line', $request->line)->delete();
        return response()->json(['success' => true, 'message' => "{$deleted} mesin untuk line ini berhasil dihapus."]);
    }

    /** Import dari Excel (kolom A = line, kolom B = mesin) */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);

        try {
            $import = new LineConfigImport();
            Excel::import($import, $request->file('file'));

            $count   = $import->getImportedCount();
            $skipped = $import->getSkippedCount();

            $message = "Import berhasil! {$count} data diimpor.";
            if ($skipped > 0) {
                $message .= " {$skipped} data dilewati (duplikat/kosong).";
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
}