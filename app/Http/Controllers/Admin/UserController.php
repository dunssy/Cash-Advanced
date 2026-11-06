<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureSuperAdmin();

        $query = User::with('manager')->orderBy('name');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $this->ensureSuperAdmin();

        $managers = User::whereIn('role', ['atasan', 'direktur'])->get();

        return view('admin.users.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:karyawan,atasan,finance,direktur,super_admin'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'department' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'manager_id' => $validated['manager_id'] ?: null,
            'department' => $validated['department'],
            'phone' => $validated['phone'],
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_holder' => $validated['bank_account_holder'],
            'is_active' => true,
        ]);

        AuditLog::record('USER_CREATED', $user, "User baru {$user->name} ({$user->email}, role: {$user->role}) dibuat oleh Super Admin.");

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} berhasil dibuat.");
    }

    public function edit(User $user)
    {
        $this->ensureSuperAdmin();

        $managers = User::whereIn('role', ['atasan', 'direktur'])
            ->where('id', '!=', $user->id)
            ->get();

        return view('admin.users.edit', compact('user', 'managers'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:karyawan,atasan,finance,direktur,super_admin'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'department' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldValues = $user->toArray();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'manager_id' => $validated['manager_id'] ?: null,
            'department' => $validated['department'],
            'phone' => $validated['phone'],
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_holder' => $validated['bank_account_holder'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        AuditLog::record('USER_UPDATED', $user, "Data user {$user->name} diperbarui oleh Super Admin.", $oldValues, $user->toArray());

        return redirect()->route('admin.users.index')->with('success', "Data user {$user->name} berhasil diperbarui.");
    }

    private function ensureSuperAdmin(): void
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Halaman ini hanya dapat diakses oleh Super Admin.');
        }
    }
}
