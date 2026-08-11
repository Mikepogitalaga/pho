<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('op_distributions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->date('date_distributed');
            $table->string('distributed_by')->nullable();
            $table->string('status')->default('Draft'); // Draft, Released, Canceled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('op_distribution_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_distribution_id')->constrained('op_distributions')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            // Patient info
            $table->string('patient_name');
            $table->unsignedTinyInteger('patient_age')->nullable();
            $table->string('patient_gender')->nullable(); // Male, Female, Other
            // Item info
            $table->text('item_description')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('uom')->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('lot_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('op_distribution_items');
        Schema::dropIfExists('op_distributions');
    }
};
