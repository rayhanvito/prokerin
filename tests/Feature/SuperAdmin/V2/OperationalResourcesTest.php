<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin\V2;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\AiUsageLogs\AiUsageLogResource;
use App\Filament\Resources\Campuses\CampusResource;
use App\Filament\Resources\CertificateRecipients\CertificateRecipientResource;
use App\Filament\Resources\EventRegistrations\EventRegistrationResource;
use App\Filament\Resources\OrganizationInvitations\OrganizationInvitationResource;
use App\Filament\Resources\PaymentOrders\PaymentOrderResource;
use App\Filament\Resources\WhatsAppDeliveryLogs\WhatsAppDeliveryLogResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OperationalResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_super_admin_can_open_operational_resource_indexes(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $resources = [
            ActivityLogResource::class,
            OrganizationInvitationResource::class,
            CampusResource::class,
            PaymentOrderResource::class,
            EventRegistrationResource::class,
            WhatsAppDeliveryLogResource::class,
            AiUsageLogResource::class,
            CertificateRecipientResource::class,
        ];

        foreach ($resources as $resource) {
            $this->actingAs($superAdmin)
                ->get($resource::getUrl())
                ->assertOk();
        }
    }

    public function test_regular_user_cannot_open_operational_resource_indexes(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->get(ActivityLogResource::getUrl())
            ->assertForbidden();
    }
}
