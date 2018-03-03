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
        $campaign = \Route::current()->parameter('campaign');
        $campaign = $campaign instanceof Models\Campaign ? $campaign : Models\Campaign::find($campaign);
        if (
            !Models\User::Me()->campaigns()->where('campaigns.id', '=', $campaign->id)->exists()
            && !Models\User::Me()->is_superadmin
        )
            \abort(500);

        return $next($request);
    }
}
