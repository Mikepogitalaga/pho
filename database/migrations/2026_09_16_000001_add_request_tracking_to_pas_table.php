<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_allocation_slips', function (Blueprint $table) {
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->string('request_status')->nullable()->after('requested_by');
        });
    }

    public function down(): void
    {
        Schema::table('property_allocation_slips', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropColumn(['requested_by', 'request_status']);
        });
    }
};
