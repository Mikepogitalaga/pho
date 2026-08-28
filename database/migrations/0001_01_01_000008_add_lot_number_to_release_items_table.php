<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('release_items', function (Blueprint $table) {
            $table->string('lot_number')->nullable()->after('uom');
        });
    }

    public function down(): void
    {
        Schema::table('release_items', function (Blueprint $table) {
            $table->dropColumn('lot_number');
        });
    }
};

