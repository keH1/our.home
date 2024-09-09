<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OneCMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $staticToken = config('app.one_c_token');
        $token = $request->header('Authorization');

        if ($token !== 'Bearer ' . $staticToken) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
