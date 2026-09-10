<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.auth', ['isRegister' => true]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:50', 'unique:users,employee_id'],
            'address'     => ['nullable', 'string', 'max:500'],
            'email'       => ['required', 'email', 'max:255', 'unique:users'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'employee_id.unique' => 'This employee ID is already registered to another account.',
            'email.unique'       => 'This email address is already registered.',
        ]);

        // The very first account created becomes the administrator.
        $role = User::count() === 0 ? User::ROLE_ADMIN : User::ROLE_STAFF;

        $user = User::create([
            'name'        => $data['name'],
            'employee_id' => $data['employee_id'] ?? null,
            'address'     => $data['address'] ?? null,
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'role'        => $role,
            'is_active'   => true,
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
