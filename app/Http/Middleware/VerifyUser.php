<?php

namespace Jolt\Http\Middleware;

use Closure;
use Jolt\Models;

class VerifyUser
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

        return $next($request);
    }
}
