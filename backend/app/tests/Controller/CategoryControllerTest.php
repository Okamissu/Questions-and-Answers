<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Controller;

use App\Controller\CategoryController;
use App\Dto\CreateCategoryDto;
use App\Dto\ListFiltersDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;
use App\Service\CategoryServiceInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CategoryControllerTest.
 *
 * Tests CRUD operations for CategoryController.
 *
 * @covers \App\Controller\CategoryController
 */
class CategoryControllerTest extends TestCase
{
    private CategoryServiceInterface|MockObject $serviceMock;
    private ValidatorInterface|MockObject $validatorMock;
    private SerializerInterface|MockObject $serializerMock;
    private CategoryController|MockObject $controller;

    /**
     * Sets up mocks and controller before each test.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(CategoryServiceInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        $this->controller = $this->getMockBuilder(CategoryController::class)
            ->onlyMethods(['denyAccessUnlessGranted', 'json'])
            ->setConstructorArgs([$this->serviceMock, $this->serializerMock, $this->validatorMock])
            ->getMock();

        $this->controller->method('denyAccessUnlessGranted')
            ->willReturnCallback(fn () => null);

        $this->controller->method('json')
            ->willReturnCallback(function ($data, $status = 200) {
                if ($data instanceof Category) {
                    $data = ['name' => $data->getName()];
                }

                return new JsonResponse($data, $status);
            });
    }

    /**
     * Tests that list endpoint returns paginated categories.
     */
    public function testList(): void
    {
        $filters = new ListFiltersDto();
        $filters->limit = 10;
        $filters->search = null;
        $filters->sort = null;

        $this->serviceMock->method('getPaginatedList')->willReturn([
            'items' => [['id' => 1, 'name' => 'Test Category']],
            'totalItems' => 1,
        ]);

        $page = 1;
        $response = $this->controller->list($filters, $page);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(1, $data['pagination']['totalItems']);
        $this->assertEquals('Test Category', $data['items'][0]['name']);
        $this->assertEquals($page, $data['pagination']['page']);
        $this->assertEquals($filters->limit, $data['pagination']['limit']);
    }

    /**
     * Tests that show endpoint returns a single category.
     *
     * @throws ExceptionInterface
     */
    public function testShow(): void
    {
        $category = new Category();
        $category->setName('Category Show');

        $this->serializerMock->method('serialize')
            ->willReturn(json_encode(['name' => $category->getName()]));

        $response = $this->controller->show($category);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('Category Show', $data['name']);
    }

    /**
     * Tests that create endpoint successfully creates a category.
     *
     * @throws ExceptionInterface
     */
    public function testCreate(): void
    {
        $dto = new CreateCategoryDto();
        $category = new Category();
        $category->setName('New Category');

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->serializerMock->method('serialize')->willReturn(json_encode(['name' => $category->getName()]));
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $this->serviceMock->method('create')->willReturn($category);

        $request = new Request([], [], [], [], [], [], json_encode(['name' => 'New Category']));
        $response = $this->controller->create($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('New Category', $data['name']);
    }

    /**
     * Tests that create endpoint returns 400 when validation fails.
     *
     * @throws ExceptionInterface
     */
    public function testCreateValidationError(): void
    {
        $dto = new CreateCategoryDto();
        $violation = new ConstraintViolation('Invalid name', '', [], '', '', '');
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList([$violation]));
        $this->serializerMock->method('deserialize')->willReturn($dto);

        $request = new Request([], [], [], [], [], [], json_encode(['name' => '']));
        $response = $this->controller->create($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid name', $data['error']);
    }

    /**
     * Tests that update endpoint successfully updates a category.
     *
     * @throws ExceptionInterface
     */
    public function testUpdate(): void
    {
        $category = new Category();
        $category->setName('Old Name');

        $dto = new UpdateCategoryDto();
        $updatedCategory = new Category();
        $updatedCategory->setName('Updated Name');

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->serializerMock->method('serialize')->willReturn(json_encode(['name' => $updatedCategory->getName()]));
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $this->serviceMock->method('update')->willReturn($updatedCategory);

        $request = new Request([], [], [], [], [], [], json_encode(['name' => 'Updated Name']));
        $response = $this->controller->update($request, $category);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Updated Name', $data['name']);
    }

    /**
     * Tests that update endpoint returns 400 when validation fails.
     *
     * @throws ExceptionInterface
     */
    public function testUpdateValidationError(): void
    {
        $category = new Category();
        $dto = new UpdateCategoryDto();
        $violation = new ConstraintViolation('Invalid name', '', [], '', '', '');
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList([$violation]));
        $this->serializerMock->method('deserialize')->willReturn($dto);

        $request = new Request([], [], [], [], [], [], json_encode(['name' => '']));
        $response = $this->controller->update($request, $category);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid name', $data['error']);
    }

    /**
     * Tests that delete endpoint deletes a category and returns 204.
     */
    public function testDelete(): void
    {
        $category = new Category();
        $this->serviceMock->expects($this->once())->method('delete')->with($category);

        $response = $this->controller->delete($category);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
