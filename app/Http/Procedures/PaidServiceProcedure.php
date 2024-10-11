<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Models\PaidService;
use App\Models\PaidServiceCategory;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Sajya\Server\Procedure;
use App\Enums\PriceType;


class PaidServiceProcedure extends Procedure
{
    public static string $name = 'paid_service';
    public string $subDirName = 'paid_services/';

    public array $errors;

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function createPaidService(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $paidService = $this->createPaidServiceObj($data);

        if (!empty($this->errors)) {
            return $responseBuilder->setData(['errors' => $this->errors])->build();
        }
        return $responseBuilder->setData(['paid_service' => $paidService->toArray()])->setMessage("Paid service was created successfully")->build();
    }

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function updatePaidService(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $paidService = PaidService::find($data['id']);
        if (!$this->createPaidServiceObj($data, $paidService)) {
            return $responseBuilder->setData([])->setMessage("Category id was not found")->build();
        }

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
     * @param $data
     * @param PaidService|null $service
     * @return PaidService|false|null
     */
    private function createPaidServiceObj($data, PaidService $service = null)
    {
        $paidService = $service;
        if ($service == null) {
            $paidService = new PaidService();
        }
        $data['name'] == '' ?: $paidService->name = $data['name'];
        $data['description'] == '' ?: $paidService->description = $data['description'];
        $data['price'] == '' ?: $paidService->price = $data['price'];
        if (PaidServiceCategory::query()->find($data['category_id']) == null) {
            $this->errors = ['message' => 'Category id was not found'];
            return false;
        }
        $data['category_id'] == '' ?: $paidService->category_id = $data['category_id'];

        if ($this->checkPriceType($data['price_type'])) {
            $paidService->price_type = PriceType::{
                strtoupper($data['price_type'])}?->value;
        }
        $paidService->save();

        return $paidService;
    }


    /**
     * @param string $type
     * @return bool
     */
    public function checkPriceType(string $type): bool
    {
        foreach (PriceType::cases() as $case) {
            if (Str::contains($type, $case->value)) {
                return true;
            }
        }
        return false;
    }
}
