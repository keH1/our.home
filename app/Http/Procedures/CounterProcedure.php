<?php

declare(strict_types=1);

namespace App\Http\Procedures;


use App\Models\CounterData;
use App\Models\CounterHistory;
use App\Services\ApiResponseBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Procedure;


class CounterProcedure extends Procedure
{
    private array $mapArr;
    protected array $nullValueCounters;
    protected array $notFoundCounters;
    /**
     * The name of the procedure that is used for referencing.
     *
     * @var string
     */
    public static string $name = 'counter_procedure';

    /**
     * @var bool
     */
    private bool $fromCRM;

    /**
     * todo лог на $nullValueCounters
     * todo лог на $notFoundCounters
     * @param Request $request
     * @return array
     */
    public function acceptHouseCounters(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $params = collect(json_decode($request->getContent(), true)['params']);
        $countersData = collect($params['counters_data']);
        if (!isset($params['from_crm'])){
            throw new InvalidParams(['message'=>"field 'from_crm' is empty"]);
        }
        $this->fromCRM = $params['from_crm'];
        $counterIDs = $countersData->map(function ($counter) {
            $this->mapArr[$counter['counter_id']] = $counter;
            return $counter['counter_id'];
        })->toArray();
        $toUpdateCounters = CounterData::with('histories')->whereIn('id', $counterIDs)->get();
        if ($toUpdateCounters->isEmpty()) {
            throw new InvalidParams(['message'=>"You are sending empty ids / this ids doe's not exist"]);
        }
        $foundCounters = [];
        foreach ($toUpdateCounters as $counter) {
            $foundCounters[] = $counter->id;
            $this->createCounterHistory($counter);
        }
        $this->notFoundCounters = array_diff($counterIDs, $foundCounters);

        return $responseBuilder->setData([])->setMessage("Counters data was sent successfully")->build();
    }


    /**
     * @param mixed $counter
     * @return CounterHistory
     */
    private function createCounterHistory(mixed $counter): CounterHistory|null
    {
        $canCreate = false;
        $counterHistory = new CounterHistory();
        if ($this->mapArr[$counter->id]['daily_consumption'] != null) {
            $counterHistory->daily_consumption = $this->mapArr[$counter->id]['daily_consumption'];
            $canCreate = true;
        }
        if ($this->mapArr[$counter->id]['night_consumption'] != null) {
            $counterHistory->night_consumption = $this->mapArr[$counter->id]['night_consumption'];
            $canCreate = true;
        }
        if ($this->mapArr[$counter->id]['peak_consumption'] != null) {
            $counterHistory->peak_consumption = $this->mapArr[$counter->id]['peak_consumption'];
            $canCreate = true;
        }
        if ($canCreate) {
            $counterHistory->approved = $this->fromCRM;
            $counterHistory->from_1c = false;
            $counterHistory->last_checked_date = Carbon::now();
            $counter->histories()->save($counterHistory);
            return $counterHistory;
        }
        $this->nullValueCounters[] = $counter->id;
        return null;
    }
}
