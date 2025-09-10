<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $users = User::orderBy('id')
            ->when($search, function ($q, $search) {
                return $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })
            ->paginate();

        if ($search) {
            $users->appends(['search' => $search]);
        }

        return view('user.index', [
            'users' => $users
        ]);
    }

    public function create()
    {
        return view('user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'max:100'],
            'username' => ['required', 'max:100', 'unique:users'],
            'role' => ['required', 'in:admin,petugas'],
            'password' => ['required', 'max:100', 'confirmed'],
        ]);

        $request->merge([
            'password' => bcrypt($request->password),
        ]);

        User::create($request->all());

        return redirect()->route('user.index')->with('store', 'success');
    }

    public function show(User $user)
    {
        abort(404);
    }

    public function edit(User $user)
    {
        return view('user.edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama' => ['required', 'max:100'],
            'username' => ['required', 'max:100', 'unique:users,username,' . $user->id],
            'role' => ['required', 'in:admin,petugas'],
            'password_baru' => ['nullable', 'max:100', 'confirmed'],
        ]);

        if ($request->password_baru) {
            $request->merge([
                'password' => bcrypt($request->password_baru),
            ]);
            $user->update($request->all());
        } else {
            $user->update($request->only('nama', 'username', 'role'));
        }

        return redirect()->route('user.index')->with('update', 'success');
    }

    public function destroy(User $user)
    {
        if (! $user->canBeDeletedByCurrentUser()) {
            return redirect()->route('user.index')->with([
                'destroy' => 'error',
                'destroy_message' => 'User ini tidak dapat dihapus!'
            ]);
        }

        try {
            $user->delete();
            return redirect()->route('user.index')->with('destroy', 'success');
        } catch (\Exception $e) {
            return redirect()->route('user.index')->with([
                'destroy' => 'error',
                'destroy_message' => 'Terjadi kesalahan saat menghapus user!'
            ]);
        }
    }
}
