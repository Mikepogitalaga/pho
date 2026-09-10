<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_allocation_slips', function (Blueprint $table) {
            if (! Schema::hasColumn('property_allocation_slips', 'transfer_type')) {
                $table->string('transfer_type')->default('PTR')->after('facility_coordinator');
            }
        });
    }

    public function down(): void
    {
        Schema::table('property_allocation_slips', function (Blueprint $table) {
            if (Schema::hasColumn('property_allocation_slips', 'transfer_type')) {
                $table->dropColumn('transfer_type');
            }
        });
    }
};
