<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Models\WorkerCategory;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sajya\Server\Procedure;
use Illuminate\Support\Facades\Validator;

class WorkerCategoryProcedure extends Procedure
{
    public static string $name = 'worker_category';

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     * @throws ValidationException
     */
    public function createWorkerCategory(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = json_decode($request->getContent(), true)['params'];

        Validator::make($data, [
            'name' => 'required|string|max:255',
        ])->validate();

        $category = WorkerCategory::create(['name' => $data['name']]);

        return $responseBuilder->setData(['category' => $category->toArray()])->setMessage("Worker category created successfully.")->build();
    }

}
