<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // notifications: speed up "unread per user" queries (notification bell badge).
        if (Schema::hasTable('notifications') && ! $this->indexExists('notifications', 'notifications_notifiable_read_at_idx')) {
            Schema::table('notifications', function (Blueprint $table): void {
                $table->index(['notifiable_id', 'read_at'], 'notifications_notifiable_read_at_idx');
            });
        }

        // attendance_qr_tokens: speed up token lookup with expiry filter at check-in.
        if (Schema::hasTable('attendance_qr_tokens') && ! $this->indexExists('attendance_qr_tokens', 'attendance_qr_tokens_hash_expires_idx')) {
            Schema::table('attendance_qr_tokens', function (Blueprint $table): void {
                $table->index(['token_hash', 'expires_at'], 'attendance_qr_tokens_hash_expires_idx');
            });
        }

        // projects: speed up "active proker per organization with start date sort".
        if (Schema::hasTable('projects') && ! $this->indexExists('projects', 'projects_org_starts_at_idx')) {
            Schema::table('projects', function (Blueprint $table): void {
                $table->index(['organization_id', 'starts_at'], 'projects_org_starts_at_idx');
            });
        }

        // budget_lines: tenant + status filter (finance approval queue).
        if (Schema::hasTable('budget_lines') && ! $this->indexExists('budget_lines', 'budget_lines_status_updated_idx')) {
            Schema::table('budget_lines', function (Blueprint $table): void {
                $table->index(['status', 'updated_at'], 'budget_lines_status_updated_idx');
            });
        }

        // documents: visibility + tenant filter (search + folder browse).
        if (Schema::hasTable('documents') && ! $this->indexExists('documents', 'documents_org_visibility_idx')) {
            Schema::table('documents', function (Blueprint $table): void {
                $table->index(['organization_id', 'visibility'], 'documents_org_visibility_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notifications') && $this->indexExists('notifications', 'notifications_notifiable_read_at_idx')) {
            Schema::table('notifications', function (Blueprint $table): void {
                $table->dropIndex('notifications_notifiable_read_at_idx');
            });
        }

        if (Schema::hasTable('attendance_qr_tokens') && $this->indexExists('attendance_qr_tokens', 'attendance_qr_tokens_hash_expires_idx')) {
            Schema::table('attendance_qr_tokens', function (Blueprint $table): void {
                $table->dropIndex('attendance_qr_tokens_hash_expires_idx');
            });
        }

        if (Schema::hasTable('projects') && $this->indexExists('projects', 'projects_org_starts_at_idx')) {
            Schema::table('projects', function (Blueprint $table): void {
                $table->dropIndex('projects_org_starts_at_idx');
            });
        }

        if (Schema::hasTable('budget_lines') && $this->indexExists('budget_lines', 'budget_lines_status_updated_idx')) {
            Schema::table('budget_lines', function (Blueprint $table): void {
                $table->dropIndex('budget_lines_status_updated_idx');
            });
        }

        if (Schema::hasTable('documents') && $this->indexExists('documents', 'documents_org_visibility_idx')) {
            Schema::table('documents', function (Blueprint $table): void {
                $table->dropIndex('documents_org_visibility_idx');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $rows = $connection->select("PRAGMA index_list('{$table}')");

            foreach ($rows as $row) {
                if (($row->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $database = $connection->getDatabaseName();
            $rows = $connection->select(
                'SELECT COUNT(*) as cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$database, $table, $indexName],
            );

            return ($rows[0]->cnt ?? 0) > 0;
        }

        return false;
    }
};
