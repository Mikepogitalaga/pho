<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('unit')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->string('location')->nullable();
            $table->timestamps();
        });

        Schema::create('receivings', function (Blueprint $table) {
            $table->id();
            $table->string('receiving_number')->unique();
            $table->string('po_number')->nullable();
            $table->string('source_document_number')->nullable();
            $table->string('ics_ptr_ris')->nullable();
            $table->date('document_date')->nullable();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->date('date_received');
            $table->string('received_by')->nullable();
            $table->string('location')->nullable();
            $table->string('stock_keeping_unit')->nullable();
            $table->string('program_coordinator')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('receiving_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiving_id')->constrained('receivings')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('category')->nullable();
            $table->text('item_description')->nullable();
            $table->integer('quantity_received');
            $table->string('uom')->nullable();
            $table->string('lot_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('release_number')->unique();
            $table->string('pas_number')->nullable();
            $table->string('health_program_coordinator')->nullable();
            $table->string('ptr_itr_ris_no')->nullable();
            $table->string('pho_code')->nullable();
            $table->string('source_docs_ptr_po_no')->nullable();
            $table->string('facility_name')->nullable();
            $table->string('received_by')->nullable();
            $table->date('date_released');
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('release_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_id')->constrained('releases')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->text('item_description')->nullable();
            $table->integer('quantity_released');
            $table->string('uom')->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_items');
        Schema::dropIfExists('releases');
        Schema::dropIfExists('receiving_items');
        Schema::dropIfExists('receivings');
        Schema::dropIfExists('items');
        Schema::dropIfExists('suppliers');
    }
};
