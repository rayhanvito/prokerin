<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('handover_packages', function (Blueprint $table): void {
            $table->foreignId('incoming_owner_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_by_user_id')->nullable()->after('accepted_at')->constrained('users')->nullOnDelete();

            $table->index(['organization_id', 'incoming_owner_id']);
        });
    }

    public function down(): void
    {
        Schema::table('handover_packages', function (Blueprint $table): void {
            $table->dropIndex(['organization_id', 'incoming_owner_id']);
            $table->dropConstrainedForeignId('accepted_by_user_id');
            $table->dropConstrainedForeignId('incoming_owner_id');
        });
    }
};
