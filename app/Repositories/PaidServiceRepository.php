<?php

namespace App\Repositories;


use App\Enums\PriceType;
use App\Models\PaidService;
use App\Models\PaidServiceCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Sajya\Server\Exceptions\InvalidParams;

class PaidServiceRepository
{
    /**
     * @param Collection $data
     * @param PaidService|null $service
     * @return PaidService|false|null
     */
    public function createPaidServiceObj(Collection $data, PaidService $service = null): false|PaidService|null
    {
        $paidService = new PaidService();
        Validator::make($data, [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'category_id' => 'required',
        ])->validate();
        $paidService->name = $data['name'];
        $paidService->description = $data['description'];
        $paidService->price = $data['price'];
        if (PaidServiceCategory::query()->find($data['category_id']) == null) {
            throw new InvalidParams(['message' => 'Category id was not found']);
        }
        $paidService->category_id = $data['category_id'];

        if ($this->checkPriceType($data['price_type'])) {
            $paidService->price_type = PriceType::{
                strtoupper($data['price_type'])}?->value;
        }
        return $paidService;
    }

    /**
     * @param Collection $data
     * @param PaidService|null $service
     * @return PaidService|false|null
     */
    public function updatePaidService(Collection $data, PaidService $service): false|PaidService|null
    {
        $paidService = $service;
        if ($data['name'] !== '') {
            $paidService->name = $data['name'];
        }
        if ($data['description'] !== '') {
            $paidService->description = $data['description'];
        }
        if ($data['price'] !== '') {
            $paidService->price = $data['price'];
        }
        if ($data['category_id'] !== '') {
            if (PaidServiceCategory::query()->find($data['category_id']) == null) {
                throw new InvalidParams(['message' => 'Category id was not found']);
            }
            $paidService->category_id = $data['category_id'];
        }
        if ($data['price_type'] !== '') {
            if ($this->checkPriceType($data['price_type'])) {
                $paidService->price_type = PriceType::{strtoupper($data['price_type'])}?->value;
            }
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

    /**
     * @param string $categoryName
     * @return PaidServiceCategory
     */
    public function createPaidCategory(string $categoryName): PaidServiceCategory
    {
        $paidServiceCategory = new PaidServiceCategory();
        $paidServiceCategory->name = $categoryName;
        $paidServiceCategory->save();

        return $paidServiceCategory;
    }
}
