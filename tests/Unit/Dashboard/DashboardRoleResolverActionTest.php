<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard;

use App\Actions\Dashboard\DashboardRoleResolverAction;
use App\Enums\DashboardVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DashboardRoleResolverActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_resolves_pimpinan_for_owner_and_admin(): void
    {
        $this->assertVariant('owner@prokerin.test', DashboardVariant::Pimpinan);
        $this->assertVariant('admin@prokerin.test', DashboardVariant::Pimpinan);
    }

    public function test_it_resolves_sekretaris_for_secretary(): void
    {
        $this->assertVariant('sekretaris@prokerin.test', DashboardVariant::Sekretaris);
    }

    public function test_it_resolves_bendahara_for_treasurer(): void
    {
        $this->assertVariant('bendahara@prokerin.test', DashboardVariant::Bendahara);
    }

    public function test_it_resolves_operasional_for_project_lead_and_division_coordinator(): void
    {
        $this->assertVariant('lead@prokerin.test', DashboardVariant::Operasional);
        $this->assertVariant('koordinator@prokerin.test', DashboardVariant::Operasional);
    }

    public function test_it_resolves_member_and_viewer(): void
    {
        $this->assertVariant('member@prokerin.test', DashboardVariant::Member);
        $this->assertVariant('viewer@prokerin.test', DashboardVariant::Viewer);
    }

    public function test_it_resolves_highest_role_when_user_has_multiple_roles(): void
    {
        $organizationId = $this->organizationId();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();

        DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->where('user_id', $member->id)
            ->update(['role' => 'organization_owner']);

        DB::table('project_members')->updateOrInsert(
            [
                'project_id' => $this->projectId(),
                'user_id' => $member->id,
            ],
            [
                'role' => 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        DB::table('project_members')->updateOrInsert(
            [
                'project_id' => $this->projectId(),
                'user_id' => $treasurer->id,
            ],
            [
                'role' => 'project_lead',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $this->assertSame(DashboardVariant::Pimpinan, $this->resolve($member));
        $this->assertSame(DashboardVariant::Bendahara, $this->resolve($treasurer));
    }

    private function assertVariant(string $email, DashboardVariant $expected): void
    {
        $user = User::query()->where('email', $email)->firstOrFail();

        $this->assertSame($expected, $this->resolve($user));
    }

    private function resolve(User $user): DashboardVariant
    {
        return app(DashboardRoleResolverAction::class)->execute($user, $this->organizationId());
    }

    private function organizationId(): int
    {
        return (int) DB::table('organizations')->where('slug', 'bem-fakultas-teknologi')->value('id');
    }

    private function projectId(): int
    {
        return (int) DB::table('projects')->where('slug', 'seminar-karier-digital')->value('id');
    }
}
