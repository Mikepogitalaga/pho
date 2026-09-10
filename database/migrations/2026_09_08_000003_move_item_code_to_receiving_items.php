<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add item_code to receiving_items
        Schema::table('receiving_items', function (Blueprint $table) {
            $table->string('item_code')->nullable()->after('item_id');
        });

        // Copy existing item_code values from items to their receiving_items
        DB::statement('UPDATE receiving_items ri JOIN items i ON i.id = ri.item_id SET ri.item_code = i.item_code WHERE i.item_code IS NOT NULL');

        // Drop item_code from items
        Schema::table('items', function (Blueprint $table) {
            $table->dropUnique(['item_code']);
            $table->dropColumn('item_code');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('item_code')->nullable()->after('id');
        });

        Schema::table('receiving_items', function (Blueprint $table) {
            $table->dropColumn('item_code');
        });
    }
};
