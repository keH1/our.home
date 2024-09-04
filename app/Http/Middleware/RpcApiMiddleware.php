<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use \Illuminate\Auth\Middleware\Authenticate;


class RpcApiMiddleware
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
        if (is_array($json) && isset($json['jsonrpc'], $json['method'])) {
            $method = $json['method'];
            if(in_array($method,$this->routesWithoutSanctum)){
                return $next($request);
            }
        }
        $sanctum = new Authenticate(auth());

        return $sanctum->handle($request, $next, 'sanctum');
    }
}
