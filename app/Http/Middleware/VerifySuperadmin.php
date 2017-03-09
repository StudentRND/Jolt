<?php

namespace Jolt\Http\Middleware;

use Closure;
use Jolt\Models;

class VerifySuperadmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (!Models\User::Me()->is_superadmin) \abort(403);

        return $next($request);
    }
}
