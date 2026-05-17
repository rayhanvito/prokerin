<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->index();
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('location')->nullable();
            $table->string('condition')->default('good')->index();
            $table->string('status')->default('available')->index();
            $table->string('qr_token', 64)->unique();
            $table->date('purchased_at')->nullable();
            $table->unsignedBigInteger('purchase_amount')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
