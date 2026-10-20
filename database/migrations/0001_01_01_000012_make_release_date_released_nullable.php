<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('releases')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('ALTER TABLE releases RENAME COLUMN date_released TO date_released_tmp');
                DB::statement('ALTER TABLE releases ADD COLUMN date_released DATE NULL');
                DB::statement('UPDATE releases SET date_released = date_released_tmp');
                DB::statement('ALTER TABLE releases DROP COLUMN date_released_tmp');
            } else {
                DB::statement('ALTER TABLE `releases` MODIFY `date_released` DATE NULL');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('releases')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('ALTER TABLE releases RENAME COLUMN date_released TO date_released_tmp');
                DB::statement('ALTER TABLE releases ADD COLUMN date_released DATE NOT NULL');
                DB::statement('UPDATE releases SET date_released = date_released_tmp');
                DB::statement('ALTER TABLE releases DROP COLUMN date_released_tmp');
            } else {
                DB::statement('ALTER TABLE `releases` MODIFY `date_released` DATE NOT NULL');
            }
        }
    }
};
