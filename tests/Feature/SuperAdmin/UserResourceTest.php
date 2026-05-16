<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\ActivityLog;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_super_admin_can_edit_user_name_and_email(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.test')->firstOrFail();
        $target = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($superAdmin);

        Livewire::test(EditUser::class, ['record' => $target->getRouteKey()])
            ->fillForm([
                'name' => 'Renamed Member',
                'email' => 'renamed-member@prokerin.test',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $target->refresh();

        $this->assertSame('Renamed Member', $target->name);
        $this->assertSame('renamed-member@prokerin.test', $target->email);
    }

    public function test_super_admin_role_is_not_listed_as_assignable_option(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.test')->firstOrFail();
        $target = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->actingAs($superAdmin);

        Livewire::test(EditUser::class, ['record' => $target->getRouteKey()])
            ->assertDontSeeHtml('value="super_admin"');
    }

    public function test_super_admin_cannot_delete_their_own_account(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.test')->firstOrFail();

        $this->actingAs($superAdmin);

        try {
            Livewire::test(ListUsers::class)
                ->callTableAction(DeleteAction::class, $superAdmin);
        } catch (\Throwable) {
            // Expected — guard throws to abort the action
        }

        $this->assertNotNull(User::query()->find($superAdmin->id));
    }

    public function test_super_admin_cannot_delete_sole_organization_owner(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.test')->firstOrFail();
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($superAdmin);

        try {
            Livewire::test(ListUsers::class)
                ->callTableAction(DeleteAction::class, $owner);
        } catch (\Throwable) {
            // Expected — guard throws to abort the action
        }

        $this->assertNotNull(User::query()->find($owner->id));
    }

    public function test_user_delete_is_logged_when_safe(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.test')->firstOrFail();
        $target = User::factory()->create([
            'email' => 'temp-user@prokerin.test',
            'name' => 'Temp User',
        ]);

        $this->actingAs($superAdmin);

        Livewire::test(ListUsers::class)
            ->callTableAction(DeleteAction::class, $target);

        $this->assertNull(User::query()->find($target->id));

        $log = ActivityLog::query()
            ->where('action', 'user.delete')
            ->where('target_type', User::class)
            ->where('target_id', $target->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('temp-user@prokerin.test', $log->payload['email'] ?? null);
        $this->assertSame('Temp User', $log->payload['name'] ?? null);
    }

    public function test_users_list_shows_all_users_to_super_admin(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.test')->firstOrFail();

        $this->actingAs($superAdmin);

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords(User::query()->limit(5)->get());
    }
}
