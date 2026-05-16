<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('organization_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
        });

        Schema::create('organization_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->index();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
        });

        Schema::create('organization_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('email')->index();
            $table->string('role');
            $table->string('status')->default('pending')->index();
            $table->string('token')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->string('label');
            $table->text('proposal_outline');
            $table->json('tasks');
            $table->json('budget_lines');
            $table->json('lpj_checklist');
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_period_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_lead_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('status')->default('draft')->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'status']);
        });

        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->index();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('division')->nullable();
            $table->string('status')->default('backlog')->index();
            $table->date('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['pic_user_id', 'due_at']);
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('folder');
            $table->string('storage_path');
            $table->string('mime_type');
            $table->unsignedInteger('size_kb');
            $table->string('visibility')->default('private')->index();
            $table->string('status')->default('uploaded')->index();
            $table->timestamps();

            $table->index(['organization_id', 'folder']);
        });

        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category');
            $table->unsignedBigInteger('planned_amount');
            $table->unsignedBigInteger('realized_amount')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });

        Schema::create('budget_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('receipt_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('name');
            $table->unsignedBigInteger('amount');
            $table->string('status')->default('review')->index();
            $table->timestamps();
        });

        Schema::create('proposal_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('subtitle');
            $table->json('sections');
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('lpj_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_required')->default(true);
            $table->boolean('is_complete')->default(false);
            $table->timestamps();
        });

        Schema::create('notification_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('event')->index();
            $table->string('label');
            $table->string('audience');
            $table->json('channels');
            $table->string('trigger');
            $table->string('status')->default('planned')->index();
            $table->timestamps();
        });

        Schema::create('document_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_title');
            $table->string('document_type');
            $table->string('format');
            $table->string('queue_name');
            $table->string('engine');
            $table->string('storage_disk');
            $table->string('output_path');
            $table->string('status')->default('queued')->index();
            $table->timestamps();
        });

        Schema::create('role_permission_matrix', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('label');
            $table->string('scope');
            $table->json('permissions');
            $table->boolean('is_system_role')->default(false);
            $table->timestamps();

            $table->unique(['role', 'scope']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission_matrix');
        Schema::dropIfExists('document_exports');
        Schema::dropIfExists('notification_rules');
        Schema::dropIfExists('lpj_checklist_items');
        Schema::dropIfExists('proposal_drafts');
        Schema::dropIfExists('budget_transactions');
        Schema::dropIfExists('budget_lines');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_templates');
        Schema::dropIfExists('organization_invitations');
        Schema::dropIfExists('organization_members');
        Schema::dropIfExists('organization_periods');
        Schema::dropIfExists('organizations');
    }
};
