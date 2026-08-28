<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_allocation_slips', function (Blueprint $table) {
            if (! Schema::hasColumn('property_allocation_slips', 'facility_name')) {
                $table->string('facility_name')->nullable()->after('purpose_activity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('property_allocation_slips', function (Blueprint $table) {
            if (Schema::hasColumn('property_allocation_slips', 'facility_name')) {
                $table->dropColumn('facility_name');
            }
        });
    }
};
