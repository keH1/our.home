<?php

namespace Tests\Feature;

use App\Models\AccountPersonalNumber;
use App\Models\Apartment;
use App\Models\CounterData;
use App\Models\CounterHistory;
use App\Models\House;
use App\Models\User;
use Database\Factories\ApartmentFactory;
use Database\Factories\HouseFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CounterProcedureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testAcceptHouseCounters(): void
    {
        //Создаем юзера
        $user = User::factory()->create();
        $this->actingAs($user);
        //Создаем дом
        $house = House::factory()->create();
        //Создаем 5 апартаментов для дома
        $apartments = Apartment::factory()->count(5)->for($house)->sequence(fn($sequence
        ) => ['number' => (string)($sequence->index + 1)])->create();
        //Массив для id созданных счетчиков чтоб потом слать по ним новую инофрмацию
        $counterIDs = [];
        foreach ($apartments as $apartment) {
            //для каждого апртмента по аккаунту
            $accountPersonalNumber = AccountPersonalNumber::factory()->create();

            $apartment->personal_number = $accountPersonalNumber->id;
            $apartment->save();
            //счетчик для апартаментов
            $counter = CounterData::factory()->for($apartment)->create([
                'personal_number' => $accountPersonalNumber->id, 'number' => 'C' . $apartment->number,
            ]);
            $counterIDs[] = $counter->id;

            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastMonthDate = $this->faker->dateTimeBetween($lastMonthStart, $lastMonthEnd);
            //исторя за прошлый месяц  для счетчика
            CounterHistory::factory()->create([
                'counter_name_id' => $counter->id, 'approved' => false, 'last_checked_date' => $lastMonthDate,
                'daily_consumption' => rand(10, 50), 'night_consumption' => rand(10, 50), 'peak_consumption' => 165, 'from_1c' => true
            ]);
        }
        //Формирование запроса
        $requestData = [
            "jsonrpc" => "2.0",
            "id" => "1",
            "method" => "send_counters_data",
            "params" => []
        ];
        //Меняем данные у каждого счетчика
        foreach ($counterIDs as $counterID) {
            $requestData['params'][] =
                [
                    "counter_id" => $counterID,
                    "daily_consumption" => rand(51, 100),
                    "night_consumption" => rand(51, 100),
                    "peak_consumption" => 123
                ];
        }
        $response = $this->postJson('/api/v1/jsonrpc', $requestData);
        // Проверяем статус ответа
        $response->assertStatus(200);

        // Проверяем структуру ответа
        $responseData = $response->json();

        $this->assertArrayHasKey('result', $responseData);
        $this->assertArrayHasKey('data', $responseData['result']);
        $this->assertArrayHasKey('message', $responseData['result']);

        // Проверяем данные
        $this->assertEquals('Counters data was sent successfully', $responseData['result']['message']);

    }

    public function testAcceptHouseCountersWithNullNewValues(): void
    {
        //Создаем юзера
        $user = User::factory()->create();
        $this->actingAs($user);
        //Создаем дом
        $house = House::factory()->create();
        //Создаем 5 апартаментов для дома
        $apartments = Apartment::factory()->count(5)->for($house)->sequence(fn($sequence
        ) => ['number' => (string)($sequence->index + 1)])->create();
        //Массив для id созданных счетчиков чтоб потом слать по ним новую инофрмацию
        $counterIDs = [];
        $counterHistoryArr = [];
        foreach ($apartments as $apartment) {
            //Для каждого апртмента по аккаунту
            $accountPersonalNumber = AccountPersonalNumber::factory()->create();
            $apartment->personal_number = $accountPersonalNumber->id;
            $apartment->save();
            //Счетчик для апартаментов
            $counter = CounterData::factory()->for($apartment)->create([
                'personal_number' => $accountPersonalNumber->id, 'number' => 'C' . $apartment->number,
            ]);
            $counterIDs[] = $counter->id;

            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastMonthDate = $this->faker->dateTimeBetween($lastMonthStart, $lastMonthEnd);
            //Исторя за прошлый месяц  для счетчика
            $counterHistory = CounterHistory::factory()->create([
                'counter_name_id' => $counter->id, 'approved' => false, 'last_checked_date' => $lastMonthDate,
                'daily_consumption' => rand(10, 50), 'night_consumption' => rand(10, 50), 'peak_consumption' => 165, 'from_1c' => true
            ]);
            $counterHistoryArr[$counterHistory->id] = $counterHistory;
        }
        //формирование запроса
        $requestData = [
            "jsonrpc" => "2.0",
            "id" => "1",
            "method" => "send_counters_data",
            "params" => []
        ];
        //Меняем данные у каждого счетчика
        foreach ($counterIDs as $counterID) {
            $requestData['params'][] =
                [
                    "counter_id" => $counterID,
                    "daily_consumption" => null,
                    "night_consumption" => null,
                    "peak_consumption" => null
                ];
        }
        $response = $this->postJson('/api/v1/jsonrpc', $requestData);

        // Проверяем статус ответа
        $response->assertStatus(200);

        // Проверяем структуру ответа
        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData['result']);
        $this->assertArrayHasKey('message', $responseData['result']);

        // Проверяем данные
        $this->assertEquals('Counters data was sent successfully', $responseData['result']['message']);

        $dbCounterHistory = CounterHistory::get();
        $this->assertEquals(count($counterHistoryArr), $dbCounterHistory->count());
        // проверям чтоб значения не поменялись в базе
        foreach ($dbCounterHistory as $historyItem) {
            $this->assertEquals($historyItem->daily_consumption, $counterHistoryArr[$historyItem->id]['daily_consumption']);
            $this->assertEquals($historyItem->night_consumption, $counterHistoryArr[$historyItem->id]['night_consumption']);
            $this->assertEquals($historyItem->peak_consumption, $counterHistoryArr[$historyItem->id]['peak_consumption']);
        }

    }

    public function testAcceptHouseCountersWithNullId(): void
    {
        //Создаем юзера
        $user = User::factory()->create();
        $this->actingAs($user);
        //Создаем дом
        $house = House::factory()->create();
        //Создаем 5 апартаментов для дома
        $apartments = Apartment::factory()->count(5)->for($house)->sequence(fn($sequence
        ) => ['number' => (string)($sequence->index + 1)])->create();
        //Массив для id созданных счетчиков чтоб потом слать по ним новую инофрмацию
        $counterIDs = [];
        foreach ($apartments as $apartment) {
            //Для каждого апртмента по аккаунту
            $accountPersonalNumber = AccountPersonalNumber::factory()->create();
            $apartment->personal_number = $accountPersonalNumber->id;
            $apartment->save();
            //Счетчик для апартаментов
            $counter = CounterData::factory()->for($apartment)->create([
                'personal_number' => $accountPersonalNumber->id, 'number' => 'C' . $apartment->number,
            ]);
            $counterIDs[] = $counter->id;

            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastMonthDate = $this->faker->dateTimeBetween($lastMonthStart, $lastMonthEnd);
            //Исторя за прошлый месяц  для счетчика
            CounterHistory::factory()->create([
                'counter_name_id' => $counter->id, 'approved' => false, 'last_checked_date' => $lastMonthDate,
                'daily_consumption' => rand(10, 50), 'night_consumption' => rand(10, 50), 'peak_consumption' => 165, 'from_1c' => true
            ]);
        }
        //Формирование запроса
        $requestData = [
            "jsonrpc" => "2.0",
            "id" => "1",
            "method" => "send_counters_data",
            "params" => []
        ];
        //Меняем данные у каждого счетчика
        foreach ($counterIDs as $counterID) {
            $requestData['params'][] =
                [
                    "counter_id" => null,
                    "daily_consumption" => 123,
                    "night_consumption" => 123,
                    "peak_consumption" => 123
                ];
        }
        $response = $this->postJson('/api/v1/jsonrpc', $requestData);

        // Проверяем статус ответа
        $response->assertStatus(200);

        // Проверяем структуру ответа
        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData['result']);
        $this->assertArrayHasKey('message', $responseData['result']);

        // Проверяем данные
        $this->assertEquals("You are sending empty ids / this ids doe's not exist", $responseData['result']['message']);
    }
}
