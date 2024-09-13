<?php

namespace App\Jobs;

use App\Models\Apartment;
use App\Models\CounterData;
use App\Models\CounterHistory;
use App\Models\House;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use LaravelIdea\Helper\App\Models\_IH_Apartment_QB;


class ProcessCustomerData implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $customer)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $customer = $this->customer;

        $customer['Задолженность'] == null ? $debt = 0 : $debt = $this->parseFloat($customer['Задолженность']);
        /* todo пользователь может быть помечен на удаление
         * докинуть таблицу с account_id
         *
         *
         * Хранить данные по счетчикам, отдавать просто фильром за последний месяц
        */
        $email = $customer['АдресЭлектроннойПочты'] ?? str()->random(5) . sha1(time()) . '@asdasdasd.rrrr';
        $phone = $customer['Телефон'] ?? '+7123' . rand(1, 99999999);

        if (($house = $this->houseExistence($customer)) !== null) {

        } else {
            $house = $this->createHouse($customer);
        }
        $apartment = $this->findApartment($house, $customer['Помещение']) ?? new Apartment();
        $apartment->house_id = $house->id;
        $this->setApartmentData($apartment, $customer);
        $user = $this->checkUser($customer['Телефон']) ?? $this->checkUserByBIO($customer['ОтветственныйВладелец']);
        if ($user == null) {
            $user = $this->createNewUser($email, $phone, $customer);
            $client = $this->createNewClientObj($user, $phone, $customer);
        } else {
            $client = $user->client()->first();
            if ($client == null) {
                $client = $this->createNewClientObj($user, $phone, $customer);
            }
        }
        $client->debt = $debt;
        $client->save();
        $this->isApartmentAlreadyAttached($client) ?: $client->apartments()->sync($apartment, false);
    }


    public function houseExistence($customer): House|null
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
     * @param $customerPhone
     * @return User
     */
    private function checkUser($customerPhone)
    {
        return User::where('phone', $customerPhone)->first();
    }

    /**
     * @param $BIO
     * @return User
     */
    private function checkUserByBIO($BIO)
    {
        return User::where('name', $BIO)->first();
    }

    /**
     * @param $string
     * @return array|string|string[]
     */
    public function parseFloat($string)
    {
        $string = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', ($string));
        return str_replace(',', '.', $string);
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
        $apartment->account_id = $customer['Идентификатор'];
        $apartment->gku_id = $customer['ИдентификаторЖКУ'];
        $apartment->account_number = $customer['ЕдиныйЛицевойСчет'];
        $apartment->account_owner = $customer['ОтветственныйВладелец'];
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
        $user->name = $customer['ОтветственныйВладелец'];
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

    public function isApartmentAlreadyAttached($client)
    {
        if ($client->apartments()->count() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
