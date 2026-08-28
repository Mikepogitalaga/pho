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
        if (!Schema::hasColumn('suppliers', 'supplier_type')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('supplier_type')->default('DOH')->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('suppliers', 'supplier_type')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('supplier_type');
            });
        }
    }
};

