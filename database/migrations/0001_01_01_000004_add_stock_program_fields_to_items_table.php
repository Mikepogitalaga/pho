<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('items', 'stock_keeping_unit')) {
            Schema::table('items', function (Blueprint $table) {
                $table->string('stock_keeping_unit')->nullable()->after('location');
            });
        }

        if (!Schema::hasColumn('items', 'program_coordinator')) {
            Schema::table('items', function (Blueprint $table) {
                $table->string('program_coordinator')->nullable()->after('stock_keeping_unit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('items', 'program_coordinator')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('program_coordinator');
            });
        }

        if (Schema::hasColumn('items', 'stock_keeping_unit')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('stock_keeping_unit');
            });
        }
    }
};
