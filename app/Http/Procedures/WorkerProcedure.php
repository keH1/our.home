<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Attributes\RpcProcedure;
use App\Contracts\ProcedurePermissionsInterface;
use App\Enums\Permissions;
use App\Models\Worker;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Procedure;
use Illuminate\Support\Facades\Validator;

#[RpcProcedure(version: 'v1', group: 'workers')]
class WorkerProcedure extends Procedure implements ProcedurePermissionsInterface
{
    public static string $name = 'worker';

    public function getMethodsPermissions(): array
    {
        return [
            'createWorker' => [Permissions::NORMAL],
            'editWorker' => [Permissions::NORMAL],
            'getWorkers' => [Permissions::NORMAL],
        ];
    }

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     * @throws ValidationException
     */
    public function createWorker(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = json_decode($request->getContent(), true)['params'];

        Validator::make($data, [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:worker_categories,id',
            'status' => 'sometimes|in:active,disabled',
        ])->validate();

        $worker = Worker::create([
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'status' => 'active'
        ]);

        return $responseBuilder->setData(['worker' => $worker->toArray()])->setMessage("Worker created successfully.")->build();
    }

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     * @throws ValidationException
     */
    public function editWorker(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = json_decode($request->getContent(), true)['params'];

        Validator::make($data, [
            'id' => 'required|exists:workers,id',
            'name' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|exists:worker_categories,id',
            'status' => 'sometimes|in:active,disabled',
        ])->validate();

        $worker = Worker::find($data['id']);

        if (!$worker) {
            throw new InvalidParams(['message' => "Worker with ID {$data['id']} not found"]);
        }

        $worker->update([
            'name' => $data['name'] ?? $worker->name,
            'category_id' => $data['category_id'] ?? $worker->category_id,
            'status' => $data['status'] ?? $worker->status,
        ]);

        return $responseBuilder->setData(['worker' => $worker->toArray()])->setMessage("Worker updated successfully.")->build();
    }

    /**
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function getWorkers(ApiResponseBuilder $responseBuilder): array
    {
        return $responseBuilder->setData(Worker::all())->build();
    }

}
