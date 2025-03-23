<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Attributes\RpcProcedure;
use App\Contracts\ProcedurePermissionsInterface;
use App\Enums\ClaimMessageSenderType;
use App\Enums\NotificationType;
use App\Enums\Permissions;
use App\Models\Claim;
use App\Models\ClaimMessage;
use App\Repositories\NotificationRepository;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Procedure;
use \Illuminate\Support\Collection;
use \App\Repositories\FileRepository;

#[RpcProcedure(version: 'v1', group: 'claims')]
class ClaimMessageProcedure extends Procedure implements ProcedurePermissionsInterface
{
    public static string $name = 'claim_message';

    public function getMethodsPermissions(): array
    {
        return [
            'createMessage' => [Permissions::NORMAL],
            'getClaimChat' => [Permissions::NORMAL],
            'createMessageObj' => [Permissions::NORMAL],
        ];
    }


    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @param FileRepository $fileRepository
     * @param NotificationRepository $notificationRepository
     * @return array
     * @throws \Exception
     */
    public function createMessage(Request $request, ApiResponseBuilder $responseBuilder, FileRepository $fileRepository, NotificationRepository $notificationRepository): array
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
            $user = $claim->client->user;
            if(!$user->isOnline()){
                $notification = $notificationRepository->createNotificationByTemplate($user->id,NotificationType::USER);
                $notificationRepository->putMessageIntoQueue($notification);
            }
            if (count($data['files']) > 0) {
                $filesArr = [];
                foreach ($data['files'] as $fileToUpload) {
                    $filesArr[] = $fileRepository->uploadFileToStorage($fileToUpload['file_original_name'], $fileToUpload['file']);
                }
                if(count($filesArr)>0){
                    $message->files()->saveMany($filesArr);
                }
            }
            return $responseBuilder->setData([])->setMessage("Chat message was created successfully")->build();
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
