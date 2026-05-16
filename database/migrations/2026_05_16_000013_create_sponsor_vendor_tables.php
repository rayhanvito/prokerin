<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsors_vendors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('name');
            $table->string('category')->index();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'type', 'status']);
        });

        Schema::create('sponsor_vendor_project_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sponsor_vendor_id')->constrained('sponsors_vendors')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('role_description');
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamp('linked_at');
            $table->timestamps();

            $table->unique(['sponsor_vendor_id', 'project_id'], 'sponsor_vendor_project_unique');
        });

        Schema::create('sponsor_vendor_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sponsor_vendor_id')->constrained('sponsors_vendors')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['sponsor_vendor_id', 'document_id'], 'sponsor_vendor_document_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_vendor_documents');
        Schema::dropIfExists('sponsor_vendor_project_links');
        Schema::dropIfExists('sponsors_vendors');
    }
};
