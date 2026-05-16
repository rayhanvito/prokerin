<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campuses', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('campus_organization_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['campus_id', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_organization_links');
        Schema::dropIfExists('campuses');
    }
};
