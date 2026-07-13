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
        if (!Schema::hasColumn('items', 'unit_cost')) {
            Schema::table('items', function (Blueprint $table) {
                $table->decimal('unit_cost', 12, 2)->nullable()->after('program_coordinator');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('items', 'unit_cost')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('unit_cost');
            });
        }
    }
};
