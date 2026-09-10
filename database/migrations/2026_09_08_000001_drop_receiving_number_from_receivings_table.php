<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('receivings', 'receiving_number')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS receivings_receiving_number_unique');
                DB::statement('ALTER TABLE receivings DROP COLUMN receiving_number');
            } else {
                Schema::table('receivings', function (Blueprint $table) {
                    $table->dropColumn('receiving_number');
                });
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE receivings ADD COLUMN receiving_number VARCHAR(255) NULL');
        } else {
            Schema::table('receivings', function (Blueprint $table) {
                $table->string('receiving_number')->nullable()->after('id');
            });
        }
    }
};
