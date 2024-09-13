<?php

namespace App\Jobs;

use App\Models\Apartment;
use App\Models\CounterData;
use App\Models\CounterHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;


class ProcessCounterData implements ShouldQueue
{
    use Queueable;
    public array $counterTypeMap = [
        'Холодное водоснабжение' => 'COLD_WATER',
        'Горячее водоснабжение' => 'WARM_WATER',
    ];

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $counter)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $counter = $this->counter;
        if (($counterData = $this->checkCounterExistence($counter)) !== null) {

        } else {
            $counterData = new CounterData();
        }
        if (($apartment = $this->findApartmentByAccountID($counter['ИдентификаторЛС'])) !== null) {
            $this->setCounterParams($counterData, $counter, $apartment->id);
            $counterData->save();
            $this->createCounterHistory($counterData, $counter);
        }
    }


    /**
     * @param mixed $counter
     * @return CounterData|null
     */
    private function checkCounterExistence(mixed $counter): CounterData|null
    {
        return CounterData::where([
            ['account_id', '=', $counter['ИдентификаторЛС']],
            ['number', '=', $counter['Идентификатор']],
            ['counter_type', '=', constant('\App\Enums\CounterType::' . $this->counterTypeMap[$counter['ВидУслуги']])],
        ])->first();
    }

    /**
     * @param $accountID
     * @return Apartment|null
     */
    public function findApartmentByAccountID($accountID): Apartment|null
    {
        return Apartment::where('account_id', $accountID)->first();
    }

    /**
     * @param CounterData $counterData
     * @param $counter
     * @param $apartmentID
     * @return void
     */
    private function setCounterParams(CounterData $counterData, $counter, $apartmentID): void
    {
        $counterData->account_id = $counter['ИдентификаторЛС'];
        $counterData->name = 'test';
        $counterData->apartment_id = $apartmentID;
        $counterData->number = $counter['Идентификатор'];
        $counterData->shutdown_reason = $counter['ПричинаОтключения'];
        $counterData->counter_seal = $counter['НомерПломбы'];
        $counterData->created_at = $counter['ДатаНачала'];
        $counterData->verification_to = $counter['ДатаПоверки'];
        $counterData->counter_type = constant('\App\Enums\CounterType::' . $this->counterTypeMap[$counter['ВидУслуги']]);
        $counterData->factory_number = $counter['ЗаводскойНомер'];
        $counterData->calibration_interval = $counter['МежпроверочныйИнтервал'];
        $counterData->commissioning_date = $counter['ДатаВводаВЭксплуатацию'];
        $counterData->first_calibration_date = $counter['ДатаПервойПоверки'];
    }

    /**
     * @param CounterData $counterData
     * @param $counter
     * @return void
     */
    private function createCounterHistory(CounterData $counterData, $counter): void
    {
        $counterHistory = new CounterHistory();
        $counterHistory->counter_name_id = $counterData->id;
        $counterHistory->daily_consumption = $counter['ДневноеПоказание'];
        $counterHistory->night_consumption = $counter['НочноеПоказание'];
        $counterHistory->peak_consumption = $counter['ПиковоеПоказание'];
        $counterHistory->from_1c = true;
        $counterHistory->last_checked_date = Carbon::createFromFormat('d.m.Y H:i:s', $counter['ДатаПоказаний']);
        $counterHistory->save();
    }

}
