<?php

declare(strict_types=1);

namespace App\Http\Procedures;
use App\Repositories\ClaimRepository;
use App\Repositories\FileRepository;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Sajya\Server\Procedure;
use Illuminate\Support\Facades\Validator;


class ClaimProcedure extends Procedure
{
    public static string $name = 'claim';

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function createPaidClaim(Request $request, ApiResponseBuilder $responseBuilder, FileRepository $fileRepository, ClaimRepository $claimRepository): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $files = $data['files'];
        $fileObjArr = [];
        $fileRepository->setUploadSubDir('claims/');
        foreach ($files as $file) {
           Validator::make($file, [
                'file' => 'required|regex:(base64,)',
                'original_file_name' => 'required|min:4',
            ])->validate();
            $baseData = $file['file'];
            $fileName = $file['original_file_name'];
            $fileObjArr[] = $fileRepository->uploadFileToStorage($fileName, $baseData);
        }
        $claim = $claimRepository->createClaim($data, $fileObjArr);

        return $responseBuilder->setData(['paid_service' => $claim->toArray()])->setMessage("Paid service claim was created successfully")->build();
    }
}
