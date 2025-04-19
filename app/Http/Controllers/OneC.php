<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessApartmentData;
use App\Jobs\ProcessCounterData;
use App\Jobs\ProcessCustomerNumberData;
use App\Jobs\ProcessHouseData;
use App\Models\Apartment;
use App\Models\House;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class OneC extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function houses(Request $request)
    {
        $houses = $request->json()->all();
        foreach ($houses as $houseData) {
            ProcessHouseData::dispatch($houseData);
        }
        return response()->json(['message' => 'Houses queued for processing']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function apartments(Request $request)
    {
        $apartments = $request->json()->all();
        foreach ($apartments as $apartmentData) {
            ProcessApartmentData::dispatch($apartmentData);
        }
        return response()->json(['message' => 'Apartments queued for processing']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function customerNumbers(Request $request)
    {
        $customerNumbers = $request->json()->all();

        foreach ($customerNumbers as $customerNumber) {
            ProcessCustomerNumberData::dispatch($customerNumber);
        }
        return response()->json(['message' => 'Customer numbers queued for processing']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function counters(Request $request)
    {
        $counters = $request->json()->all();
        foreach ($counters as $counter) {
            ProcessCounterData::dispatch($counter);
        }
        return response()->json(['message' => 'Counters queued for processing']);
    }
}
