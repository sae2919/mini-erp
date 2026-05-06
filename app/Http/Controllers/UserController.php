<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // INDEX  ← added withQueryString() + search + role filter
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->search, fn($q) =>                      // ← NEW
                $q->where(fn($q2) =>
                    $q2->where('name',  'like', '%' . $request->search . '%')
                       ->orWhere('email', 'like', '%' . $request->search . '%')
                )
            )
            ->when($request->role, fn($q) =>                        // ← NEW
                $q->whereHas('roles', fn($r) => $r->where('name', $request->role))
            )
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();                                      // ← THIS was missing

        $roles = Role::orderBy('name')->get();                       // ← NEW (needed for filter dropdown)

        return view('users.index', compact('users', 'roles'));
    }

    // ─────────────────────────────────────────────────────────────
    // Everything below is UNCHANGED from your version
    // ─────────────────────────────────────────────────────────────

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')
            ->with('success', "User \"{$user->name}\" created with role: {$validated['role']}.");
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'exists:roles,name'],
        ]);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')
            ->with('success', "User \"{$user->name}\" updated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User deleted.");
    }
}
