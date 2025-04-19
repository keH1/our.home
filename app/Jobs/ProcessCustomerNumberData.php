<?php

namespace App\Jobs;

use App\Models\AccountPersonalNumber;
use App\Models\Apartment;
use App\Models\User;
use App\Repositories\AccountRepository;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Hash;


class ProcessCustomerNumberData implements ShouldQueue
{
    use Queueable;

    public AccountRepository $accountRepository;
    public UserRepository $userRepository;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $customerNumber)
    {
        $this->userRepository = new UserRepository();
        $this->accountRepository = new AccountRepository();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $customerNumber = $this->customerNumber;
//        $channel = Log::build([
//            'driver' => 'single',
//            'path' => storage_path('logs/1c_customer.log'),
//        ]);
//        Log::stack(['slack', $channel])->info(json_encode($customer));
        if ($customerNumber['phone'] !== null) {
            $customerNumber['phone'] = $this->parsePhone($customerNumber['phone']);;
            if (!($personalNumber = AccountPersonalNumber::where('number', $customerNumber['id'])->first())) {
                $personalNumber = new AccountPersonalNumber();
            }
            $this->setPersonalNumberFields($personalNumber, $customerNumber);
            $this->tryToAttachAccountToApartment($personalNumber, $customerNumber);
            $this->tryToCreateUser($personalNumber, $customerNumber);
        }

    }

    /**
     * @param AccountPersonalNumber $personalNumber
     * @param array $customerNumber
     * @return AccountPersonalNumber
     */
    public function setPersonalNumberFields(AccountPersonalNumber $personalNumber, array $customerNumber): AccountPersonalNumber
    {
        $personalNumber->number = $customerNumber['id'];
        $personalNumber->els_id = $customerNumber['els_id'] ?? null;
        $personalNumber->gis_id = $customerNumber['gis_id'] ?? null;
        $personalNumber->email = $customerNumber['email'] ?? null;
        $personalNumber->phone = $customerNumber['phone'] ?? null;
        $personalNumber->login = $customerNumber['login'] ?? null;
        $personalNumber->one_c_id = $customerNumber['account_id_1c'] ?? null;
        $personalNumber->debt = $this->parseFloat($customerNumber['debt']) ?? null;
        $personalNumber->is_active = $customerNumber['is_active'] === 'Да';
        $personalNumber->fio = $customerNumber['fio'] ?? null;
        $personalNumber->save();
        return $personalNumber;
    }

    /**
     * @param AccountPersonalNumber $personalNumber
     * @param array $customerNumber
     * @return void
     */
    public function tryToAttachAccountToApartment(AccountPersonalNumber $personalNumber, array $customerNumber): void
    {
        $isApartmentExist = Apartment::where('one_c_id', $customerNumber['appartment_id'])->first();
        if ($isApartmentExist !== null && $personalNumber->apartment?->one_c_id !== $customerNumber['appartment_id']) {
            $personalNumber->apartment_id = $isApartmentExist->id;
            $personalNumber->save();
        }

    }

    /**
     * @param AccountPersonalNumber $personalNumber
     * @param array $customerNumber
     * @return void
     */
    public function tryToCreateUser(AccountPersonalNumber $personalNumber, array $customerNumber): void
    {
        if ($customerNumber['phone'] !== null) {
            $user = $this->userRepository->checkUserByPhone($customerNumber['phone']);
            if ($user instanceof User) {
                $personalNumber->user_id = $user->id;
            } else {
                $user = $this->createUser($customerNumber);
                $personalNumber->user_id = $user->id;
            }
            $personalNumber->save();
        }
    }

    /**
     * @param array $customerNumber
     * @return User
     */
    public function createUser(array $customerNumber): User
    {
        $user = new User();
        $user->email = $customerNumber['email'];
        $user->phone = $customerNumber['phone'];
        $user->password = Hash::make(Hash::make('123'));
        $user->save();

        return $user;
    }

    /**
     * @param $string
     * @return array|string|null
     */
    public function parseFloat($string): array|string|null
    {
        if ($string == '') {
            return null;
        }
        return preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', ($string));
    }


    /**
     * @param $string
     * @return string
     */
    public function parsePhone($string): string
    {
        $tel = trim($string);
        $tel = preg_replace('/[^0-9]/', '', $tel);
        preg_match('/9[0-9]{1,9}/', $tel, $matches);
        return $matches[0];
    }

}
