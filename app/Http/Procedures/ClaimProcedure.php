<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\File;
use App\Models\PaidService;
use App\Repositories\FileRepository;
use App\Services\ApiResponseBuilder;
use Bitrix\Mobile\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sajya\Server\Procedure;


class ClaimProcedure extends Procedure
{
    public static string $name = 'claim';
    public string $subDirName = 'claims/';

    public array $errors;

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function createPaidClaim(Request $request, ApiResponseBuilder $responseBuilder, FileRepository $fileRepository): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $files = $data['files'];
        $claim = $this->createClaimObj($data);
        foreach ($files as $file) {
            $baseData = $file['file'];
            $fileName = $file['original_file_name'];
            $fileObj = $fileRepository->uploadFileToStorage($this->subDirName, $fileName, $baseData);
            if(is_string($fileObj)){
                return $responseBuilder->setData(['errors' => $fileObj])->build();
            }
            $claim->files()->save($fileObj);
        }
        if (!empty($this->errors)) {
            return $responseBuilder->setData(['errors' => $this->errors])->build();
        }

        return $responseBuilder->setData(['paid_service' => $claim->toArray()])->setMessage("Paid service claim was created successfully")->build();
    }

    private function createClaimObj($data)
    {
        $paidServiceID = $data['id'];
        $message = $data['message'];
        $claim = new Claim();
        if (PaidService::query()->find($paidServiceID) == null) {
            $this->errors = ['message' => 'paid service id not found'];
            return false;
        }
        $claim->paid_service_id = $paidServiceID;
        $claim->comment = $message;
        $claim->category_id = null;
        $claim->user_id = auth()->user()->id;
        $claim->type = "paid";
        $claim->save();

        return $claim;
    }
}
