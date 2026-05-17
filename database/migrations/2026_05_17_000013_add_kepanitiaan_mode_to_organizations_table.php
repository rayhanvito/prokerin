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
            $table->string('mode')->default('organization')->after('plan_tier')->index();
            $table->date('event_date')->nullable()->after('mode');
            $table->timestamp('auto_archive_at')->nullable()->after('event_date')->index();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            $table->dropColumn(['mode', 'event_date', 'auto_archive_at']);
        });
    }
};
