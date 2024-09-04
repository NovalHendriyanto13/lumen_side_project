<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

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
        if ($this->auth->guard($guard)->guest()) {
            return response() ->json(['error' => 'Unauthorized.'], 401);
        }
        try {
            // Attempt to decode the token and retrieve the user
            $user = JWTAuth::parseToken()->authenticate();

            // If the token is valid but no user is found, return an error
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Attach the authenticated user to the request for further use
            $request->auth = $user;

        } catch (TokenExpiredException $e) {
            // Handle token expiration
            return response()->json(['error' => 'Token expired'], 401);

        } catch (TokenInvalidException $e) {
            // Handle invalid token
            return response()->json(['error' => 'Token invalid'], 401);

        } catch (JWTException $e) {
            // Handle missing token
            return response()->json(['error' => 'Token not provided'], 401);
        }
        
        return $next($request);
    }
}
