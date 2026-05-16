<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Actions\Dashboard\SidebarMenuAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SidebarMenuActionTest extends TestCase
{
    use RefreshDatabase;

    private int $organizationId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Cache::flush();

        $this->organizationId = (int) DB::table('organizations')
            ->where('slug', 'bem-fakultas-teknologi')
            ->value('id');
    }

    public function test_pimpinan_sidebar_exposes_full_leadership_menu(): void
    {
        $labels = $this->labelsFor('owner@prokerin.test');

        $this->assertContains('Anggota & Role', $labels);
        $this->assertContains('RAB & Keuangan', $labels);
        $this->assertContains('Sponsor & Vendor', $labels);
        $this->assertContains('Handover', $labels);
    }

    public function test_secretary_sidebar_hides_finance_and_handover_menus(): void
    {
        $labels = $this->labelsFor('sekretaris@prokerin.test');

        $this->assertContains('Proposal', $labels);
        $this->assertContains('Dokumen', $labels);
        $this->assertNotContains('RAB & Keuangan', $labels);
        $this->assertNotContains('Handover', $labels);
    }

    public function test_treasurer_sidebar_focuses_on_finance(): void
    {
        $labels = $this->labelsFor('bendahara@prokerin.test');

        $this->assertContains('RAB & Keuangan', $labels);
        $this->assertContains('Sponsor & Vendor', $labels);
        $this->assertNotContains('Proposal', $labels);
        $this->assertNotContains('Dokumen', $labels);
    }

    public function test_member_sidebar_only_exposes_personal_activity(): void
    {
        $labels = $this->labelsFor('member@prokerin.test');

        $this->assertContains('Task Saya', $labels);
        $this->assertContains('Proker', $labels);
        $this->assertNotContains('RAB & Keuangan', $labels);
        $this->assertNotContains('Anggota & Role', $labels);
    }

    public function test_sidebar_badges_are_scoped_to_user_and_organization(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $projectId = (int) DB::table('projects')
            ->where('organization_id', $this->organizationId)
            ->value('id');

        DB::table('project_tasks')->insert([
            'project_id' => $projectId,
            'pic_user_id' => $member->id,
            'title' => 'Konfirmasi vendor konsumsi',
            'division' => 'Acara',
            'status' => 'in_progress',
            'due_at' => now()->toDateString(),
            'completed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'sidebar-test',
            'notifiable_type' => User::class,
            'notifiable_id' => $member->id,
            'data' => json_encode(['message' => 'Perlu dicek']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $menu = app(SidebarMenuAction::class)->execute($member, $this->organizationId);

        $this->assertSame(1, $this->badgeFor($menu, 'Task Saya'));
        $this->assertSame(1, $this->badgeFor($menu, 'Notifikasi'));
    }

    public function test_finance_approval_badge_only_appears_for_finance_capable_roles(): void
    {
        $ownerMenu = app(SidebarMenuAction::class)->execute(
            User::query()->where('email', 'owner@prokerin.test')->firstOrFail(),
            $this->organizationId,
        );
        $secretaryMenu = app(SidebarMenuAction::class)->execute(
            User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail(),
            $this->organizationId,
        );

        $this->assertGreaterThan(0, $this->badgeFor($ownerMenu, 'RAB & Keuangan'));
        $this->assertNull($this->badgeFor($secretaryMenu, 'Proposal'));
    }

    /**
     * @return array<int, string>
     */
    private function labelsFor(string $email): array
    {
        $user = User::query()->where('email', $email)->firstOrFail();
        $menu = app(SidebarMenuAction::class)->execute($user, $this->organizationId);

        return array_values(array_map(
            static fn (array $item): string => (string) $item['label'],
            array_merge(...array_column($menu, 'items')),
        ));
    }

    /**
     * @param  array<int, array{groupLabel: string, items: array<int, array{label: string, href: string, icon: string, badgeCount: int|null}>}>  $menu
     */
    private function badgeFor(array $menu, string $label): ?int
    {
        foreach ($menu as $group) {
            foreach ($group['items'] as $item) {
                if ($item['label'] === $label) {
                    return $item['badgeCount'];
                }
            }
        }

        return null;
    }
}
