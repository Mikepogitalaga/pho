<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('op_distribution_items', function (Blueprint $table) {
            $table->foreignId('item_id_2')->nullable()->constrained('items')->nullOnDelete()->after('lot_number');
            $table->text('item_description_2')->nullable()->after('item_id_2');
            $table->integer('quantity_2')->nullable()->after('item_description_2');
            $table->string('uom_2')->nullable()->after('quantity_2');
            $table->decimal('unit_cost_2', 12, 2)->nullable()->after('uom_2');
            $table->string('lot_number_2')->nullable()->after('unit_cost_2');
        });
    }

    public function down(): void
    {
        Schema::table('op_distribution_items', function (Blueprint $table) {
            $table->dropForeign(['item_id_2']);
            $table->dropColumn(['item_id_2', 'item_description_2', 'quantity_2', 'uom_2', 'unit_cost_2', 'lot_number_2']);
        });
    }
};
