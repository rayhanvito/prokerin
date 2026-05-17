<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            $table->unsignedTinyInteger('onboarding_step')->default(1)->after('onboarding_completed_at');
            $table->boolean('onboarding_skipped')->default(false)->after('onboarding_step');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            $table->dropColumn(['onboarding_step', 'onboarding_skipped']);
        });
    }
};
