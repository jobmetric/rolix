<?php

namespace JobMetric\Rolix\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abort unless the authenticated user has the given Rolix permission.
 *
 * Usage: middleware('rolix.permission:hero.view')
 * Optional memberable context via request attribute `rolix.memberable`.
 */
class EnsurePermission
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param string $permission
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if ($user === null || ! method_exists($user, 'hasPermission')) {
            abort(403);
        }

        $context = $request->attributes->get('rolix.memberable');
        $collection = $request->attributes->get('rolix.collection');

        if (! $user->hasPermission($permission, $context instanceof Model ? $context : null, is_string($collection) ? $collection : null)) {
            abort(403);
        }

        return $next($request);
    }
}
