<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('agenda');
            $table->string('location')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('status')->default('planned')->index();
            $table->timestamps();

            $table->index(['organization_id', 'starts_at']);
            $table->index(['project_id', 'status']);
        });

        Schema::create('meeting_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('attendance_status')->default('invited')->index();
            $table->timestamps();

            $table->index(['meeting_id', 'attendance_status']);
        });

        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('summary');
            $table->json('decisions');
            $table->json('action_items');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique('meeting_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_minutes');
        Schema::dropIfExists('meeting_attendees');
        Schema::dropIfExists('meetings');
    }
};
