<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_tiers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['project_id', 'is_active']);
        });

        Schema::table('event_registrations', function (Blueprint $table): void {
            $table->foreignId('ticket_tier_id')
                ->nullable()
                ->after('project_id')
                ->constrained('ticket_tiers')
                ->nullOnDelete();
        });

        Schema::create('payment_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('registration_id')->constrained('event_registrations')->cascadeOnDelete();
            $table->foreignId('tier_id')->constrained('ticket_tiers')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('status')->default('pending')->index();
            $table->string('provider_order_id')->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tier_id', 'status']);
            $table->index(['registration_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_orders');

        Schema::table('event_registrations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('ticket_tier_id');
        });

        Schema::dropIfExists('ticket_tiers');
    }
};
