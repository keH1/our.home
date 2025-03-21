<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Attributes\RpcProcedure;
use App\Contracts\ProcedurePermissionsInterface;
use App\Enums\Permissions;
use App\Models\ClaimCategory;
use App\Services\ApiResponseBuilder;
use Sajya\Server\Procedure;

#[RpcProcedure(version: 'v1', group: 'claims')]
class ClaimCategoryProcedure extends Procedure implements ProcedurePermissionsInterface
{
    public static string $name = 'claim_category';

    public function getMethodsPermissions(): array
    {
        return [
            'getCategories' => [Permissions::NORMAL],
        ];
    }

    /**
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function getCategories(ApiResponseBuilder $responseBuilder): array
    {
        return $responseBuilder->setData(ClaimCategory::all())->build();
    }

}
