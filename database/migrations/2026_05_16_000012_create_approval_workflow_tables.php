<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflow_definitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('workflow_type')->index();
            $table->json('steps');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['organization_id', 'workflow_type'], 'approval_def_org_type_unique');
        });

        Schema::create('approval_instances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workflow_definition_id')->constrained('approval_workflow_definitions')->cascadeOnDelete();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('current_step')->default(1);
            $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('approval_step_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instance_id')->constrained('approval_instances')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision')->default('pending')->index();
            $table->text('note')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->unique(['instance_id', 'step_order']);
        });

        Schema::create('approval_delegations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('step_record_id')->constrained('approval_step_records')->cascadeOnDelete();
            $table->foreignId('delegated_from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('delegated_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('delegated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_delegations');
        Schema::dropIfExists('approval_step_records');
        Schema::dropIfExists('approval_instances');
        Schema::dropIfExists('approval_workflow_definitions');
    }
};
