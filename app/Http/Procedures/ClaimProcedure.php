<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Contracts\ProcedurePermissionsInterface;
use App\Enums\ClaimPriority;
use App\Enums\ClaimStatus;
use App\Enums\ClaimType;
use App\Enums\Permissions;
use App\Models\Claim;
use App\Repositories\ClaimRepository;
use App\Repositories\FileRepository;
use App\Services\ApiResponseBuilder;
use App\Services\PaginationBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Procedure;
use Illuminate\Support\Facades\Validator;


class ClaimProcedure extends Procedure implements ProcedurePermissionsInterface
{
    public static string $name = 'claim';

    public function getMethodsPermissions(): array
    {
        return [
            'createClaim' => [Permissions::NORMAL],
            'updateClaim' => [Permissions::NORMAL],
            'getClaims' => [Permissions::NORMAL],
        ];
    }

    public function createClaim(
        Request $request,
        ApiResponseBuilder $responseBuilder,
        FileRepository $fileRepository,
        ClaimRepository $claimRepository
    ): array {
        $data = collect(json_decode($request->getContent(), true)['params']);

        $claimTypes = array_column(ClaimType::cases(), 'value');
        $claimStatuses = array_column(ClaimStatus::cases(), 'value');
        $claimPriorities = array_column(ClaimPriority::cases(), 'value');

        $validator = Validator::make($data->toArray(), [
            'type' => ['required', Rule::in($claimTypes)],
            'title' => 'required|string',
            'text' => 'nullable|string',
            'client_id' => 'required|integer|exists:clients,id',
            'status' => ['required', Rule::in($claimStatuses)],
            'worker' => 'nullable|array',
            'worker.id' => 'nullable|integer|exists:workers,id',
            'priority' => ['required', Rule::in($claimPriorities)],
            'files' => 'nullable|array',
            'files.*.file' => 'required_with:files|regex:/^data:([a-zA-Z0-9\/\-\+]+);base64,/',
            'files.*.original_file_name' => 'required_with:files|min:4',
        ]);

        $type = ClaimType::from($data['type']);
        $validator->addRules($type->getValidationRules());
        $validator->validate();

        $files = $data->get('files', []);
        $fileObjArr = [];
        $fileRepository->setUploadSubDir('claims/');

        foreach ($files as $file) {
            Validator::make($file, [
                'file' => 'required|regex:/^data:([a-zA-Z0-9\/\-\+]+);base64,/',
                'original_file_name' => 'required|min:4',
            ])->validate();

            $baseData = $file['file'];
            $fileName = $file['original_file_name'];
            $fileObjArr[] = $fileRepository->uploadFileToStorage($fileName, $baseData);
        }

        $claim = $claimRepository->createClaim($data, $fileObjArr);

        return $responseBuilder->setData(['claim' => $claim->toArray()])->setMessage("Заявка успешно создана")->build();
    }

    public function updateClaim(
        Request $request,
        ApiResponseBuilder $responseBuilder,
        ClaimRepository $claimRepository
    ): array {
        $data = collect(json_decode($request->getContent(), true)['params']);

        $claimStatuses = array_column(ClaimStatus::cases(), 'value');
        $claimPriorities = array_column(ClaimPriority::cases(), 'value');

        $validator = Validator::make($data->toArray(), [
            'id' => 'required|integer|exists:claims,id',
            'title' => 'sometimes|string',
            'text' => 'sometimes|string',
            'status' => ['sometimes', Rule::in($claimStatuses)],
            'worker' => 'nullable|array',
            'worker.id' => 'nullable|integer|exists:workers,id',
            'priority' => ['sometimes', Rule::in($claimPriorities)],
        ]);

        $claim = Claim::find($data['id']);
        if (!$claim) {
            throw new InvalidParams(['message' => 'Заявка не найдена']);
        }

        $typeSpecificRules = $claim->type->getValidationRules(false);
        $validator->addRules($typeSpecificRules);
        $validator->validate();

        $updatedClaim = $claimRepository->updateClaim($claim, $data);

        return $responseBuilder->setData(['claim' => $updatedClaim->toArray()])
                               ->setMessage("Заявка успешно обновлена")
                               ->build();
    }

