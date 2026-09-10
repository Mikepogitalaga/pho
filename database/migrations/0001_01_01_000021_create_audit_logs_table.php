<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('action');           // created, updated, deleted, status_changed
            $table->string('module');           // Receiving, Release, PAS, Item, Supplier, etc.
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('record_label')->nullable();
            $table->json('changes')->nullable(); // ['field' => ['old' => x, 'new' => y]]
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
