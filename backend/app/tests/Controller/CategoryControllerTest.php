<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Service\CategoryService;

class CategoryControllerTest extends WebTestCase
{
    public function testListReturnsPaginatedCategories(): void
    {
        // 1. Tworzymy mock serwisu
        $mockService = $this->createMock(CategoryService::class);
        $mockService->method('getPaginatedList')
            ->willReturn([
                'items' => [
                    ['id' => 1, 'name' => 'Test Category'],
                ],
                'totalItems' => 1,
            ]);

        // 2. Tworzymy klienta i podmieniamy serwis w kontenerze
        $client = static::createClient();
        $container = static::getContainer();
        $container->set(CategoryService::class, $mockService);

        // 3. Wysyłamy żądanie HTTP
        $client->request('GET', '/api/categories?page=1&limit=5');

        // 4. Asercje
        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('items', $data);
        $this->assertEquals('Test Category', $data['items'][0]['name']);
    }
}
