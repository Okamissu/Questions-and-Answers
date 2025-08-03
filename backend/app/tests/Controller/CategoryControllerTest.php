<?php

namespace App\Tests\Controller;

use App\Controller\CategoryController;
use App\Dto\CreateCategoryDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;
use App\Service\CategoryService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryControllerTest extends WebTestCase
{
    private MockObject $categoryService;
    private MockObject $serializer;
    private MockObject $validator;
    private CategoryController $controller;

    protected function setUp(): void
    {
        $this->categoryService = $this->createMock(CategoryService::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->controller = new CategoryController(
            $this->categoryService,
            $this->serializer,
            $this->validator
        );
    }

    public function testListReturnsSerializedCategories()
    {
        $categories = [new Category()];
        $jsonCategories = '[{"id":1,"name":"Test"}]';

        $this->categoryService->expects($this->once())
            ->method('getAllCategories')
            ->willReturn($categories);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($categories, 'json', ['groups' => ['category:read']])
            ->willReturn($jsonCategories);

        $response = $this->controller->list();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($jsonCategories, $response->getContent());
        $this->assertTrue(
            str_contains($response->headers->get('Content-Type'), 'json'),
            'Response content type is not JSON'
        );

    }

    public function testCreateReturnsCreatedCategoryOnValidData()
    {
        $jsonInput = '{"name":"New Category"}';
        $dto = new CreateCategoryDto();
        $category = new Category();
        $jsonOutput = '{"id":1,"name":"New Category"}';

        $request = new Request([], [], [], [], [], [], $jsonInput);

        // Deserialize call
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($jsonInput, CreateCategoryDto::class, 'json')
            ->willReturn($dto);

        // Validator returns empty error list
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($dto)
            ->willReturn(new ConstraintViolationList());

        // Service create returns entity
        $this->categoryService->expects($this->once())
            ->method('create')
            ->with($dto)
            ->willReturn($category);

        // Serializer serializes entity
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($category, 'json', ['groups' => ['category:read']])
            ->willReturn($jsonOutput);

        $response = $this->controller->create($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($jsonOutput, $response->getContent());
    }

    public function testCreateReturnsBadRequestOnValidationErrors()
    {
        $jsonInput = '{"name":""}';
        $dto = new CreateCategoryDto();
        $request = new Request([], [], [], [], [], [], $jsonInput);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($jsonInput, CreateCategoryDto::class, 'json')
            ->willReturn($dto);

        // Simulate validation errors
        $violations = $this->createMock(ConstraintViolationList::class);
        $violations->method('count')->willReturn(1);
        $violations->method('__toString')->willReturn('Validation error');

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($dto)
            ->willReturn($violations);

        $response = $this->controller->create($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('Validation error', $response->getContent());
    }

    public function testUpdateReturnsUpdatedCategoryOnValidData()
    {
        $jsonInput = '{"name":"Updated Category"}';
        $dto = new UpdateCategoryDto();
        $category = new Category();
        $updatedCategory = new Category();
        $jsonOutput = '{"id":1,"name":"Updated Category"}';

        $request = new Request([], [], [], [], [], [], $jsonInput);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($jsonInput, UpdateCategoryDto::class, 'json')
            ->willReturn($dto);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($dto)
            ->willReturn(new ConstraintViolationList());

        $this->categoryService->expects($this->once())
            ->method('update')
            ->with($category, $dto)
            ->willReturn($updatedCategory);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($updatedCategory, 'json', ['groups' => ['category:read']])
            ->willReturn($jsonOutput);

        $response = $this->controller->update($request, $category);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($jsonOutput, $response->getContent());
    }

    public function testUpdateReturnsBadRequestOnValidationErrors()
    {
        $jsonInput = '{"name":""}';
        $dto = new UpdateCategoryDto();
        $category = new Category();
        $request = new Request([], [], [], [], [], [], $jsonInput);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($jsonInput, UpdateCategoryDto::class, 'json')
            ->willReturn($dto);

        $violations = $this->createMock(ConstraintViolationList::class);
        $violations->method('count')->willReturn(1);
        $violations->method('__toString')->willReturn('Validation error');

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($dto)
            ->willReturn($violations);

        $response = $this->controller->update($request, $category);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('Validation error', $response->getContent());
    }

    public function testDeleteReturnsNoContent()
    {
        $category = new Category();

        $this->categoryService->expects($this->once())
            ->method('delete')
            ->with($category);

        $response = $this->controller->delete($category);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty($response->getContent());

    }
}
