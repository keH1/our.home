<?php

namespace Tests\Feature;

use App\Models\AccountPersonalNumber;
use App\Models\Apartment;
use App\Models\CounterData;
use App\Models\CounterHistory;
use App\Models\House;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetApartmentsWithCountersTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Тест успешного получения списка квартир со счетчиками.
     */
    public function testGetApartmentsWithCountersSuccessfully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $house = House::factory()->create();

        $apartments = Apartment::factory()->count(5)->for($house)->sequence(fn($sequence
            ) => ['number' => (string) ($sequence->index + 1)])->create();

        foreach ($apartments as $apartment) {
            $accountPersonalNumber = AccountPersonalNumber::factory()->create();

            $apartment->personal_number = $accountPersonalNumber->id;
            $apartment->save();

            $counter = CounterData::factory()->for($apartment)->create([
                    'personal_number' => $accountPersonalNumber->id, 'number' => 'C'.$apartment->number,
                ]);

            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $lastMonthDate = $this->faker->dateTimeBetween($lastMonthStart, $lastMonthEnd);

            CounterHistory::factory()->create([
                'counter_name_id' => $counter->id, 'approved' => true, 'last_checked_date' => $lastMonthDate,
                'daily_consumption' => 100, 'night_consumption' => 50, 'peak_consumption' => 150,
            ]);

            CounterHistory::factory()->create([
                'counter_name_id' => $counter->id, 'approved' => false, 'last_checked_date' => $lastMonthDate,
                'daily_consumption' => 110, 'night_consumption' => 55, 'peak_consumption' => 165,
            ]);
        }

        $requestData = [
            'jsonrpc' => '2.0', 'method' => 'get_apartments_with_counters', 'params' => [
                'house_id' => $house->id, 'limit' => 10, 'offset' => 0,
            ], 'id' => 1,
        ];

        $response = $this->postJson('/api/v1/jsonrpc', $requestData);

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('result', $responseData);
        $this->assertArrayHasKey('data', $responseData['result']);
        $this->assertArrayHasKey('pagination', $responseData['result']);

        $apartmentsData = $responseData['result']['data'];
        $this->assertCount(5, $apartmentsData);

        $pagination = $responseData['result']['pagination'];
        $this->assertEquals(5, $pagination['total']);
        $this->assertEquals(10, $pagination['limit']);
        $this->assertEquals(0, $pagination['offset']);

        foreach ($apartmentsData as $apartmentData) {
            $this->assertArrayHasKey('id', $apartmentData);
            $this->assertArrayHasKey('number', $apartmentData);
            $this->assertArrayHasKey('counters', $apartmentData);

            $counters = $apartmentData['counters'];
            $this->assertNotEmpty($counters);

            foreach ($counters as $counterData) {
                $this->assertArrayHasKey('counter_id', $counterData);
                $this->assertArrayHasKey('counter_number', $counterData);
                $this->assertArrayHasKey('counter_type', $counterData);
                $this->assertArrayHasKey('confirmed_history', $counterData);
                $this->assertArrayHasKey('unconfirmed_history', $counterData);

                $this->assertNotNull($counterData['confirmed_history']);
                $this->assertArrayHasKey('daily_consumption', $counterData['confirmed_history']);
                $this->assertArrayHasKey('night_consumption', $counterData['confirmed_history']);
                $this->assertArrayHasKey('peak_consumption', $counterData['confirmed_history']);
                $this->assertArrayHasKey('last_checked_date', $counterData['confirmed_history']);

                $this->assertNotNull($counterData['unconfirmed_history']);
                $this->assertArrayHasKey('daily_consumption', $counterData['unconfirmed_history']);
                $this->assertArrayHasKey('night_consumption', $counterData['unconfirmed_history']);
                $this->assertArrayHasKey('peak_consumption', $counterData['unconfirmed_history']);
                $this->assertArrayHasKey('last_checked_date', $counterData['unconfirmed_history']);
            }
        }
    }

    /**
     * Тест получения пустого списка квартир.
     */
    public function testGetApartmentsWithCountersNoApartments(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $house = House::factory()->create();

        $requestData = [
            'jsonrpc' => '2.0', 'method' => 'get_apartments_with_counters', 'params' => [
                'house_id' => $house->id, 'limit' => 10, 'offset' => 0,
            ], 'id' => 2,
        ];

        $response = $this->postJson('/api/v1/jsonrpc', $requestData);

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('result', $responseData);
        $this->assertArrayHasKey('data', $responseData['result']);

        $apartmentsData = $responseData['result']['data'];
        $this->assertEmpty($apartmentsData);

        $this->assertEquals('Квартиры не найдены', $responseData['result']['message']);
    }

    /**
     * Тест обработки неверных параметров запроса.
     */
    public function testGetApartmentsWithCountersInvalidParams(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $requestData = [
            'jsonrpc' => '2.0', 'method' => 'get_apartments_with_counters', 'params' => [
                'limit' => 10, 'offset' => 0,
            ], 'id' => 3,
        ];

        $response = $this->postJson('/api/v1/jsonrpc', $requestData);

        // Проверяем статус ответа
        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals(-32602, $responseData['error']['code']);
        $this->assertArrayHasKey('data', $responseData['error']);
        $this->assertArrayHasKey('house_id', $responseData['error']['data']);
    }

    /**
     * Тест проверки пагинации.
     */
    public function testGetApartmentsWithCountersPagination(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $house = House::factory()->create();

        $apartments = Apartment::factory()->count(30)->for($house)->sequence(fn($sequence
            ) => ['number' => (string) ($sequence->index + 1)])->create();

        foreach ($apartments as $apartment) {
            $accountPersonalNumber = AccountPersonalNumber::factory()->create();

            $apartment->personal_number = $accountPersonalNumber->id;
            $apartment->save();
        }

        $requestData = [
            'jsonrpc' => '2.0', 'method' => 'get_apartments_with_counters', 'params' => [
                'house_id' => $house->id, 'limit' => 10, 'offset' => 10,
            ], 'id' => 4,
        ];

        $response = $this->postJson('/api/v1/jsonrpc', $requestData);

        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('result', $responseData);
        $this->assertArrayHasKey('data', $responseData['result']);
        $this->assertArrayHasKey('pagination', $responseData['result']);

        $pagination = $responseData['result']['pagination'];
        $this->assertEquals(30, $pagination['total']);
        $this->assertEquals(10, $pagination['limit']);
        $this->assertEquals(10, $pagination['offset']);

        $apartmentsData = $responseData['result']['data'];
        $this->assertCount(10, $apartmentsData);
    }
}
