<?php

namespace App\Http\Controllers;

use App\Contracts\ProcedurePermissionsInterface;
use App\Enums\RpcApiMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sajya\Server\App;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Http\Response;

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
     * @return \Illuminate\Http\JsonResponse|\Sajya\Server\Http\Response
     */
    public function __invoke(Request $request, array $procedures, ?string $delimiter = null): JsonResponse|Response
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

        $rpcRequest = \Sajya\Server\Http\Request::loadArray($request->toArray());
        try {
            $procedure = $guide->findProcedure($rpcRequest);
            $this->checkPermissions($procedure);
        } catch (\Exception $e) {
            return Response::makeFromResult([], $rpcRequest)->setError($e);
        }

        $response = $guide->handle(json_encode($request->all()));

        return response()->json($response);
    }

    private function checkPermissions(string $procedureName)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new InvalidParams('Пользователь не авторизован');
        }

        [$procedureClass, $methodName] = explode('@', $procedureName);
        $procedure = app($procedureClass);

        if ($procedure instanceof ProcedurePermissionsInterface) {
            $methodPermissions = $procedure->getMethodsPermissions();
            $requiredPermissions = $methodPermissions[$methodName] ?? [];

            if (empty($requiredPermissions)) {
                return;
            }

            if (!$user->hasAnyPermission($requiredPermissions)) {
                throw new InvalidParams('Недостаточно прав для выполнения данного действия');
            }
        }
    }
}
