<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Attributes\RpcProcedure;
use App\Contracts\ProcedurePermissionsInterface;
use App\Enums\Permissions;
use App\Models\ClaimReview;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Sajya\Server\Procedure;

#[RpcProcedure(version: 'v1', group: 'claims')]
class ClaimReviewProcedure extends Procedure implements ProcedurePermissionsInterface
{
    public static string $name = 'claim_review';

    public function getMethodsPermissions(): array
    {
        return [
            'createReview' => [Permissions::NORMAL],
        ];
    }

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     * @throws ValidationException
     */
    public function createReview(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = json_decode($request->getContent(), true)['params'];

        Validator::make($data, [
            'claim_id' => 'required|exists:claims,id',
            'rating' => 'required|in:like,dislike',
            'text' => 'nullable|string',
        ])->validate();

        $review = ClaimReview::create([
            'claim_id' => $data['claim_id'],
            'rating' => $data['rating'],
            'text' => $data['text'] ?? null,
        ]);

        return $responseBuilder->setData(['review' => $review->toArray()])->setMessage("Review created successfully.")->build();
    }
}
