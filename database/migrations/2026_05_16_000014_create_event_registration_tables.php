<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registration_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_open')->default(false)->index();
            $table->unsignedInteger('capacity')->nullable();
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->boolean('require_payment')->default(false);
            $table->timestamps();

            $table->unique('project_id');
            $table->index(['is_open', 'opens_at', 'closes_at']);
        });

        Schema::create('event_registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('participant_name');
            $table->string('participant_email')->index();
            $table->string('phone')->nullable();
            $table->string('institution')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('registered_at');
            $table->timestamps();

            $table->unique(['project_id', 'participant_email'], 'event_registration_project_email_unique');
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('event_registration_settings');
    }
};
