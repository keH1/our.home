<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Contracts\ProcedurePermissionsInterface;
use App\Enums\Permissions;
use App\Models\Apartment;
use App\Models\House;
use App\Repositories\ApartmentRepository;
use App\Services\ApiResponseBuilder;
use App\Services\PaginationBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Procedure;

class HouseProcedure extends Procedure implements ProcedurePermissionsInterface
{
    public static string $name = 'house_procedure';

    public function getMethodsPermissions(): array
    {
        return [
            'getApartmentDataById' => [Permissions::NORMAL],
            'getApartmentsWithCounters' => [Permissions::NORMAL],
            'getHousesByStreet' => [Permissions::NORMAL],
            'getAllStreets' => [Permissions::NORMAL],
        ];
    }

    /**
     * Метод для получения всех улиц.
     *
     * @param  Request  $request
     * @param  \App\Services\ApiResponseBuilder  $responseBuilder
     * @return array
     */
    public function getAllStreets(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $paginationBuilder = PaginationBuilder::fromRequest($request);
        $query = House::select('street')->distinct()->orderBy('street');

        if ($paginationBuilder->isPaginationEnabled()) {
            $limit = $paginationBuilder->getLimit();
            $offset = $paginationBuilder->getOffset() ?? 0;

            $paginationBuilder->setTotal((clone $query)->count());
            $query->limit($limit)->offset($offset);
        }

        $streets = $query->pluck('street')->toArray();

        if (empty($streets)) {
            return $responseBuilder->setData([])->setMessage('Список улиц пуст')->build();
        }

        if ($paginationBuilder->isPaginationEnabled()) {
            return $responseBuilder->setData($streets)
                                   ->setPagination($paginationBuilder)
                                   ->build();
        }

        return $responseBuilder->setData($streets)->build();
    }

    /**
     * Метод для получения домов по подстроке названия улицы.
     *
     * @param  Request  $request
     * @param  ApiResponseBuilder  $responseBuilder
     * @return array
     */
    public function getHousesByStreet(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $validator = Validator::make($request->all(), [
            'street' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidParams($validator->errors()->toArray());
        }

        $street = $validator->getValue('street');

        $paginationBuilder = PaginationBuilder::fromRequest($request);
        $query = House::where('street', 'ILIKE', '%'.$street.'%')->orderBy('number');

        if ($paginationBuilder->isPaginationEnabled()) {
            $limit = $paginationBuilder->getLimit();
            $offset = $paginationBuilder->getOffset() ?? 0;

            $paginationBuilder->setTotal((clone $query)->count());
            $houses = $query->limit($limit)->offset($offset)->get();

            if ($houses->isEmpty()) {
                return $responseBuilder->setData([])->setMessage('Дома по указанной улице не найдены')->build();
            }

            return $responseBuilder->setData($houses)->setPagination($paginationBuilder)->build();
        }

        $houses = $query->get();
        if ($houses->isEmpty()) {
            return $responseBuilder->setData([])->setMessage('Дома по указанной улице не найдены')->build();
        }

        return $responseBuilder->setData($houses)->build();
    }

    /**
     * Метод для получения квартир и счетчиков по идентификатору дома.
     *
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     * @throws ValidationException
     */
    public function getApartmentsWithCounters(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $validator = Validator::make($request->all(), [
            'house_id' => 'nullable|integer|exists:houses,id',
            'apartment_id' => 'nullable|integer|exists:apartments,id',
        ]);

        if ($validator->fails()) {
            throw new InvalidParams($validator->errors()->toArray());
        }

        $validated = $validator->validated();
        $paginationBuilder = PaginationBuilder::fromRequest($request);

        $query = Apartment::with([
            'counterData' => function ($query) {
                $query->with([
                    'latestConfirmedHistory', 'latestUnconfirmedHistory',
                ]);
            }
        ])->orderBy('number');

        if (isset($validated['house_id'])) {
            $query->where('house_id', $validated['house_id']);
        }

        if (isset($validated['apartment_id'])) {
            $query->where('id', $validated['apartment_id']);
        }

        if ($paginationBuilder->isPaginationEnabled()) {
            $limit = $paginationBuilder->getLimit();
            $offset = $paginationBuilder->getOffset() ?? 0;

            $paginationBuilder->setTotal((clone $query)->count());

            $apartments = $query->limit($limit)->offset($offset)->get();
            if ($apartments->isEmpty()) {
                return $responseBuilder->setData([])->setMessage('Квартиры не найдены')->build();
            }

            return $responseBuilder->setData($this->buildApartments($apartments))->setPagination($paginationBuilder)->build();
        }

        $apartments = $query->get();
        if ($apartments->isEmpty()) {
            return $responseBuilder->setData([])->setMessage('Квартиры не найдены')->build();
        }

        return $responseBuilder->setData($this->buildApartments($apartments))->build();
    }

    /**
     * Метод для получения информации о апартаментах по их идентификатору.
     *
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @param ApartmentRepository $apartmentRepository
     * @return array
     */
    public function getApartmentDataById(Request $request, ApiResponseBuilder $responseBuilder, ApartmentRepository $apartmentRepository): array
    {
        $params = collect(json_decode($request->getContent(), true)['params']);
        $apartment = $apartmentRepository->findApartmentById($params['id']);

        if (!$apartment) {
            return $responseBuilder->setMessage('Apartment not found.')->build();
        }

        $response = [
            'id'=>$apartment->id,
            'number'=>$apartment->number,
            'house'=>[
                'id' => $apartment->house->id,
                'street' => $apartment->house->street,
                'number' => $apartment->house->number,
                'building' => $apartment->house->building ?? null,
                'city' => $apartment->house->city,
            ],
            'clients'=>$apartment->clients?->map(function ($client){return [
                'id'=>$client->id,
                'user_id'=>$client->user_id,
                'accounts'=>$client->accounts?->map(function ($account){
                    return [
                        'id' =>$account->id,
                        'number' =>$account->number,
                    ];
                })->toArray()
            ];})->toArray()
        ];

        return $responseBuilder->setData($response)->build();
    }


    private function buildApartments($apartments)
    {
        return $apartments->map(function ($apartment) {
            return [
                'id' => $apartment->id,
                'number' => $apartment->number,
                'counters' => $apartment->counterData->map(function ($counter) {
                    return [
                        'counter_id' => $counter->id,
                        'counter_number' => $counter->number,
                        'counter_type' => $counter->counter_type,
                        'verification_to' => $counter->verification_to,
                        'apartment_id' => $counter->apartment_id,
                        'personal_number' => $counter->personal_number,
                        'confirmed_history' => $counter->latestConfirmedHistory ? [
                            'daily_consumption' => $counter->latestConfirmedHistory->daily_consumption,
                            'night_consumption' => $counter->latestConfirmedHistory->night_consumption,
                            'peak_consumption' => $counter->latestConfirmedHistory->peak_consumption,
                            'last_checked_date' => $counter->latestConfirmedHistory->last_checked_date,
                        ] : null,
                        'unconfirmed_history' => $counter->latestUnconfirmedHistory ? [
                            'daily_consumption' => $counter->latestUnconfirmedHistory->daily_consumption,
                            'night_consumption' => $counter->latestUnconfirmedHistory->night_consumption,
                            'peak_consumption' => $counter->latestUnconfirmedHistory->peak_consumption,
                            'last_checked_date' => $counter->latestUnconfirmedHistory->last_checked_date,
                        ] : null,
                    ];
                }),
            ];
        });
    }
}
