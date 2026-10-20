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
        $items = DB::table('items')->whereNotNull('item_code')->get();
        foreach ($items as $item) {
            DB::table('receiving_items')
                ->where('item_id', $item->id)
                ->update(['item_code' => $item->item_code]);
        }

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
