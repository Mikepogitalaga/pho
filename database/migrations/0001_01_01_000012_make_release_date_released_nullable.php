<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('releases')) {
            DB::statement('ALTER TABLE `releases` MODIFY `date_released` DATE NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('releases')) {
            DB::statement('ALTER TABLE `releases` MODIFY `date_released` DATE NOT NULL');
        }
    }
};
