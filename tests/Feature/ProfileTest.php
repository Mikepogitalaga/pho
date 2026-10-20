<?php

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

/**
 * Build an active program user (non-admin with a program assignment).
 */
function makeProgramUser(int $programId): User
{
    $user = User::factory()->create([
        'role' => User::ROLE_STAFF,
        'is_active' => true,
        'program_id' => $programId,
        'employee_id' => 'EMP-'.fake()->unique()->numerify('####'),
        'address' => '123 Test St',
        'password' => Hash::make('password'),
    ]);

    $user->programs()->attach($programId);

    return $user;
}

it('shows the profile link in the sidebar and opens the profile page for a program user', function () {
    $program = Program::create(['name' => 'Program A', 'description' => 'A', 'status' => 'Active']);
    $user = makeProgramUser($program->id);

    $response = $this->actingAs($user)->get(route('profile.edit'));

    $response->assertOk();
    $response->assertSee('My Profile');
    $response->assertSee('My Account');
    $response->assertSee(route('profile.edit'), false);
    $response->assertSee($program->name);
});

it('lets a program user update their own profile details', function () {
    $program = Program::create(['name' => 'Program A', 'description' => 'A', 'status' => 'Active']);
    $user = makeProgramUser($program->id);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Updated Program User',
        'employee_id' => 'EMP-9999',
        'address' => '456 Updated Ave',
        'email' => 'updated.program.user@example.com',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHas('success');

    $user->refresh();

    expect($user->name)->toBe('Updated Program User')
        ->and($user->employee_id)->toBe('EMP-9999')
        ->and($user->address)->toBe('456 Updated Ave')
        ->and($user->email)->toBe('updated.program.user@example.com')
        ->and($user->role)->toBe(User::ROLE_STAFF)
        ->and($user->program_id)->toBe($program->id);
});

it('lets a program user change their password with the current password', function () {
    $program = Program::create(['name' => 'Program A', 'description' => 'A', 'status' => 'Active']);
    $user = makeProgramUser($program->id);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'employee_id' => $user->employee_id,
        'address' => $user->address,
        'email' => $user->email,
        'current_password' => 'password',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHasNoErrors();

    expect(Hash::check('new-secret-password', $user->refresh()->password))->toBeTrue();
});

it('rejects a password change when the current password is wrong', function () {
    $program = Program::create(['name' => 'Program A', 'description' => 'A', 'status' => 'Active']);
    $user = makeProgramUser($program->id);
    $originalPassword = $user->password;

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'current_password' => 'not-my-password',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ]);

    $response->assertSessionHasErrors('current_password');

    expect($user->refresh()->password)->toBe($originalPassword);
});

it('rejects a new password without the current password', function () {
    $program = Program::create(['name' => 'Program A', 'description' => 'A', 'status' => 'Active']);
    $user = makeProgramUser($program->id);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ]);

    $response->assertSessionHasErrors('current_password');

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});

it('rejects an email address that belongs to another account', function () {
    $program = Program::create(['name' => 'Program A', 'description' => 'A', 'status' => 'Active']);
    $user = makeProgramUser($program->id);

    $otherUser = User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $otherUser->email,
    ]);

    $response->assertSessionHasErrors('email');

    expect($user->refresh()->email)->not->toBe($otherUser->email);
});

it('does not let a program user change their own role, program, or status from the profile page', function () {
    $program = Program::create(['name' => 'Program A', 'description' => 'A', 'status' => 'Active']);
    $otherProgram = Program::create(['name' => 'Program B', 'description' => 'B', 'status' => 'Active']);
    $user = makeProgramUser($program->id);

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'role' => User::ROLE_ADMIN,
        'program_id' => $otherProgram->id,
        'is_active' => '0',
    ]);

    $user->refresh();

    expect($user->role)->toBe(User::ROLE_STAFF)
        ->and($user->program_id)->toBe($program->id)
        ->and($user->is_active)->toBeTrue();
});

it('lets an administrator open the profile page as well', function () {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($admin)->get(route('profile.edit'));

    $response->assertOk();
    $response->assertSee('My Profile');
});
