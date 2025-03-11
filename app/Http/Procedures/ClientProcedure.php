<?php

declare(strict_types=1);

namespace App\Http\Procedures;


use App\Attributes\RpcProcedure;
use App\Contracts\ProcedurePermissionsInterface;
use App\Enums\Permissions;
use App\Models\Client;
use App\Services\ApiResponseBuilder;
use App\Services\PaginationBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Sajya\Server\Procedure;

#[RpcProcedure(version: 'v1', group: 'clients')]
class ClientProcedure extends Procedure implements ProcedurePermissionsInterface
{
    /**
     * The name of the procedure that is used for referencing.
     *
     * @var string
     */
    public static string $name = 'client_procedure';

    public function getMethodsPermissions(): array
    {
        return [
            'getClients' => [Permissions::NORMAL],
        ];
    }

    public function getClients(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params'] ?? []);
        $paginationBuilder = PaginationBuilder::fromRequest($request);
        $validator = Validator::make($data->toArray(), [
            'id' => 'nullable|array',
            'id.*' => 'integer|exists:clients,id',
            'search_text' => 'nullable|string',
            'street' => 'nullable|string',
            'house_id' => 'nullable|integer|exists:houses,id',
            'apartment_id' => 'nullable|integer|exists:apartments,id',
        ]);
        $validator->validate();

        $query = Client::query();
        if ($data->has('search_text') && !empty($data['search_text'])) {
            $searchText = $data['search_text'];

            $searchResults = Client::search($searchText)->keys();
            if ($searchResults->isEmpty()) {
                $paginationBuilder->setTotal(0);
                return $responseBuilder
                    ->setData([])
                    ->setPagination($paginationBuilder)
                    ->setMessage('По вашему запросу ничего не найдено')
                    ->build();
            }

            $query->whereIn('id', $searchResults);
        }

        if ($data->has('id')) {
            $query->whereIn('id', $data['id']);
        }

        if ($data->has('street')) {
            $street = $data['street'];
            $query->whereHas('apartments.house', function ($q) use ($street) {
                $q->where('street', 'LIKE', '%' . $street . '%');
            });
        }

        if ($data->has('house_id')) {
            $houseId = $data['house_id'];
            $query->whereHas('apartments', function ($q) use ($houseId) {
                $q->where('house_id', $houseId);
            });
        }

        if ($data->has('apartment_id')) {
            $apartmentId = $data['apartment_id'];
            $query->whereHas('apartments', function ($q) use ($apartmentId) {
                $q->where('id', $apartmentId);
            });
        }

        $totalQuery = clone $query;

        if ($paginationBuilder->isPaginationEnabled()) {
            $limit = $paginationBuilder->getLimit();
            $offset = $paginationBuilder->getOffset() ?? 0;

            $total = $totalQuery->count();
            $paginationBuilder->setTotal($total);

            $query->limit($limit)->offset($offset);
        }

        $query->with(['user', 'apartments.house']);

        $clients = $query->get();
        if ($clients->isEmpty()) {
            return $responseBuilder->setData([])->setMessage('Список клиентов пуст')->build();
        }

        $clientsData = $clients->map(function ($client) {
            $user = $client->user;
            $apartment = $client->apartments->first();
            $house = $apartment?->house;

            return [
                'id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $user?->email,
                'debt' => $client->debt,

                'street' => $house?->street,
                'house_number' => $house?->number,
                'building_number' => $house?->building,
                'apartment_number' => $apartment?->number,
            ];
        });

        return $responseBuilder
            ->setData($clientsData)
            ->setPagination($paginationBuilder->isPaginationEnabled() ? $paginationBuilder : null)
            ->build();
    }

}
