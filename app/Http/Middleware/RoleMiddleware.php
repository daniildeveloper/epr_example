<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(
        $request,
        Closure $next,
        $role
    ) {

        $roles = is_array($role)
        ? $role
        : explode('|', $role);

        if (!JWTAuth::toUser($request->header('authorization'))->hasAnyRole($roles)) {
            throw UnauthorizedException::forRoles($roles);
        }
        return $next($request);
    }
}
