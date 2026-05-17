<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('letter_type')->index();
            $table->longText('template_html');
            $table->string('numbering_pattern')->default('B.{seq}/{type_code}/{roman_month}/{year}');
            $table->foreignId('signatory_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'letter_type', 'is_active']);
        });

        Schema::create('letters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('letter_templates')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('letter_number')->index();
            $table->string('letter_type')->index();
            $table->string('subject');
            $table->json('body_data');
            $table->string('recipient_name');
            $table->string('recipient_organization')->nullable();
            $table->string('rendered_pdf_path')->nullable();
            $table->string('status')->default('draft')->index();
            $table->foreignId('drafted_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('signed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'letter_type', 'status']);
            $table->unique(['organization_id', 'letter_number']);
        });

        Schema::create('letter_number_sequences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('sequence')->default(0);
            $table->timestamps();

            $table->unique(['organization_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_number_sequences');
        Schema::dropIfExists('letters');
        Schema::dropIfExists('letter_templates');
    }
};
