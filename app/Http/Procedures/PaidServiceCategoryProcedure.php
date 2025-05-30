<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Attributes\RpcProcedure;
use App\Contracts\ProcedurePermissionsInterface;
use App\Enums\Permissions;
use App\Repositories\FileRepository;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Sajya\Server\Procedure;
use \App\Repositories\PaidServiceRepository;

#[RpcProcedure(version: 'v1', group: 'services')]
class PaidServiceCategoryProcedure extends Procedure implements ProcedurePermissionsInterface
{
    public static string $name = 'paid_service_category';

    public function getMethodsPermissions(): array
    {
        return [
            'createPaidServiceCategory' => [Permissions::NORMAL],
            'getPaidServiceCategories' => [Permissions::NORMAL],
        ];
    }

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @param FileRepository $fileRepository
     * @return array
     */
    public function createPaidServiceCategory(Request $request, ApiResponseBuilder $responseBuilder, FileRepository $fileRepository, PaidServiceRepository $paidServiceRepository): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);

        $categoryName = $data['name'];
        $paidCategory = $paidServiceRepository->createPaidCategory($categoryName);
        if (strlen($data['image']) > 0) {
            $baseData = $data['image'];
            $fileName = $data['original_file_name'];
            $fileRepository->setUploadSubDir('paid_services/');
            $fileObj = $fileRepository->uploadFileToStorage($fileName, $baseData);
            $paidCategory->file()->save($fileObj);
        }

        return $responseBuilder->setData(['paid_category_id' => $paidCategory->id])->setMessage("Paid service category was created successfully")->build();
    }

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @param PaidServiceRepository $paidServiceRepository
     * @return array
     */
    public function getPaidServiceCategories(Request $request, ApiResponseBuilder $responseBuilder, PaidServiceRepository $paidServiceRepository): array
    {
        $categories = $paidServiceRepository->getAllPaidCategories();

        return $responseBuilder->setData($categories)->setMessage("Paid service categories retrieved successfully")->build();
    }
}
