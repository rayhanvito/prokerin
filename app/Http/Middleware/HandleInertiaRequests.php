<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
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
        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
                'activeOrganization' => [
                    'name' => 'BEM Fakultas Teknologi',
                    'period' => '2026',
                    'role' => 'organization_owner',
                ],
            ],
            'auth' => [
                'user' => $request->user(),
            ],
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
}
