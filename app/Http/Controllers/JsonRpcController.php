<?php

namespace App\Http\Controllers;

use App\Enums\RpcApiMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sajya\Server\App;

class JsonRpcController extends Controller
{
    /**
     * @var App|null
     */
    protected ?App $guide;

    /**
     * Invoke the controller method.
     *
     * @param  Request  $request
     * @param  string[]  $procedures
     * @param  null|string  $delimiter
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, array $procedures, ?string $delimiter = null): JsonResponse
    {
        $guide = new App($procedures, $delimiter);
        $content = $request->getContent();
        $json = json_decode($content, true);

        if (isset($json['jsonrpc'], $json['method'])) {
            $method = $json['method'];

            if (!str_contains($method, '@')) {
                $fullMethodName = RpcApiMapper::{strtoupper($method)}?->value;

                if ($fullMethodName) {
                    $json['method'] = $fullMethodName;
                    $request->merge($json);
                }
            }
        }

        $response = $guide->handle(json_encode($request->all()));

        return response()->json($response);
    }
}
