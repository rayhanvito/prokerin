<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Workspace\GetActiveOrganizationContextAction;
use App\Support\Roles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureFinanceAccess
{
    public function __construct(
        private GetActiveOrganizationContextAction $activeOrganizationContext,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if($user === null, 403);

        $activeOrganizationId = $request->session()->get('active_organization_id');
        $context = $this->activeOrganizationContext->execute(
            actorUserId: (int) $user->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        );

        abort_unless(in_array($context->role, Roles::FINANCE_VIEWERS, true), 403);

        return $next($request);
    }
}