    public function getClaims(
        Request $request,
        ApiResponseBuilder $responseBuilder
    ): array {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $paginationBuilder = PaginationBuilder::fromRequest($request);

        $claimTypes = array_column(ClaimType::cases(), 'value');
        $claimStatuses = array_column(ClaimStatus::cases(), 'value');
        $claimPriorities = array_column(ClaimPriority::cases(), 'value');

        $validator = Validator::make($data->toArray(), [
            'client_id' => 'nullable|array',
            'client_id.*' => 'integer|exists:clients,id',
            'search_text' => 'nullable|string',
            'type' => ['nullable', Rule::in($claimTypes)],
            'author_id' => 'nullable|integer|exists:users,id',
            'status' => ['nullable', Rule::in($claimStatuses)],
            'worker_id' => 'nullable|integer|exists:workers,id',
            'priority' => ['nullable', Rule::in($claimPriorities)],
            'category_id' => 'nullable|integer|exists:claim_categories,id',
            'service_id' => 'nullable|integer|exists:paid_services,id',
            'is_paid' => 'nullable|boolean',
            'expectation_date' => 'nullable|date',
            'sort_by' => 'nullable|array',
            'sort_by.*.field' => 'required|string',
            'sort_by.*.sort_type' => 'required|string|in:asc,desc',
        ]);
        $validator->validate();

        $query = Claim::query();
        $query->leftJoin('client_apartment', 'claims.client_id', '=', 'client_apartment.client_id')
              ->leftJoin('apartments', 'client_apartment.apartment_id', '=', 'apartments.id')
              ->leftJoin('houses', 'apartments.house_id', '=', 'houses.id');
        $query->with(['files']);
        $query->select(
            'claims.*',
            'apartments.id as apartment_id',
            'houses.id as house_id',
            'houses.street'
        );

        if ($data->has('client_id')) {
            $query->whereIn('client_id', $data['client_id']);
        }

        if ($data->has('type')) {
            $query->where('type', $data['type']);
        }

        if ($data->has('author_id')) {
            $authorId = $data['author_id'];
            $query->whereHas('client', function ($q) use ($authorId) {
                $q->where('user_id', $authorId);
            });
        }

        if ($data->has('status')) {
            $query->where('status', $data['status']);
        }

        if ($data->has('worker_id')) {
            $query->where('worker_id', $data['worker_id']);
        }

        if ($data->has('priority')) {
            $query->where('priority', $data['priority']);
        }

        if ($data->has('category_id')) {
            $query->where('category_id', $data['category_id']);
        }

        if ($data->has('service_id')) {
            $query->where('paid_service_id', $data['service_id']);
        }

        if ($data->has('is_paid')) {
            $query->where('is_paid', $data['is_paid']);
        }

        if ($data->has('expectation_date')) {
            $query->whereDate('expectation_date', $data['expectation_date']);
        }

        if ($data->has('search_text') && !empty($data['search_text'])) {
            $searchText = $data['search_text'];

            $searchResults = Claim::search($searchText)->keys();
            $query->where(function ($query) use ($searchText, $searchResults) {
                if ($searchResults->isNotEmpty()) {
                    $query->whereIn('claims.id', $searchResults);
                }
                $query->orWhere('claims.id', 'LIKE', "%{$searchText}%");
                $query->orWhereHas('client', function ($q) use ($searchText) {
                    $q->where('name', 'LIKE', "%{$searchText}%");
                });
            });
        }

        if ($data->has('sort_by') && is_array($data['sort_by'])) {
            foreach ($data['sort_by'] as $sort) {
                $field = $sort['field'];
                $sortType = $sort['sort_type'];
                $query->orderBy($field, $sortType);
            }
        }

        $totalQuery = clone $query;
        if ($paginationBuilder->isPaginationEnabled()) {
            $limit = $paginationBuilder->getLimit();
            $offset = $paginationBuilder->getOffset() ?? 0;

            $total = $totalQuery->count();
            $paginationBuilder->setTotal($total);

            $query->limit($limit)->offset($offset);
        }

        $claims = $query->get();
        if ($claims->isEmpty()) {
            return $responseBuilder->setData([])->setMessage('Список заявок пуст')->build();
        }

        return $responseBuilder
            ->setData($claims)
            ->setPagination($paginationBuilder->isPaginationEnabled() ? $paginationBuilder : null)
            ->build();
    }
}
