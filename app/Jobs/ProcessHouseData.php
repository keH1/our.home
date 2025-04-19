<?php

namespace App\Jobs;

use App\Models\House;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;


class ProcessHouseData implements ShouldQueue
{
    use Queueable;


    /**
     * Create a new job instance.
     */
    public function __construct(protected array $houseData)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $houseData = $this->houseData;
//        $channel = Log::build([
//            'driver' => 'single',
//            'path' => storage_path('logs/1c_customer.log'),
//        ]);
//        Log::stack(['slack', $channel])->info(json_encode($customer));
        if (!$this->checkHouseExistence($houseData)) {
            $house = new House();
            $this->fillHouseFields($house, $houseData);
            $house->save();
        }

    }


    /**
     * @param array $customer
     * @return House|null
     */
    public function checkHouseExistence(array $houseData): House|null
    {
        return House::where([
            ['one_c_id', '=', $houseData['id']],
        ])->first();
    }

    /**
     * @param House $house
     * @param mixed $houseData
     * @return void
     */
    public function fillHouseFields(House &$house, mixed $houseData): void
    {
        $house->city = $houseData['city'] ?? null;
        $house->street = $houseData['street'] ?? null;
        $house->number = $houseData['number'] ?? null;
        $house->building = $houseData['building'] ?? null;
        $house->one_c_id = $houseData['id'];
    }
}
