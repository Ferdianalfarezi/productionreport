<?php

namespace App\Http\Controllers;

use App\Models\Mesin;
use App\Imports\MesinImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class MesinController extends Controller
{
    public function index()
    {
        $groups = Mesin::selectRaw('line_machine, COUNT(*) as total_mesin, GROUP_CONCAT(DISTINCT line) as line_list, AVG(gsph_theory) as avg_gsph, MAX(update_time) as last_update')
            ->groupBy('line_machine')
            ->orderBy('line_machine')
            ->get();

        return view('mesin.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'line_machine' => 'required|string|max:100',
            'machine_no'   => 'required|string|max:100|unique:mesins,machine_no',
            'tonage'       => 'nullable|string|max:50',
            'line'         => 'nullable|string|max:100',
            'gsph_theory'  => 'nullable|integer|min:0',
            'remarks'      => 'nullable|string|max:255'
        ]);

        $validated['update_by']   = Auth::user()->username;
        $validated['update_time'] = now();
        $validated['gsph_theory'] = $validated['gsph_theory'] ?? 0;

        Mesin::create($validated);

        return response()->json(['success' => true, 'message' => 'Mesin berhasil ditambahkan.']);
    }

    public function showGroup($lineMachine)
    {
        $group = Mesin::where('line_machine', $lineMachine)
            ->orderBy('machine_no')
            ->get();

        if ($group->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        return response()->json([
            'success'      => true,
            'line_machine' => $lineMachine,
            'data'         => $group,
        ]);
    }

    public function edit($id)
    {
        $mesin = Mesin::findOrFail($id);
        return response()->json(['success' => true, 'data' => $mesin]);
    }

    public function update(Request $request, $id)
    {
        $mesin = Mesin::findOrFail($id);

        $validated = $request->validate([
            'line_machine' => 'required|string|max:100',
            'machine_no'   => 'required|string|max:100|unique:mesins,machine_no,' . $id,
            'tonage'       => 'nullable|string|max:50',
            'line'         => 'nullable|string|max:100',
            'gsph_theory'  => 'nullable|integer|min:0',
            'remarks'      => 'nullable|string|max:255',
            
        ]);

        $validated['update_by']   = Auth::user()->username;
        $validated['update_time'] = now();
        $validated['gsph_theory'] = $validated['gsph_theory'] ?? 0;

        $mesin->update($validated);

        return response()->json(['success' => true, 'message' => 'Mesin berhasil diupdate.']);
    }

    public function destroy($id)
    {
        $mesin = Mesin::findOrFail($id);
        $mesin->delete();

        return response()->json(['success' => true, 'message' => 'Mesin berhasil dihapus.']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new MesinImport, $request->file('file'));
            return response()->json(['success' => true, 'message' => 'Import berhasil.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Import gagal: ' . $e->getMessage()], 500);
        }
    }
}