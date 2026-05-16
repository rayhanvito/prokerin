<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('meeting_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('status')->default('open')->index();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['meeting_id', 'starts_at']);
        });

        Schema::create('attendance_qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['attendance_session_id', 'expires_at']);
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('meeting_attendee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('attendee_name');
            $table->string('attendee_email')->nullable();
            $table->string('check_in_method')->default('qr')->index();
            $table->timestamp('checked_in_at');
            $table->string('status')->default('present')->index();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['attendance_session_id', 'user_id'], 'attendance_records_session_user_unique');
            $table->unique(['attendance_session_id', 'meeting_attendee_id'], 'attendance_records_session_attendee_unique');
            $table->index(['attendance_session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_qr_tokens');
        Schema::dropIfExists('attendance_sessions');
    }
};
