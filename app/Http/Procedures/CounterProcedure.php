<?php

declare(strict_types=1);

namespace App\Http\Procedures;


use App\Models\CounterData;
use App\Models\CounterHistory;
use App\Services\ApiResponseBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sajya\Server\Procedure;


class CounterProcedure extends Procedure
{
    private array $mapArr;
    /**
     * The name of the procedure that is used for referencing.
     *
     * @var string
     */
    public static string $name = 'counter_procedure';

    /**
     * todo лог на $nullValueCounters
     * todo лог на $notFoundCounters
     * @param Request $request
     * @return array
     */
    public function acceptHouseCounters(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $approved = false;
        $params = collect(json_decode($request->getContent(), true)['params']);
        $counterIDs = $params->map(function ($counter) {
            $this->mapArr[$counter['counter_id']] = $counter;
            return $counter['counter_id'];
        })->toArray();
        $toUpdateCounters = CounterData::with('histories')->whereIn('id', $counterIDs)->get();
        if ($toUpdateCounters->count() > 0) {
            $foundCounters = [];
            $nullValueCounters = [];
            foreach ($toUpdateCounters as $counter) {
                $foundCounters[] = $counter->id;
                $counterHistory = new CounterHistory();
                if ($this->mapArr[$counter->id]['daily_consumption'] != null) {
                    $counterHistory->daily_consumption = $this->mapArr[$counter->id]['daily_consumption'];
                    $approved = true;
                }
                if ($this->mapArr[$counter->id]['night_consumption'] != null) {
                    $counterHistory->night_consumption = $this->mapArr[$counter->id]['night_consumption'];
                    $approved = true;
                }
                if ($this->mapArr[$counter->id]['peak_consumption'] != null) {
                    $counterHistory->peak_consumption = $this->mapArr[$counter->id]['peak_consumption'];
                    $approved = true;
                }
                if ($approved) {
                    $counterHistory->from_1c = false;
                    $counterHistory->last_checked_date = Carbon::now();
                    $counterHistory->approved = $approved;
                    $counter->histories()->save($counterHistory);
                }else{
                    $nullValueCounters[] = $counter->id;
                }
            }
            $notFoundCounters = array_diff($counterIDs, $foundCounters);
            return $responseBuilder->setData([])->setMessage("Counters data was sent successfully")->build();
        }
        return $responseBuilder->setData([])->setMessage("You are sending empty ids / this ids doe's not exist")->build();
    }

}
