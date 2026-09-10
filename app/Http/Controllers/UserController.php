<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('per_page', 15);

        $users = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateUser($request);

        $user = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
        ]);

        $this->writeAudit('created', $user, []);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validateUser($request, $user);

        // An admin cannot deactivate their own account.
        if (! ($data['is_active'] ?? true) && Auth::id() === $user->id) {
            return back()->withInput()->withErrors(['is_active' => 'You cannot deactivate your own account.']);
        }

        $changes = [];
        foreach ($data as $field => $newValue) {
            $oldValue = $user->{$field};
            if ((string) $oldValue !== (string) $newValue) {
                $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
            }
        }

        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        $this->writeAudit('updated', $user, array_diff_key($changes, ['password' => 0]));

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isAdmin() && User::where('role', User::ROLE_ADMIN)->where('id', '!=', $user->id)->count() === 0) {
            return back()->with('error', 'Cannot delete the only administrator account.');
        }

        $label = $this->labelFor($user);
        $userId = $user->id;
        $user->delete();

        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'System',
                'action' => 'deleted',
                'module' => 'User',
                'record_id' => $userId,
                'record_label' => $label,
                'changes' => null,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable) {
            // Never break the main transaction due to audit failure
        }

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Clear a locked account's failed attempt counter.
     */
    public function unlock(User $user)
    {
        $wasLocked = $user->failed_login_attempts > 0 || $user->locked_until !== null;

        $user->resetLoginAttempts();

        if ($wasLocked) {
            $this->writeAudit('unlocked', $user, []);
        }

        return redirect()->route('users.index')->with('success', "Login attempts cleared for {$user->name}.");
    }

    /**
     * Toggle the active state of an account.
     */
    public function toggleStatus(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot change the status of your own account.');
        }

        $wasActive = $user->is_active;

        $user->update(['is_active' => ! $wasActive]);

        // Deactivating also clears any lock and pending failure counter.
        if (! $user->is_active) {
            $user->resetLoginAttempts();
        }

        $this->writeAudit($user->is_active ? 'activated' : 'deactivated', $user, [
            'is_active' => ['old' => $wasActive, 'new' => $user->is_active],
        ]);

        return redirect()->route('users.index')
            ->with('success', $user->is_active ? "{$user->name} has been activated." : "{$user->name} has been deactivated.");
    }

    /**
     * Shared validation rules for store/update.
     */
    private function validateUser(Request $request, ?User $user = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_id')->ignore($user?->id)],
            'address' => ['nullable', 'string', 'max:500'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF])],
            'password' => $user
                ? ['nullable', 'string', 'min:8', 'confirmed']
                : ['required', 'string', 'min:8', 'confirmed'],
        ];

        $messages = [
            'employee_id.unique' => 'This employee ID is already registered to another account.',
            'email.unique' => 'This email address is already registered.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($request->filled('password')) {
            $validated['password'] = $request->input('password');
        } else {
            unset($validated['password']);
        }

        $validated['role'] = $validated['role'] ?? User::ROLE_STAFF;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function writeAudit(string $action, User $user, array $changes): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'System',
                'action' => $action,
                'module' => 'User',
                'record_id' => $user->id,
                'record_label' => $this->labelFor($user),
                'changes' => empty($changes) ? null : $changes,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable) {
            // Never break the main transaction due to audit failure
        }
    }

    private function labelFor(User $user): string
    {
        return trim(($user->employee_id ? $user->employee_id.' · ' : '').$user->name);
    }
}

