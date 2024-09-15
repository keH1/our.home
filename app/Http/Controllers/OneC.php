<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCounterData;
use App\Jobs\ProcessCustomerData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class OneC extends Controller
{
    /**
     * todo пользователь может быть помечен на удаление
     * @param Request $request
     * @return JsonResponse
     */
    public function customers(Request $request)
    {
        $customers = $request->json()->all();
        foreach ($customers as $customer) {
            ProcessCustomerData::dispatch($customer);
        }
        return response()->json(['message' => 'Customers queued for processing']);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function counters(Request $request)
    {
        $counters = $request->json()->all();
        foreach ($counters as $counter) {
             ProcessCounterData::dispatch($counter);
        }
    }
}
