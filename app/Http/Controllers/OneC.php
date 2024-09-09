<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCustomerData;
use Illuminate\Http\Request;

class OneC extends Controller
{
    public function customers(Request $request)
    {
        $customers = $request->json()->all();
        foreach ($customers as $customer) {
            ProcessCustomerData::dispatch($customer);
        }

        return response()->json(['message' => 'Customers queued for processing']);
    }

    public function counters()
    {

    }
}
