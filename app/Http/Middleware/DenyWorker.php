<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Blocks users with the 'worker' role from admin-only areas
 * (settings, couriers, coupons, user/role management, etc.).
 *
 * It ONLY restricts workers — admins, super-admins and every other
 * role keep the exact access they already had, so this can never
 * lock out the site owner.
 */
class DenyWorker
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('worker')) {
            abort(403, 'This section is restricted to administrators.');
        }

        return $next($request);
    }
}
