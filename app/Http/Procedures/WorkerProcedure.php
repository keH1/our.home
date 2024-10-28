<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Models\Worker;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Procedure;

class WorkerProcedure extends Procedure
{
    public static string $name = 'worker';

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function createWorker(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = json_decode($request->getContent(), true)['params'];
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
     */
    public function editWorker(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = json_decode($request->getContent(), true)['params'];
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

}
