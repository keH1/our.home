<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Enums\ClaimMessageSenderType;
use App\Models\Claim;
use App\Models\ClaimMessage;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Procedure;
use \Illuminate\Support\Collection;
use \App\Repositories\FileRepository;

class ClaimMessageProcedure extends Procedure
{
    public static string $name = 'claim_message';

    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @param FileRepository $fileRepository
     * @return array
     */
    public function createMessage(Request $request, ApiResponseBuilder $responseBuilder, FileRepository $fileRepository): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);

        if (strlen($data['text']) == 0 && count($data['files']) == 0) {
            throw new InvalidParams(['message' => "Empty message"]);
        }
        $claim = Claim::find($data['claim_id']);
        if ($claim !== null) {
            $fileRepository->setUploadSubDir("claims_chat/{$data['claim_id']}/");
            $message = $this->createMessageObj($data);
            $message->save();
            if (count($data['files']) > 0) {
                foreach ($data['files'] as $fileToUpload) {
                    $uploadedFile = $fileRepository->uploadFileToStorage($fileToUpload['file_original_name'], $fileToUpload['file']);
                    $message->files()->save($uploadedFile);
                }
            }
            return $responseBuilder->setData([])->setMessage("Chat message was create successfully")->build();
        }
        throw new InvalidParams(['message' => "claim with id {$data['claim_id']} does not exist"]);
    }


    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function getClaimChat(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        if(!($data['claim_id']>0)){
            throw new InvalidParams(['message' => "empty id field"]);
        }
        $messageReq = ClaimMessage::with(['files'])->where('claim_id',$data['claim_id']);
        if(isset($data['limit']) && $data['limit'] > 0){
            $messageReq->limit($data['limit']);
        }
        if(isset($data['offset']) && $data['offset'] > 0){
            $messageReq->offset($data['offset']);
        }
        $messages = $messageReq->get();
        if ($messages == null){
            throw new InvalidParams(['message' => "claim with id {$data['claim_id']} does not exist"]);
        }

        return $responseBuilder->setData($messages)->build();

    }

    /**
     * @param Collection $data
     * @return ClaimMessage
     */
    private function createMessageObj(Collection $data): ClaimMessage
    {
        $message = new ClaimMessage();
        strlen($data['text']) > 0 ? $message->text = $data['text'] : $message->text = '';
        $message->claim_id = $data['claim_id'];
        $message->sender_type = ClaimMessageSenderType::{strtoupper($data['from'])}?->value;
        return $message;
    }
}
