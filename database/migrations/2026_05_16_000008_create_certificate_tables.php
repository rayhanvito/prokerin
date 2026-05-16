<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('template_html');
            $table->string('signature_label')->nullable();
            $table->string('signature_name')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->unique(['organization_id', 'name']);
        });

        Schema::create('certificate_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('certificate_templates')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_name');
            $table->string('recipient_email')->nullable();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('meeting_id')->nullable()->constrained()->nullOnDelete();
            $table->string('certificate_number')->unique();
            $table->timestamp('issued_at');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('verification_token')->unique();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'issued_at']);
            $table->index(['template_id', 'issued_at']);
            $table->index(['project_id', 'issued_at']);
            $table->index(['meeting_id', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_recipients');
        Schema::dropIfExists('certificate_templates');
    }
};
