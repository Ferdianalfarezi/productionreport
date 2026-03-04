<?php

namespace App\Http\Controllers;

use App\Models\BreakTime;
use Illuminate\Http\Request;

class BreakTimeController extends Controller
{
    public function index()
    {
        $breakTimes = BreakTime::orderBy('shift')->orderBy('break_start')->get();
        return view('break-times.index', compact('breakTimes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'break_name'  => 'required|string|max:100',
            'shift'       => 'nullable|in:1,2',
            'break_start' => 'required|date_format:H:i',
            'duration'    => 'required|integer|min:1|max:480',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        BreakTime::create($data);

        return back()->with('success', 'Break time berhasil ditambahkan.');
    }

    public function update(Request $request, BreakTime $breakTime)
    {
        $data = $request->validate([
            'break_name'  => 'required|string|max:100',
            'shift'       => 'nullable|in:1,2',
            'break_start' => 'required|date_format:H:i',
            'duration'    => 'required|integer|min:1|max:480',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $breakTime->update($data);

        return back()->with('success', 'Break time berhasil diperbarui.');
    }

    public function destroy(BreakTime $breakTime)
    {
        $breakTime->delete();
        return back()->with('success', 'Break time berhasil dihapus.');
    }
}