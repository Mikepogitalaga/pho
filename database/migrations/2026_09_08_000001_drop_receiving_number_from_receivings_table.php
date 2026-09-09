<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receivings', function (Blueprint $table) {
            if (Schema::hasColumn('receivings', 'receiving_number')) {
                $table->dropColumn('receiving_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('receivings', function (Blueprint $table) {
            $table->string('receiving_number')->nullable()->after('id');
        });
    }
};
