<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_allocation_slips', function (Blueprint $table) {
            $table->id();
            $table->string('pas_number')->unique();
            $table->date('date_of_pass');
            $table->date('date_released')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('purpose_activity')->nullable();
            $table->string('facility_coordinator')->nullable();
            $table->string('program')->nullable();
            $table->string('status')->default('Pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pas_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pas_id')->constrained('property_allocation_slips')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->text('item_description')->nullable();
            $table->string('product_code')->nullable();
            $table->string('lot_number')->nullable();
            $table->date('expiration_date')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('unit')->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pas_items');
        Schema::dropIfExists('property_allocation_slips');
    }
};
