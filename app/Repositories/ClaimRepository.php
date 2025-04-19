<?php

namespace App\Repositories;


use App\Enums\ClaimPriority;
use App\Enums\ClaimStatus;
use App\Enums\ClaimType;
use App\Models\Claim;
use Illuminate\Support\Collection;

class ClaimRepository
{
    /**
     * @param  Collection  $data
     * @param  array|null  $files
     * @return Claim
     */
    public function createClaim(Collection $data, array $files = null): Claim
    {
        $type = ClaimType::from($data['type']);
        $accountID = $data['account_id'];

        $claim = new Claim();
        $claim->type = $type;
        $claim->title = $data['title'];
        $claim->text = $data->get('text', null);
        $claim->account_id = $accountID;
        $claim->status = ClaimStatus::from($data['status']);
        $claim->priority = ClaimPriority::from($data['priority']);
        $claim->is_active = true;

        foreach ($type->getDataFieldMappings() as $modelField => $dataField) {
            if (is_int($modelField)) {
                $modelField = $dataField;
            }
            $claim->{$modelField} = $data[$dataField];
        }

        if ($data->has('worker') && isset($data['worker']['id'])) {
            $claim->worker_id = $data['worker']['id'];
        }

        $claim->save();

        if (!empty($files)) {
            $claim->files()->saveMany($files);
        }

        return $claim;
    }

    public function updateClaim(Claim $claim, Collection $data): Claim
    {
        if ($data->has('title')) {
            $claim->title = $data['title'];
        }
        if ($data->has('text')) {
            $claim->text = $data['text'];
        }
        if ($data->has('status')) {
            $claim->status = ClaimStatus::from($data['status']);
        }
        if ($data->has('priority')) {
            $claim->priority = ClaimPriority::from($data['priority']);
        }
        if ($data->has('worker') && isset($data['worker']['id'])) {
            $claim->worker_id = $data['worker']['id'];
        }

        $typeSpecificFields = $claim->type->getDataFieldMappings();

        foreach ($typeSpecificFields as $modelField => $dataField) {
            if (is_int($modelField)) {
                $modelField = $dataField;
            }
            if ($data->has($dataField)) {
                $claim->{$modelField} = $data[$dataField];
            }
        }

        $claim->save();

        return $claim;
    }
}
