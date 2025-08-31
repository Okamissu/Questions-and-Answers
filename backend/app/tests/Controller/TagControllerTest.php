<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Controller;

use App\Controller\TagController;
use App\Dto\CreateTagDto;
use App\Dto\ListFiltersDto;
use App\Dto\UpdateTagDto;
use App\Entity\Tag;
use App\Service\TagServiceInterface;
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
 * Class TagControllerTest.
 *
 * Tests CRUD operations for TagController.
 *
 * @covers \App\Controller\TagController
 */
class TagControllerTest extends TestCase
{
    private TagServiceInterface|MockObject $serviceMock;
    private ValidatorInterface|MockObject $validatorMock;
    private SerializerInterface|MockObject $serializerMock;
    private TagController|MockObject $controller;

    /**
     * Sets up mocks and controller before each test.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(TagServiceInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        $this->controller = $this->getMockBuilder(TagController::class)
            ->onlyMethods(['denyAccessUnlessGranted', 'json'])
            ->setConstructorArgs([$this->serviceMock, $this->serializerMock, $this->validatorMock])
            ->getMock();

        $this->controller->method('denyAccessUnlessGranted')
            ->willReturnCallback(fn () => null);

        $this->controller->method('json')->willReturnCallback(function ($data, $status = 200) {
            if ($data instanceof Tag) {
                $data = ['name' => $data->getName()];
            }

            return new JsonResponse($data, $status);
        });
    }

    /**
     * Tests listing tags with pagination.
     */
    public function testList(): void
    {
        $filters = new ListFiltersDto();
        $filters->limit = 10;
        $filters->search = null;
        $filters->sort = null;
        $page = 1;

        $this->serviceMock->method('getPaginatedList')->willReturn([
            'items' => [['id' => 1, 'name' => 'Tag1']],
            'totalItems' => 1,
        ]);

        $response = $this->controller->list($filters, $page);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(1, $data['pagination']['totalItems']);
        $this->assertEquals(1, $data['pagination']['page']);
        $this->assertEquals('Tag1', $data['items'][0]['name']);
    }

    /**
     * Tests showing a single tag.
     *
     * @throws ExceptionInterface
     */
    public function testShow(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $this->serializerMock->method('serialize')
            ->willReturn(json_encode(['name' => $tag->getName()]));

        $response = $this->controller->show($tag);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('Test Tag', $data['name']);
    }

    /**
     * Tests creating a new tag successfully.
     *
     * @throws ExceptionInterface
     */
    public function testCreate(): void
    {
        $dto = new CreateTagDto();
        $tag = new Tag();
        $tag->setName('New Tag');

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $this->serviceMock->method('create')->willReturn($tag);
        $this->serializerMock->method('serialize')->willReturn(json_encode(['name' => $tag->getName()]));

        $request = new Request([], [], [], [], [], [], json_encode(['name' => 'New Tag']));
        $response = $this->controller->create($request);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('New Tag', $data['name']);
        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * Tests creating a tag with validation errors.
     *
     * @throws ExceptionInterface
     */
    public function testCreateValidationError(): void
    {
        $dto = new CreateTagDto();
        $violation = new ConstraintViolation('Invalid name', '', [], '', '', '');
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList([$violation]));
        $this->serializerMock->method('deserialize')->willReturn($dto);

        $request = new Request([], [], [], [], [], [], json_encode(['name' => '']));
        $response = $this->controller->create($request);

        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Invalid name', $data['error']);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Tests updating a tag successfully.
     *
     * @throws ExceptionInterface
     */
    public function testUpdate(): void
    {
        $tag = new Tag();
        $tag->setName('Old Tag');

        $dto = new UpdateTagDto();
        $updatedTag = new Tag();
        $updatedTag->setName('Updated Tag');

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $this->serviceMock->method('update')->willReturn($updatedTag);
        $this->serializerMock->method('serialize')->willReturn(json_encode(['name' => $updatedTag->getName()]));

        $request = new Request([], [], [], [], [], [], json_encode(['name' => 'Updated Tag']));
        $response = $this->controller->update($request, $tag);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Updated Tag', $data['name']);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Tests updating a tag with validation errors.
     *
     * @throws ExceptionInterface
     */
    public function testUpdateValidationError(): void
    {
        $tag = new Tag();
        $dto = new UpdateTagDto();
        $violation = new ConstraintViolation('Invalid name', '', [], '', '', '');
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList([$violation]));
        $this->serializerMock->method('deserialize')->willReturn($dto);

        $request = new Request([], [], [], [], [], [], json_encode(['name' => '']));
        $response = $this->controller->update($request, $tag);

        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Invalid name', $data['error']);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Tests deleting a tag successfully.
     */
    public function testDelete(): void
    {
        $tag = new Tag();

        $this->serviceMock->expects($this->once())->method('delete')->with($tag);

        $response = $this->controller->delete($tag);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
