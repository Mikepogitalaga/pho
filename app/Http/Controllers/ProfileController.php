<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the signed-in account's own profile.
     */
    public function edit()
    {
        $user = Auth::user();

        $userPrograms = $user->programs()->orderBy('name')->get();

        // Keep the primary program visible even when it was only set on users.program_id.
        if ($user->program_id && ! $userPrograms->contains('id', $user->program_id)) {
            $primaryProgram = $user->program()->first();

            if ($primaryProgram) {
                $userPrograms->push($primaryProgram);
            }
        }

        return view('profile.edit', compact('user', 'userPrograms'));
    }

    /**
     * Update the signed-in account's own details (and optionally its password).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_id')->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:500'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'employee_id.unique' => 'This employee ID is already registered to another account.',
            'email.unique' => 'This email address is already registered.',
            'current_password.required_with' => 'Enter your current password to set a new one.',
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $changes = [];
        foreach (['name', 'employee_id', 'address', 'email'] as $field) {
            $oldValue = $user->{$field};
            $newValue = $data[$field] ?? null;

            if ((string) $oldValue !== (string) $newValue) {
                $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
            }
        }

        $attributes = [
            'name' => $data['name'],
            'employee_id' => $data['employee_id'] ?? null,
            'address' => $data['address'] ?? null,
            'email' => $data['email'],
        ];

        $passwordChanged = ! empty($data['password']);

        if ($passwordChanged) {
            $attributes['password'] = Hash::make($data['password']);
            $changes['password'] = ['old' => null, 'new' => 'changed'];
        }

        $user->update($attributes);

        $this->writeAudit($user, $changes);

        if (empty($changes)) {
            return redirect()->route('profile.edit')->with('success', 'No changes were made to your profile.');
        }

        return redirect()->route('profile.edit')->with(
            'success',
            $passwordChanged ? 'Profile and password updated successfully.' : 'Profile updated successfully.'
        );
    }

    private function writeAudit(User $user, array $changes): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'System',
                'action' => 'updated',
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
