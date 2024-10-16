<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Repositories\FileRepository;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Sajya\Server\Procedure;
use \App\Repositories\PaidServiceRepository;

class PaidServiceCategoryProcedure extends Procedure
{
    public static string $name = 'paid_service_category';

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
        $formattedCategories = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'original_file_name' => $category->file ? $category->file->original_name : null,
                'image' => $category->file ? $category->file->path : null,
            ];
        });

        return $responseBuilder->setData($formattedCategories)->setMessage("Paid service categories retrieved successfully")->build();
    }
}
