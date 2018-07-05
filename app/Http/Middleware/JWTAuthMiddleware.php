<?php

namespace App\Http\Middleware;

use Log;
use JWTAuth;
use Closure;
use Exception;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class JWTAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::toUser($request->header('Authorization'));
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException ) {
                Log::info('Token invalid');
                return response()->json(['error'=> 'Token invalid'], 401);
            } else if ($e instanceof TokenExpiredException) {
                Log::info('Token is expired');
                return response()->json(['error' => 'Token is expired'], 401);
            } else {
                Log::info('Something is wrong');
                return response()->json(['error' => 'Something is wrong'], 401);
            }
        }
        // Log::info('Authorization token '. JWTAuth::toUser($request->header('Authorization')));
        return $next($request);
    }
}
