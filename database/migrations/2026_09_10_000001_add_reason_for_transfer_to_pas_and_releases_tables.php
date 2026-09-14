<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_allocation_slips', function (Blueprint $table) {
            if (! Schema::hasColumn('property_allocation_slips', 'reason_for_transfer')) {
                $table->text('reason_for_transfer')->nullable()->after('purpose_activity');
            }
        });

        Schema::table('releases', function (Blueprint $table) {
            if (! Schema::hasColumn('releases', 'reason_for_transfer')) {
                $table->text('reason_for_transfer')->nullable()->after('transfer_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('property_allocation_slips', function (Blueprint $table) {
            if (Schema::hasColumn('property_allocation_slips', 'reason_for_transfer')) {
                $table->dropColumn('reason_for_transfer');
            }
        });

        Schema::table('releases', function (Blueprint $table) {
            if (Schema::hasColumn('releases', 'reason_for_transfer')) {
                $table->dropColumn('reason_for_transfer');
            }
        });
    }
};
