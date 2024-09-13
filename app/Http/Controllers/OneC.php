<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCounterData;
use App\Jobs\ProcessCustomerData;
use App\Models\Apartment;
use App\Models\Client;
use App\Models\CounterData;
use App\Models\CounterHistory;
use App\Models\House;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use \App\Enums\CounterType;

class OneC extends Controller
{
    /**
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
