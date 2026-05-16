<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Dashboard\SidebarMenuAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

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

        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
                'activeOrganization' => $organization === null ? [
                    'name' => 'Belum ada organisasi',
                    'period' => '-',
                    'role' => 'viewer',
                ] : [
                    'name' => $organization['name'],
                    'period' => $organization['period'],
                    'role' => $organization['role'],
                ],
            ],
            'auth' => [
                'user' => $user,
            ],
            'sidebarMenu' => $user !== null && $organization !== null
                ? app(SidebarMenuAction::class)->execute($user, (int) $organization['id'])
                : [],
            'flash' => [
                'success' => fn (): ?string => $this->sessionString($request, 'success'),
                'error' => fn (): ?string => $this->sessionString($request, 'error'),
                'status' => fn (): ?string => $this->sessionString($request, 'status'),
            ],
        ];
    }

    private function sessionString(Request $request, string $key): ?string
    {
        $value = $request->session()->get($key);

        return is_string($value) ? $value : null;
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
