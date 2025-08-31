<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Controller;

use App\Controller\QuestionController;
use App\Dto\CreateQuestionDto;
use App\Dto\QuestionListFiltersDto;
use App\Dto\UpdateQuestionDto;
use App\Entity\Question;
use App\Entity\User;
use App\Service\QuestionServiceInterface;
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
 * Class QuestionControllerTest.
 *
 * Tests CRUD operations for QuestionController.
 *
 * @covers \App\Controller\QuestionController
 */
class QuestionControllerTest extends TestCase
{
    private QuestionServiceInterface|MockObject $serviceMock;
    private ValidatorInterface|MockObject $validatorMock;
    private SerializerInterface|MockObject $serializerMock;
    private QuestionController|MockObject $controller;

    /**
     * Sets up mocks and controller before each test.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(QuestionServiceInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        $this->controller = $this->getMockBuilder(QuestionController::class)
            ->onlyMethods(['getUser', 'denyAccessUnlessGranted', 'json'])
            ->setConstructorArgs([$this->serviceMock, $this->validatorMock, $this->serializerMock])
            ->getMock();

        $this->controller->method('json')
            ->willReturnCallback(fn ($data, $status = 200) => new JsonResponse($data, $status));

        $this->controller->method('denyAccessUnlessGranted')
            ->willReturnCallback(fn () => null);
    }

    /**
     * Tests listing questions with pagination.
     */
    public function testList(): void
    {
        $filters = new QuestionListFiltersDto();
        $filters->search = null;
        $filters->sort = null;
        $filters->limit = 10;
        $filters->categoryId = 1;

        $this->serviceMock->method('getPaginatedList')->willReturn([
            'items' => [['id' => 1, 'title' => 'Test Q']],
            'totalItems' => 1,
        ]);

        $response = $this->controller->list($filters, 1);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(1, $data['pagination']['totalItems']);
    }

    /**
     * Tests showing a single question.
     *
     * @throws ExceptionInterface
     */
    public function testShow(): void
    {
        $question = new Question();
        $question->setTitle('Show Q');

        $this->serializerMock->method('serialize')->willReturn(json_encode(['title' => 'Show Q']));

        $response = $this->controller->show($question);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('Show Q', $data['title']);
    }

    /**
     * Tests creating a new question successfully.
     *
     * @throws ExceptionInterface
     */
    public function testCreate(): void
    {
        $user = new User();
        $dto = new CreateQuestionDto();

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());

        $question = new Question();
        $question->setTitle('Created Q');

        $this->serviceMock->method('create')->willReturn($question);
        $this->serializerMock->method('serialize')->willReturn(json_encode(['title' => 'Created Q']));

        $this->controller->method('getUser')->willReturn($user);

        $request = new Request([], [], [], [], [], [], json_encode(['title' => 'Created Q']));
        $response = $this->controller->create($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('Created Q', $data['title']);
    }

    /**
     * Tests creating a question when user is not authenticated.
     *
     * @throws ExceptionInterface
     */
    public function testCreateNotAuthenticated(): void
    {
        $this->controller->method('getUser')->willReturn(null);

        $request = new Request([], [], [], [], [], [], json_encode(['title' => 'Some Q']));
        $response = $this->controller->create($request);

        $this->assertEquals(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Not authenticated', $data['error']);
    }

    /**
     * Tests creating a question with validation errors.
     *
     * @throws ExceptionInterface
     */
    public function testCreateValidationError(): void
    {
        $user = new User();
        $dto = new CreateQuestionDto();

        $violation = new ConstraintViolation('Invalid title', '', [], '', '', '');
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList([$violation]));
        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->controller->method('getUser')->willReturn($user);

        $request = new Request([], [], [], [], [], [], json_encode(['title' => '']));
        $response = $this->controller->create($request);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Invalid title', $data['error']);
    }

    /**
     * Tests updating a question successfully.
     *
     * @throws ExceptionInterface
     */
    public function testUpdate(): void
    {
        $user = new User();
        $question = new Question();
        $question->setAuthor($user);

        $dto = new UpdateQuestionDto();
        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $this->serviceMock->method('update')->willReturn($question);
        $this->serializerMock->method('serialize')->willReturn(json_encode(['title' => 'Updated Q']));

        $this->controller->method('getUser')->willReturn($user);

        $request = new Request([], [], [], [], [], [], json_encode(['title' => 'Updated Q']));
        $response = $this->controller->update($request, $question);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Updated Q', $data['title']);
    }

    /**
     * Tests updating a question with validation errors.
     *
     * @throws ExceptionInterface
     */
    public function testUpdateValidationError(): void
    {
        $user = new User();
        $question = new Question();
        $question->setAuthor($user);
        $dto = new UpdateQuestionDto();

        $violation = new ConstraintViolation('Invalid title', '', [], '', '', '');
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList([$violation]));
        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->controller->method('getUser')->willReturn($user);

        $request = new Request([], [], [], [], [], [], json_encode(['title' => '']));
        $response = $this->controller->update($request, $question);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Invalid title', $data['error']);
    }

    /**
     * Tests deleting a question successfully.
     */
    public function testDelete(): void
    {
        $user = new User();
        $question = new Question();
        $question->setAuthor($user);

        $this->controller->method('getUser')->willReturn($user);
        $this->serviceMock->expects($this->once())->method('delete')->with($question);

        $response = $this->controller->delete($question);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
