<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use \Illuminate\Auth\Middleware\Authenticate;


class CustomSanctumMiddleware
{
    protected $routesWithoutSanctum = [
        'login',
        'register',
        'ping'
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $content = $request->getContent();
        $json = json_decode($content, true);
        if (isset($json['jsonrpc'], $json['method'])) {
            $method = $json['method'];
            foreach ($this->routesWithoutSanctum as $routName) {
                if ($method == $routName) {
                    return $next($request);
                }
            }
        }
        $sanctum = new Authenticate(auth());
        return $sanctum->handle($request, $next, 'sanctum');
    }


}
