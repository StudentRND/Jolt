<?php

namespace Jolt\Http\Middleware;

use Closure;
use Jolt\Models;

class VerifyJoined
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
        if (!Models\User::IsLoggedIn()) \abort(401);
        if (
            !Models\User::Me()->campaigns()->where('campaign_id', '=', \Route::current()->parameter('campaign'))->exists()
            && !Models\User::Me()->is_superadmin
        )
            \abort(403);

        return $next($request);
    }
}
