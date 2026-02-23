<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->orderBy('created_at', 'desc')->get();
        $roles = Role::all();
        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'role_id'  => 'required|exists:roles,id',
            'status'   => 'required|in:aktif,nonaktif',
            'avatar'   => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('users', 'public');
            $validated['avatar'] = basename($validated['avatar']);
        }

        User::create($validated);

        return response()->json(['success' => true, 'message' => 'User berhasil ditambahkan.']);
    }

    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $user]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'role_id'  => 'required|exists:roles,id',
            'status'   => 'required|in:aktif,nonaktif',
            'avatar'   => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists('users/' . $user->avatar)) {
                Storage::disk('public')->delete('users/' . $user->avatar);
            }
            $validated['avatar'] = basename($request->file('avatar')->store('users', 'public'));
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json(['success' => true, 'message' => 'User berhasil diupdate.']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Superadmin tidak bisa dihapus.'], 403);
        }

        if ($user->avatar && Storage::disk('public')->exists('users/' . $user->avatar)) {
            Storage::disk('public')->delete('users/' . $user->avatar);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User berhasil dihapus.']);
    }
}
