<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Attributes\RpcProcedure;
use App\Contracts\ProcedurePermissionsInterface;
use App\Enums\Permissions;
use App\Models\PaidService;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Procedure;
use \App\Repositories\PaidServiceRepository;

#[RpcProcedure(version: 'v1', group: 'services')]
class PaidServiceProcedure extends Procedure implements ProcedurePermissionsInterface
{
    public static string $name = 'paid_service';

    public function getMethodsPermissions(): array
    {
        return [
            'createPaidService' => [Permissions::NORMAL],
            'updatePaidService' => [Permissions::NORMAL],
            'getPaidServices' => [Permissions::NORMAL],
            'deletePaidService' => [Permissions::NORMAL],
        ];
    }

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
        if ($data['id'] > 0) {
            $paidService = PaidService::find($data['id']);
            if ($paidService !== null) {
                $paidService->delete();
                return $responseBuilder->setMessage("Paid service with ID {$data['id']} was deleted successfully")->build();
            }
            return $responseBuilder->setMessage("Paid service with ID {$data['id']} not found")->build();
        }

        return $responseBuilder->setMessage("Invalid value in 'id' field")->build();
    }

}
