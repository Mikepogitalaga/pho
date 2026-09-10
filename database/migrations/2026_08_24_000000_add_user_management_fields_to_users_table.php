<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // User Management fields
            $table->string('employee_id', 50)->nullable()->unique()->after('id');
            $table->string('address')->nullable()->after('name');
            $table->string('role', 20)->default('staff')->after('password'); // admin | staff
            $table->boolean('is_active')->default(true)->after('role');

            // Login attempt lock fields
            $table->unsignedInteger('failed_login_attempts')->default(0)->after('is_active');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
        });

        // Guarantee at least one admin exists — promote the earliest registered account.
        $firstUser = DB::table('users')->orderBy('id')->first();

        if ($firstUser && $firstUser->role !== 'admin') {
            DB::table('users')
                ->where('id', $firstUser->id)
                ->update(['role' => 'admin', 'updated_at' => now()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'address',
                'role',
                'is_active',
                'failed_login_attempts',
                'locked_until',
            ]);
        });
    }
};
