<?php

namespace App\Jobs;

use App\Models\AccountPersonalNumber;
use App\Models\Apartment;
use App\Models\CounterData;
use App\Models\CounterHistory;
use App\Repositories\AccountRepository;
use App\Repositories\ApartmentRepository;
use App\Repositories\CounterRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;


class ProcessCounterData implements ShouldQueue
{
    use Queueable;

    public CounterRepository $counterRepository;
    private AccountRepository $accountRepository;
    private ApartmentRepository $apartmentRepository;

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
//        $channel = Log::build([
//            'driver' => 'single',
//            'path' => storage_path('logs/1c_counter.log'),
//        ]);
//        Log::stack(['slack', $channel])->info(json_encode($counter));
        $counter['counter_type'] = trim($counter['counter_type']);
        if (!$this->counterRepository->checkCounterType($counter['counter_type'])) {
            throw new \Exception('Данный вид счетчика не найден');
        }
        if (!($counterData = $this->checkCounterExistence($counter))) {
            $counterData = new CounterData();
        }
        if(isset($counterData->created_at) && $counterData->created_at < $counter['date_check']) {
             throw new \Exception('Старый счетчик № '.$counter['factory_number']);
        }
        $this->setCounterParams($counterData, $counter);
        $counterData->save();
        if($account = AccountPersonalNumber::where('number', $counter['account_id'])->first()) {
            $this->attachCounterToAccount($counterData,$account);
        }
    }


    /**
     * @param array $counter
     * @return CounterData|null
     */
    public function checkCounterExistence(array $counter): CounterData|null
    {
        return CounterData::where([
            ['one_c_id', '=', $counter['counter_id_1c']],
            ['counter_type', '=', $counter['counter_type']],
        ])->first();
    }

    /**
     * @param CounterData $counterData
     * @param $counter
     * @param $apartmentID
     * @return void
     */
    public function setCounterParams(CounterData &$counterData, $counter): void
    {
        $counterData->account_one_c_id = $counter['account_id_1c'];
        $counterData->counter_seal = $counter['seal_number'] ?? null;
        $counterData->created_at = null;
        $counterData->verification_to = null;
        $counterData->first_calibration_date = null;
        if (isset($counter['start_date']) && strlen($counter['start_date']) > 0) {
            $counterData->created_at = Carbon::createFromFormat('d.m.Y H:i:s', $counter['start_date']);
        }
        if (isset($counter['date_check']) && strlen($counter['date_check']) > 0) {
            $counterData->verification_to = Carbon::createFromFormat('d.m.Y H:i:s', $counter['date_check']);
        }
        if (isset($counter['first_date_check']) && strlen($counter['first_date_check']) > 0) {
            $counterData->first_calibration_date = Carbon::createFromFormat('d.m.Y H:i:s', $counter['first_date_check']);
        }
        $counterData->counter_type = $counter['counter_type'];
        $counterData->factory_number = $counter['factory_number'] ?? null;
        $counterData->gis_id = $counter['counter_gis_id'] ?? null;
        $counterData->els_id = $counter['els_id'] ?? null;
        $counterData->one_c_id = $counter['counter_id_1c'] ?? null;
        $counterData->is_active = $counter['active'] == 'Да';
    }

    /**
     * @param CounterData $counterData
     * @param $counter
     * @return void
     */
    public function createCounterHistory(CounterData $counterData, $counter): void
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

    /**
     * @param CounterData $counter
     * @param AccountPersonalNumber $account
     * @return void
     */
    public function attachCounterToAccount(CounterData $counter, AccountPersonalNumber $account): void
    {
        $account->counters()->save($counter);
    }
}
