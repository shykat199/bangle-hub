<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Keeps the admin panel to staff accounts only.
 *
 * The storefront and the admin panel share one login guard, so before this
 * existed any customer who registered on the shop could open /admin/* pages
 * simply by being logged in. Staff are always given a role from the panel
 * (admin / worker / any custom role); customers who self-register get none,
 * so "has a staff role" is the line that separates them.
 *
 * Deliberately permissive about WHICH staff role: any role that is not a
 * plain customer role passes, so adding a new role in the panel never locks
 * anyone out. Only the customer roles below and role-less accounts are
 * refused.
 */
class StaffOnly
{
    // Roles that belong to shop customers, not to staff.
    const CUSTOMER_ROLES = ['user', 'customer', 'client'];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'This section is restricted to administrators.');
        }

        // Older installs may predate the roles package; never lock the panel
        // out over a missing method.
        if (!method_exists($user, 'getRoleNames')) {
            return $next($request);
        }

        $roles = $user->getRoleNames()
            ->map(fn ($r) => strtolower(trim($r)))
            ->reject(fn ($r) => in_array($r, self::CUSTOMER_ROLES));

        if ($roles->isEmpty()) {
            abort(403, 'This section is restricted to administrators.');
        }

        return $next($request);
    }
}
