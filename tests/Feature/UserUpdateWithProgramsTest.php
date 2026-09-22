<?php

use App\Http\Controllers\UserController;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('updates a user with programs array without array to string conversion error', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($admin);

    $programA = Program::create(['name' => 'Program A', 'description' => 'Test program A', 'status' => 'Active']);
    $programB = Program::create(['name' => 'Program B', 'description' => 'Test program B', 'status' => 'Active']);

    $targetUser = User::factory()->create([
        'role' => User::ROLE_STAFF,
        'is_active' => true,
        'employee_id' => 'EMP-001',
        'address' => '123 Test St',
        'program_id' => null,
        'password' => Hash::make('password'),
    ]);

    $request = Request::create(route('users.update', $targetUser), 'PUT', [
        'name' => $targetUser->name,
        'email' => $targetUser->email,
        'employee_id' => 'EMP-001',
        'address' => '123 Updated St',
        'role' => User::ROLE_STAFF,
        'is_active' => '1',
        'programs' => [$programA->id, $programB->id],
    ]);

    $request->setUserResolver(fn () => $admin);

    $controller = app(UserController::class);
    $response = $controller->update($request, $targetUser);

    $targetUser->refresh();
    expect($targetUser->address)->toBe('123 Updated St');
    expect($targetUser->program_id)->toBe($programA->id);
    expect($targetUser->programs->pluck('id')->toArray())->toBe([$programA->id, $programB->id]);
});

it('stores a new user with programs and sets the primary program_id', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($admin);

    $programA = Program::create(['name' => 'Program A', 'description' => 'Test program A', 'status' => 'Active']);
    $programB = Program::create(['name' => 'Program B', 'description' => 'Test program B', 'status' => 'Active']);

    $request = Request::create(route('users.store'), 'POST', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'employee_id' => 'EMP-002',
        'address' => '456 Test Ave',
        'role' => User::ROLE_STAFF,
        'is_active' => '1',
        'programs' => [$programA->id, $programB->id],
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $request->setUserResolver(fn () => $admin);

    $controller = app(UserController::class);
    $controller->store($request);

    $newUser = User::where('email', 'newuser@example.com')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->program_id)->toBe($programA->id);
    expect($newUser->programs->pluck('id')->toArray())->toBe([$programA->id, $programB->id]);
});
