<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proker_microsites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_published')->default(false)->index();
            $table->string('banner_image_path')->nullable();
            $table->text('description_md')->nullable();
            $table->string('location_text')->nullable();
            $table->string('location_maps_url')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_whatsapp')->nullable();
            $table->string('contact_email')->nullable();
            $table->boolean('show_countdown')->default(true);
            $table->boolean('show_committee')->default(true);
            $table->boolean('show_gallery')->default(true);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('proker_microsite_gallery', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('microsite_id')->constrained('proker_microsites')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['microsite_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proker_microsite_gallery');
        Schema::dropIfExists('proker_microsites');
    }
};
