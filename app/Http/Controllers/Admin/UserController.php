<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('archivedCharts')->orderBy('name')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:admin,staff',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        AuditLog::record('create_user', 'users', $user->id, null, ['name' => $user->name, 'email' => $user->email, 'role' => $user->role]);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'role'      => 'required|in:admin,staff',
            'is_active' => 'boolean',
            'password'  => 'nullable|string|min:8|confirmed',
        ]);

        $old = $user->only(['name', 'email', 'role', 'is_active']);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $user->update($data);
        AuditLog::record('update_user', 'users', $user->id, $old, $user->only(['name', 'email', 'role', 'is_active']));

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function loginHistory(User $user)
    {
        $logs = \App\Models\AuditLog::where('user_id', $user->id)
            ->whereIn('action', ['login', 'logout'])
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.users.login_history', compact('user', 'logs'));
    }
}
