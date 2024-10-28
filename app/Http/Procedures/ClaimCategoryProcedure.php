<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Models\ClaimCategory;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Sajya\Server\Procedure;


class ClaimCategoryProcedure extends Procedure
{
    public static string $name = 'claim_category';

    /**
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function getCategories(ApiResponseBuilder $responseBuilder): array
    {
        return $responseBuilder->setData(ClaimCategory::all())->setMessage("Chat message was create successfully")->build();
    }


}
