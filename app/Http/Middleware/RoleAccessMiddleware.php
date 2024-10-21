<?php

namespace App\Http\Middleware;

use Closure;


class RoleAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $role = null)
    {
        if (!empty($role) && $role != $request->auth->role) {
            return response() ->json(['error' => 'Anda tidak di perkenankan untuk akses ini.'], 401);
        }

        if (empty($role) && ($request->auth->role == 'guest')) {
            return response() ->json(['error' => 'Admin Page.'], 401);
        }
        return $next($request);
    }
}
