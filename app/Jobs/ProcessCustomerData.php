<?php

namespace App\Jobs;

use App\Models\AccountPersonalNumber;
use App\Models\Apartment;
use App\Models\House;
use App\Models\User;
use App\Repositories\AccountRepository;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use LaravelIdea\Helper\App\Models\_IH_Apartment_QB;
use Illuminate\Support\Facades\Log;
use \App\Services\StreetNormalizer;


class ProcessCustomerData implements ShouldQueue
{
    use Queueable;

    private AccountRepository $accountRepository;
    private UserRepository $userRepository;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $customer)
    {
        $this->userRepository = new UserRepository();
        $this->accountRepository = new AccountRepository();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $customer = $this->customer;
        $channel = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/1c_customer.log'),
        ]);
        Log::stack(['slack', $channel])->info(json_encode($customer));
        $customer['Задолженность'] == null ? $debt = 0 : $debt = $this->parseFloat($customer['Задолженность']);
        $customer['Улица'] = StreetNormalizer::normalizeStreetName($customer['Улица']);
        $email = $customer['АдресЭлектроннойПочты'] ?? str()->random(5) . sha1(time()) . '@asdasdasd.rrrr';
        $phone = '123' . rand(1, 99999999);
        if ($customer['Телефон'] !== null) {
            $tel = trim($customer['Телефон']);
            $tel = preg_replace('/[^0-9]/', '', $tel);
            preg_match('/9[0-9]{1,9}/', $tel, $matches);
            $phone = $matches[0];
        }
        $customer['Телефон'] = $phone;
        if (($house = $this->houseExistence($customer)) !== null) {

        } else {
            $house = $this->createHouse($customer);
        }
        $apartment = $this->findApartment($house, $customer['Помещение']) ?? new Apartment();
        $apartment->house_id = $house->id;
        $this->setApartmentData($apartment, $customer);
        $user = $this->userRepository->checkUserByPhone($customer['Телефон']);
        if ($user == null) {
            $user = $this->createNewUser($email, $phone, $customer);
        }
        $clients = $user->clients;
        if (!$clients->contains('name', $customer['ОтветственныйВладелец'])) {
            $currentClient = $this->createNewClientObj($user, $phone, $customer);
        } else {
            $clientCollection = $clients->where('name', $customer['ОтветственныйВладелец'])->first();
            $currentClient = Client::find($clientCollection->id);
        }
        $currentClient->debt = $debt;
        $currentClient->save();
        if (($account = $this->accountRepository->checkAccountByNumber($customer['Идентификатор'])) == null) {
            $account = $this->createPersonalAccount($customer);
        }
        $this->attachApartmentToAccount($apartment,$account);
        $this->syncClientWithAccountWithoutDetaching($currentClient, $account);
        $this->isApartmentAlreadyAttachedToClient($currentClient, $apartment) ?: $currentClient->apartments()->sync($apartment, false);
    }


    /**
     * @param array $customer
     * @return House|null
     */
    public function houseExistence(array $customer): House|null
    {
        return House::where([
            ['city', '=', $customer['Город']],
            ['street', '=', $customer['Улица']],
            ['number', '=', $customer['Дом']],
            ['building', '=', $customer['Корпус']],
        ])->first();
    }

    /**
     * @param House $house
     * @param $apartmentNumber
     * @return HasMany|_IH_Apartment_QB|null
     */
    public function findApartment(House $house, $apartmentNumber)
    {
        return $house->apartments()->where('number', $apartmentNumber)->first();
    }

    /**
     * @param $string
     * @return array|string|string[]
     */
    public function parseFloat($string)
    {
        if ($string == '') {
            return null;
        }
        return preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', ($string));
    }

    /**
     * @param array $customer
     * @return House
     */
    private function createHouse(array $customer): House
    {
        $house = new House();
        $house->city = $customer['Город'];
        $house->street = $customer['Улица'];
        $house->number = $customer['Дом'];
        $house->building = $customer['Корпус'];
        $house->save();
        return $house;
    }

    /**
     * @param $apartment
     * @param $customer
     * @return void
     */
    private function setApartmentData($apartment, $customer): void
    {
        $apartment->number = $customer['Помещение'];
        $apartment->save();
    }

    /**
     * @param $email
     * @param $phone
     * @param $customer
     * @return User
     */
    private function createNewUser($email, $phone, $customer): User
    {
        $user = new User();
        $user->email = $email;
        $user->phone = $phone;
        $user->password = Hash::make(Hash::make($email));
        $user->save();

        return $user;
    }

    /**
     * @param $user
     * @param $phone
     * @param array $customer
     * @return Client
     */
    private function createNewClientObj($user, $phone, array $customer): Client
    {
        $client = new Client();
        $client->user_id = $user->id;
        $client->phone = $phone;
        $client->name = $customer['ОтветственныйВладелец'];

        return $client;
    }

    public function isApartmentAlreadyAttachedToClient($client, $apartment)
    {
        if ($client->apartments()->where('apartments.id', '=', $apartment->id)->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $customer
     * @param $apartment
     * @return AccountPersonalNumber
     */
    private function createPersonalAccount($customer): AccountPersonalNumber
    {
        $account = new AccountPersonalNumber();
        $account->number = $customer['Идентификатор'];
        $account->gku_id = $customer['ИдентификаторЖКУ'];
        $account->union_number = $customer['ЕдиныйЛицевойСчет'];

        $account->save();
        return $account;
    }

    /**
     * @param $apartment
     * @param $account
     * @return void
     */
    private function attachApartmentToAccount($apartment, $account): void
    {
        $account->apartment_id = $apartment->id;
        $account->save();
    }

    /**
     * @param Client|null $client
     * @param AccountPersonalNumber|null $account
     * @return void
     */
    private function syncClientWithAccountWithoutDetaching(?Client $client, ?AccountPersonalNumber $account): void
    {
        $client->accounts()->sync($account, false);
    }
}
