<?php

namespace App\Jobs;

use App\Models\AccountPersonalNumber;
use App\Models\Apartment;
use App\Models\House;
use App\Repositories\ApartmentRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;


class ProcessApartmentData implements ShouldQueue
{
    use Queueable;

    public ApartmentRepository $apartmentRepository;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $apartmentData)
    {
        $this->apartmentRepository = new ApartmentRepository();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $apartmentData = $this->apartmentData;
//        $channel = Log::build([
//            'driver' => 'single',
//            'path' => storage_path('logs/1c_customer.log'),
//        ]);
//        Log::stack(['slack', $channel])->info(json_encode($customer));
        if (!$this->apartmentRepository->checkByOneCID($apartmentData)) {
            $apartment = new Apartment();
            $this->setApartmentFields($apartment,$apartmentData);
            $this->attachApartmentToHouse($apartment,$apartmentData);
            $apartment->save();
        }
    }


    public function setApartmentFields(&$apartment, $apartmentData): void
    {
        $apartment->number = $apartmentData['number'];
        $apartment->one_c_id = $apartmentData['id'];
        $apartment->address = $apartmentData['address'] ?? null;
    }

    /**
     * @param Apartment $apartment
     * @param mixed $apartmentData
     * @return void
     */
    public function attachApartmentToHouse(Apartment $apartment, mixed $apartmentData): void
    {
        if($house = House::where('one_c_id', $apartmentData['house_id'])->first()) {
            $house->apartments()->save($apartment);
        }
    }
}
