<?php

namespace App\Jobs;

use App\Enums\CounterType;
use App\Models\AccountPersonalNumber;
use App\Models\Apartment;
use App\Models\CounterData;
use App\Models\CounterHistory;
use App\Repositories\ApartmentRepository;
use App\Repositories\CounterRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Repositories\AccountRepository;


class ProcessCounterData implements ShouldQueue
{
    use Queueable;

    private AccountRepository $accountRepository;
    private ApartmentRepository $apartmentRepository;
    public CounterRepository $counterRepository;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $counter)
    {
        $this->accountRepository = new AccountRepository();
        $this->apartmentRepository = new ApartmentRepository();
        $this->counterRepository = new CounterRepository();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $counter = $this->counter;
        $counter['ВидУслуги'] = trim($counter['ВидУслуги']);
        if (!$this->counterRepository->checkCounterType($counter['ВидУслуги'])) {
            throw new \Exception('Данный вид счетчика не найден');
        }
        if (($counterData = $this->checkCounterExistence($counter)) !== null) {

        } else {
            $counterData = new CounterData();
        }
        $this->setCounterParams($counterData, $counter);
        $counterData->save();
        $this->createCounterHistory($counterData, $counter);
        if (($account = $this->accountRepository->checkAccountByNumber($counter['ИдентификаторЛС'])) !== null) {
            $this->attachCounterToAccount($counterData, $account);
            if (($apartment = $this->apartmentRepository->findApartmentByAccountID($account->id)) !== null) {
                $this->attachCounterToApartment($counterData, $apartment);
            }
        }
    }


    /**
     * @param array $counter
     * @return CounterData|null
     */
    private function checkCounterExistence(array $counter): CounterData|null
    {
        return CounterData::where([
            ['number', '=', $counter['Идентификатор']],
            ['counter_type', '=', $counter['ВидУслуги']],
        ])->first();
    }

    /**
     * @param CounterData $counterData
     * @param $counter
     * @param $apartmentID
     * @return void
     */
    private function setCounterParams(CounterData &$counterData, $counter): void
    {
        $counterData->number = $counter['Идентификатор'];
        $counterData->shutdown_reason = $counter['ПричинаОтключения'];
        $counterData->counter_seal = $counter['НомерПломбы'];
        $counterData->created_at = Carbon::createFromFormat('d.m.Y H:i:s', $counter['ДатаНачала']);
        $counterData->verification_to = Carbon::createFromFormat('d.m.Y H:i:s', $counter['ДатаПоверки']);
        $counterData->counter_type = $counter['ВидУслуги'];
        $counterData->factory_number = $counter['ЗаводскойНомер'];
        $counterData->calibration_interval = $counter['МежпроверочныйИнтервал'];
        $counterData->commissioning_date = Carbon::createFromFormat('d.m.Y H:i:s', $counter['ДатаВводаВЭксплуатацию']);
        $counterData->first_calibration_date = Carbon::createFromFormat('d.m.Y H:i:s', $counter['ДатаПервойПоверки']);
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
        $counterHistory->daily_consumption = $this->parseFloat($counter['ДневноеПоказание']);
        $counterHistory->night_consumption = $this->parseFloat($counter['НочноеПоказание']);
        $counterHistory->peak_consumption = $this->parseFloat($counter['ПиковоеПоказание']);
        $counterHistory->from_1c = true;
        try {
            $counterHistory->last_checked_date = Carbon::createFromFormat('d.m.Y H:i:s', $counter['ДатаПоказаний']);
        } catch (\Exception $exception) {
            $counterHistory->last_checked_date = null;
        }

        $counterHistory->save();
    }

    /**
     * @param CounterData $counter
     * @param AccountPersonalNumber $account
     * @return void
     */
    private function attachCounterToAccount(CounterData $counter, AccountPersonalNumber $account): void
    {
        $account->counters()->save($counter);
    }

    /**
     * @param CounterData $counterData
     * @param Apartment $apartment
     * @return void
     */
    private function attachCounterToApartment(CounterData $counterData, Apartment $apartment): void
    {
        $apartment->counterData()->save($counterData);
    }

    /**
     * @param $string
     * @return array|string|string[]
     */
    public function parseFloat($string)
    {
        if ($string == '') {
            return null;
        }
        $string = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', ($string));
        return str_replace(',', '.', $string);
    }
}
