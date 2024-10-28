<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Models\WorkerCategory;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Sajya\Server\Procedure;

class WorkerCategoryProcedure extends Procedure
{
    public static string $name = 'worker_category';

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function createWorkerCategory(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = json_decode($request->getContent(), true)['params'];
        $category = WorkerCategory::create(['name' => $data['name']]);

        return $responseBuilder->setData(['category' => $category->toArray()])->setMessage("Worker category created successfully.")->build();
    }

}
