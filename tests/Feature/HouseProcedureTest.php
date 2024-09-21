<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseProcedureTest extends TestCase
{
    use RefreshDatabase;

    public function testGetAllStreetsSuccessfully(): void
    {
        // Создаем и аутентифицируем пользователя
        $user = User::factory()->create();
        $this->actingAs($user);

        // Создаем 30 домов с разными улицами
        for ($i = 1; $i <= 30; $i++) {
            House::factory()->create(['street' => 'Улица ' . $i]);
        }

        // Формируем JSON-RPC запрос
        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'get_all_streets',
            'params' => [
                'limit' => 10,
                'offset' => 5,
            ],
            'id' => 1,
        ];

        // Отправляем POST-запрос
        $response = $this->postJson('/api/v1/jsonrpc', $requestData);

        // Проверяем статус ответа
        $response->assertStatus(200);

        // Проверяем структуру ответа
        $responseData = $response->json();
        $this->assertArrayHasKey('result', $responseData);
        $this->assertArrayHasKey('data', $responseData['result']);
        $this->assertArrayHasKey('pagination', $responseData['result']);

        // Проверяем данные
        $streets = $responseData['result']['data'];
        $this->assertCount(10, $streets);
        $this->assertEquals('Улица 14', $streets[0]); // Так как offset = 5

        // Проверяем пагинацию
        $pagination = $responseData['result']['pagination'];
        $this->assertEquals(30, $pagination['total']);
        $this->assertEquals(10, $pagination['limit']);
        $this->assertEquals(5, $pagination['offset']);
    }

    public function testGetAllStreetsEmpty(): void
    {
        // Создаем и аутентифицируем пользователя
        $user = User::factory()->create();
        $this->actingAs($user);

        // База данных домов пуста

        // Формируем JSON-RPC запрос без параметров
        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'get_all_streets',
            'params' => [],
            'id' => 2,
        ];

        // Отправляем POST-запрос
        $response = $this->postJson('/api/v1/jsonrpc', $requestData);

        // Проверяем статус ответа
        $response->assertStatus(200);

        // Проверяем структуру ответа
        $responseData = $response->json();
        $this->assertArrayHasKey('result', $responseData);
        $this->assertArrayHasKey('data', $responseData['result']);
        $this->assertArrayNotHasKey('pagination', $responseData['result']);

        // Проверяем данные
        $this->assertEmpty($responseData['result']['data']);

        // Проверяем сообщение
        $this->assertEquals('Список улиц пуст', $responseData['result']['message']);
    }

    public function testGetAllStreetsWithInvalidPagination(): void
    {
        // Создаем и аутентифицируем пользователя
        $user = User::factory()->create();
        $this->actingAs($user);

        // Формируем JSON-RPC запрос с неверными параметрами
        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'get_all_streets',
            'params' => [
                'limit' => -10,
                'offset' => -5,
            ],
            'id' => 3,
        ];

        // Отправляем POST-запрос
        $response = $this->postJson('/api/v1/jsonrpc', $requestData);

        // Проверяем статус ответа
        $response->assertStatus(200);

        // Проверяем, что вернулась ошибка
        $responseData = $response->json();
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals(-32602, $responseData['error']['code']);
        $this->assertEquals('Неверные параметры пагинации', $responseData['error']['data']);
    }

    public function testGetAllStreetsWithoutPaginationParams(): void
    {
        // Создаем и аутентифицируем пользователя
        $user = User::factory()->create();
        $this->actingAs($user);

        // Создаем 20 домов с уникальными улицами
        for ($i = 1; $i <= 20; $i++) {
            House::factory()->create(['street' => 'Улица ' . $i]);
        }

        // Формируем JSON-RPC запрос без параметров пагинации
        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'get_all_streets',
            'params' => [],
            'id' => 4,
        ];

        // Отправляем POST-запрос
        $response = $this->postJson('/api/v1/jsonrpc', $requestData);

        // Проверяем статус ответа
        $response->assertStatus(200);

        // Проверяем структуру ответа
        $responseData = $response->json();
        $this->assertArrayHasKey('result', $responseData);
        $this->assertArrayHasKey('data', $responseData['result']);
        $this->assertArrayHasKey('pagination', $responseData['result']);

        // Проверяем данные
        $streets = $responseData['result']['data'];
        $defaultLimit = config('pagination.default_limit', 15);
        $this->assertCount($defaultLimit, $streets);

        // Проверяем пагинацию
        $pagination = $responseData['result']['pagination'];
        $this->assertEquals(20, $pagination['total']);
        $this->assertEquals($defaultLimit, $pagination['limit']);
        $this->assertEquals(0, $pagination['offset']);
    }

    public function testGetHousesByStreetSuccessfully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        House::factory()->createMany([
            ['street' => 'Ленина', 'number' => '1'],
            ['street' => 'Ленина', 'number' => '2'],
            ['street' => 'Ленина', 'number' => '3'],
            ['street' => 'Мира', 'number' => '10'],
            ['street' => 'Советская', 'number' => '5'],
        ]);

        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'get_house_by_street',
            'params' => [
                'street' => 'Ленина',
            ],
            'id' => 1,
        ];

        $response = $this->postJson('/api/v1/jsonrpc', $requestData);
        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('result', $responseData);
        $this->assertArrayHasKey('data', $responseData['result']);
        $this->assertArrayHasKey('pagination', $responseData['result']);

        $houses = $responseData['result']['data'];
        $this->assertCount(3, $houses);

        foreach ($houses as $house) {
            $this->assertEquals('Ленина', $house['street']);
        }

        $pagination = $responseData['result']['pagination'];
        $this->assertEquals(3, $pagination['total']);
    }

    public function testGetHousesByStreetNoResults(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        House::factory()->createMany([
            ['street' => 'Мира', 'number' => '10'],
            ['street' => 'Советская', 'number' => '5'],
        ]);

        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'get_house_by_street',
            'params' => [
                'street' => 'Пушкина',
            ],
            'id' => 2,
        ];

        $response = $this->postJson('/api/v1/jsonrpc', $requestData);
        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('result', $responseData);
        $this->assertArrayHasKey('data', $responseData['result']);

        $houses = $responseData['result']['data'];
        $this->assertEmpty($houses);

        $this->assertEquals('Дома по указанной улице не найдены', $responseData['result']['message']);
    }

    public function testGetHousesByStreetInvalidParams(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'get_house_by_street',
            'params' => [],
            'id' => 3,
        ];

        $response = $this->postJson('/api/v1/jsonrpc', $requestData);
        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals(-32602, $responseData['error']['code']);
        $this->assertEquals('Invalid params', $responseData['error']['message']);

        $this->assertArrayHasKey('data', $responseData['error']);
        $this->assertArrayHasKey('street', $responseData['error']['data']);
    }

    public function testGetHousesByStreetWithPagination(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        for ($i = 1; $i <= 25; $i++) {
            House::factory()->create([
                'street' => 'Ленина',
                'number' => (string)$i,
            ]);
        }

        $requestData = [
            'jsonrpc' => '2.0',
            'method' => 'get_house_by_street',
            'params' => [
                'street' => 'Ленина',
                'limit' => 10,
                'offset' => 10,
            ],
            'id' => 4,
        ];

        $response = $this->postJson('/api/v1/jsonrpc', $requestData);
        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertArrayHasKey('result', $responseData);
        $this->assertArrayHasKey('data', $responseData['result']);
        $this->assertArrayHasKey('pagination', $responseData['result']);

        $houses = $responseData['result']['data'];
        $this->assertCount(10, $houses);

        $pagination = $responseData['result']['pagination'];
        $this->assertEquals(25, $pagination['total']);
        $this->assertEquals(10, $pagination['limit']);
        $this->assertEquals(10, $pagination['offset']);
    }

}
