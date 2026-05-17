<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('borrower_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending')->index();
            $table->timestamp('loaned_at')->nullable();
            $table->timestamp('expected_return_at');
            $table->timestamp('returned_at')->nullable();
            $table->string('return_condition')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('overdue_notified_at')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'returned_at']);
            $table->index(['borrower_user_id', 'returned_at']);
            $table->index(['status', 'expected_return_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_loans');
    }
};
