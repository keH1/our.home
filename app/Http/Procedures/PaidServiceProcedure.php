<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Models\PaidService;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Procedure;
use \App\Repositories\PaidServiceRepository;

class PaidServiceProcedure extends Procedure
{
    public static string $name = 'paid_service';

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @param PaidServiceRepository $paidServiceRepository
     * @return array
     */
    public function createPaidService(Request $request, ApiResponseBuilder $responseBuilder, PaidServiceRepository $paidServiceRepository): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $paidService = $paidServiceRepository->createPaidServiceObj($data);
        $paidService->save();

        return $responseBuilder->setData(['paid_service' => $paidService->toArray()])->setMessage("Paid service was created successfully")->build();
    }

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @param PaidServiceRepository $paidServiceRepository
     * @return array
     */
    public function updatePaidService(Request $request, ApiResponseBuilder $responseBuilder, PaidServiceRepository $paidServiceRepository): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        if (!is_int($data['id']) || !($data['id'] > 0)) {
            throw new InvalidParams(['message'=>"invalid value in 'id' field"]);
        }
        $paidService = PaidService::find($data['id']);
        $paidService = $paidServiceRepository->updatePaidService($data, $paidService);
        $paidService->save();

        return $responseBuilder->setData(['paid_service' => $paidService->toArray()])->setMessage("Paid service was updated successfully")->build();
    }

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function getPaidServices(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $paidServices = PaidService::with([
            'category' => function ($category) {
                $category->select('id', 'name');
            },
            'category.file' => function ($file) {
                $file->select('original_name', 'path', 'fileable_type', 'fileable_id');
            }
        ])->get();

        return $responseBuilder->setData(['paid_services' => $paidServices->toArray()])->build();
    }


    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function deletePaidService(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = json_decode($request->getContent(), true)['params'];
        if (!isset($data['id']) || !is_int($data['id']) || $data['id'] <= 0) {
            throw new InvalidParams(['message' => "Invalid value in 'id' field"]);
        } else {
            $paidService = PaidService::find($data['id']);
            if (!$paidService) {
                throw new InvalidParams(['message' => "Paid service with ID {$data['id']} not found"]);
            }
        }

        $paidService->delete();

        return $responseBuilder->setMessage("Paid service with ID {$data['id']} was deleted successfully")->build();
    }

}
