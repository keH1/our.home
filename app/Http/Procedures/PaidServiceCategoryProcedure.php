<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Models\PaidServiceCategory;
use App\Repositories\FileRepository;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Sajya\Server\Procedure;
use App\Models\File;
use Illuminate\Support\Str;

class PaidServiceCategoryProcedure extends Procedure
{
    public static string $name = 'paid_service_category';
    public string $subDirName = 'paid_services/';

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @param FileRepository $fileRepository
     * @return array
     */
    public function createPaidServiceCategory(Request $request, ApiResponseBuilder $responseBuilder, FileRepository $fileRepository): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $categoryName = $data['name'];
        $paidCategory = $this->createPaidCategory($categoryName);
        $baseData = $data['image'];
        $fileName = $data['original_file_name'];
        $fileObj = $fileRepository->uploadFileToStorage($this->subDirName,$fileName,$baseData);
        if(is_string($fileObj)){
            return $responseBuilder->setData(['errors' => $fileObj])->build();
        }
        $paidCategory->file()->save($fileObj);

        return $responseBuilder->setData(['paid_category_id' => $paidCategory->id])->setMessage("Paid service category was created successfully")->build();
    }

    /**
     * @param $categoryName
     * @return PaidServiceCategory
     */
    private function createPaidCategory($categoryName): PaidServiceCategory
    {
        $paidServiceCategory = new PaidServiceCategory();
        $paidServiceCategory->name = $categoryName;
        $paidServiceCategory->save();

        return $paidServiceCategory;
    }
}
