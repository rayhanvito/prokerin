<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handover_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_period_id')->nullable()->constrained('organization_periods')->nullOnDelete();
            $table->foreignId('to_period_id')->nullable()->constrained('organization_periods')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft')->index();
            $table->json('snapshot');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'from_period_id', 'status']);
        });

        Schema::create('handover_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('handover_packages')->cascadeOnDelete();
            $table->string('category')->index();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('status')->default('pending')->index();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['package_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handover_items');
        Schema::dropIfExists('handover_packages');
    }
};
