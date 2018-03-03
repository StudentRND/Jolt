<?php

namespace Jolt\Http\Middleware;

use Closure;
use Jolt\Models;

class VerifyAdmin
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
        $campaign = \Route::current()->parameter('campaign');
        $campaign = $campaign instanceof Models\Campaign ? $campaign : Models\Campaign::find($campaign);
        if (!Models\User::Me()->IsAdminFor($campaign) && !Models\User::Me()->is_superadmin) \abort(403);
        return $next($request);
    }
}
