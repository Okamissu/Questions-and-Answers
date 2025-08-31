<?php

namespace App\Tests\Controller;

use App\Controller\CategoryController;
use App\Dto\CreateCategoryDto;
use App\Dto\ListFiltersDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;
use App\Service\CategoryServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryControllerTest extends TestCase
{
    private CategoryServiceInterface|MockObject $serviceMock;
    private ValidatorInterface|MockObject $validatorMock;
    private SerializerInterface|MockObject $serializerMock;
    private CategoryController|MockObject $controller;

    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(CategoryServiceInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        // Mock controller
        $this->controller = $this->getMockBuilder(CategoryController::class)
            ->onlyMethods(['denyAccessUnlessGranted', 'json'])
            ->setConstructorArgs([$this->serviceMock, $this->serializerMock, $this->validatorMock])
            ->getMock();

        // Mock denyAccessUnlessGranted to do nothing
        $this->controller->method('denyAccessUnlessGranted')
            ->willReturnCallback(function () { /* do nothing */ });

        // Mock json() so it returns a real JsonResponse
        $this->controller->method('json')->willReturnCallback(function ($data, $status = 200) {
            // If $data is Category object, mimic serialization
            if ($data instanceof Category) {
                $data = ['name' => $data->getName()];
            }

            return new JsonResponse($data, $status);
        });
    }

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


    public function testShow(): void
    {
        $category = new Category();
        $category->setName('Category Show');

        $this->serializerMock->method('serialize')->willReturn(json_encode(['name' => $category->getName()]));

        $response = $this->controller->show($category);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('Category Show', $data['name']);
    }

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

    public function testDelete(): void
    {
        $category = new Category();
        $this->serviceMock->expects($this->once())->method('delete')->with($category);

        $response = $this->controller->delete($category);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
