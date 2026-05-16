<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Dashboard\SidebarMenuAction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;
use Lab404\Impersonate\Services\ImpersonateManager;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $organization = $user === null ? null : $this->activeOrganization((int) $user->id);
        $campus = $user === null || $organization !== null ? null : $this->activeCampus((int) $user->id);

        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
                'activeOrganization' => $organization !== null ? [
                    'name' => $organization['name'],
                    'period' => $organization['period'],
                    'role' => $organization['role'],
                ] : [
                    'name' => $campus['name'] ?? 'Belum ada organisasi',
                    'period' => $campus['domain'] ?? '-',
                    'role' => $campus === null ? 'viewer' : 'campus_admin',
                ],
            ],
            'auth' => [
                'user' => $user,
            ],
            'sidebarMenu' => $user !== null && $organization !== null
                ? app(SidebarMenuAction::class)->execute($user, (int) $organization['id'])
                : ($campus === null ? [] : $this->campusSidebarMenu()),
            'flash' => [
                'success' => fn (): ?string => $this->sessionString($request, 'success'),
                'error' => fn (): ?string => $this->sessionString($request, 'error'),
                'status' => fn (): ?string => $this->sessionString($request, 'status'),
                'aiSuggestion' => fn (): ?array => $this->sessionArray($request, 'aiSuggestion'),
                'attendanceQrToken' => fn (): ?array => $this->sessionArray($request, 'attendanceQrToken'),
            ],
            'impersonating' => $this->impersonationContext(),
        ];
    }

    /**
     * @return array{active: bool, impersonator: ?string, leaveUrl: string}|null
     */
    private function impersonationContext(): ?array
    {
        $manager = app(ImpersonateManager::class);

        if (! $manager->isImpersonating()) {
            return null;
        }

        $impersonatorId = $manager->getImpersonatorId();
        $impersonator = $impersonatorId === null
            ? null
            : User::query()->find($impersonatorId);

        return [
            'active' => true,
            'impersonator' => $impersonator instanceof User ? (string) $impersonator->name : null,
            'leaveUrl' => route('impersonate.leave', absolute: false),
        ];
    }

    private function sessionString(Request $request, string $key): ?string
    {
        $value = $request->session()->get($key);

        return is_string($value) ? $value : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function sessionArray(Request $request, string $key): ?array
    {
        $value = $request->session()->get($key);

        return is_array($value) ? $value : null;
    }

    /**
     * @return array{id: int, name: string, domain: string}|null
     */
    private function activeCampus(int $userId): ?array
    {
        $campus = DB::table('campuses')
            ->where('admin_user_id', $userId)
            ->first(['id', 'name', 'domain']);

        if ($campus === null) {
            return null;
        }

        return [
            'id' => (int) $campus->id,
            'name' => (string) $campus->name,
            'domain' => (string) $campus->domain,
        ];
    }

    /**
     * @return array<int, array{groupLabel: string, items: array<int, array{label: string, href: string, icon: string, badgeCount: null}>}>
     */
    private function campusSidebarMenu(): array
    {
        return [
            [
                'groupLabel' => 'Kampus',
                'items' => [
                    [
                        'label' => 'Dashboard Kampus',
                        'href' => route('campus.dashboard', absolute: false),
                        'icon' => 'Building2',
                        'badgeCount' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{id: int, name: string, period: string, role: string}|null
     */
    private function activeOrganization(int $userId): ?array
    {
        $organization = DB::table('organization_members')
            ->join('organizations', 'organizations.id', '=', 'organization_members.organization_id')
            ->leftJoin('organization_periods', function ($join): void {
                $join->on('organization_periods.organization_id', '=', 'organizations.id')
                    ->where('organization_periods.is_active', true);
            })
            ->where('organization_members.user_id', $userId)
            ->orderBy('organization_members.id')
            ->first([
                'organizations.id',
                'organizations.name',
                'organization_members.role',
                'organization_periods.name as period_name',
            ]);

        if ($organization === null) {
            $organization = DB::table('project_members')
                ->join('projects', 'projects.id', '=', 'project_members.project_id')
                ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
                ->leftJoin('organization_periods', function ($join): void {
                    $join->on('organization_periods.organization_id', '=', 'organizations.id')
                        ->where('organization_periods.is_active', true);
                })
                ->where('project_members.user_id', $userId)
                ->orderBy('project_members.id')
                ->first([
                    'organizations.id',
                    'organizations.name',
                    'project_members.role',
                    'organization_periods.name as period_name',
                ]);
        }

        if ($organization === null) {
            return null;
        }

        return [
            'id' => (int) $organization->id,
            'name' => (string) $organization->name,
            'period' => (string) ($organization->period_name ?? '-'),
            'role' => (string) $organization->role,
        ];
    }
}
