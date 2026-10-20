<?php

use App\Models\Item;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders dashboard chart script without javascript syntax errors from blade output', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);

    Item::create([
        'name' => 'Dashboard Test Item',
        'category' => 'General',
        'unit' => 'pcs',
        'quantity_on_hand' => 5,
        'reorder_level' => 1,
        'unit_cost' => 10,
        'stock_keeping_unit' => 'SKU-TEST',
        'program_coordinator' => 'Program Test',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();

    $content = $response->getContent();

    expect($content)->toContain("function(d) { return d.category; }");
    expect($content)->not->toContain("function(d) d.");
    expect($content)->not->toContain("return (int) d.received");
    expect($content)->not->toContain("return (int) d.released");
});

it('renders the program-scoped dashboard script using javascript number parsing instead of php casts', function () {
    $program = Program::create([
        'name' => 'Program Scoped Test',
        'description' => 'Program scoped dashboard test',
        'status' => 'Active',
    ]);

    $user = User::factory()->create([
        'role' => User::ROLE_STAFF,
        'is_active' => true,
        'program_id' => $program->id,
    ]);
    $user->programs()->attach($program);

    Item::create([
        'name' => 'Program Scoped Test Item',
        'category' => 'General',
        'unit' => 'pcs',
        'quantity_on_hand' => 5,
        'reorder_level' => 1,
        'unit_cost' => 10,
        'stock_keeping_unit' => $program->name,
        'program_coordinator' => 'Program Test',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();

    $content = $response->getContent();

    // The program-scoped supply movement chart must coerce values in JavaScript,
    // not with PHP-only casts that would break the inline script.
    expect($content)->toContain('return parseInt(d.received, 10) || 0;');
    expect($content)->toContain('return parseInt(d.released, 10) || 0;');
    expect($content)->not->toContain('(int) d.received');
    expect($content)->not->toContain('(int) d.released');
    expect($content)->not->toContain('(float) d.');
});
