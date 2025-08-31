<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Controller;

use App\Controller\AnswerController;
use App\Dto\CreateAnswerDto;
use App\Dto\ListFiltersDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Service\AnswerServiceInterface;
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
 * Class AnswerControllerTest.
 *
 * Tests the AnswerController endpoints including list, show, create, update, delete, and markAsBest.
 *
 * @covers \App\Controller\AnswerController
 */
class AnswerControllerTest extends TestCase
{
    private AnswerServiceInterface|MockObject $serviceMock;
    private ValidatorInterface|MockObject $validatorMock;
    private SerializerInterface|MockObject $serializerMock;
    private AnswerController|MockObject $controller;

    /**
     * Sets up mocks and the controller before each test.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(AnswerServiceInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        // Mock controller
        $this->controller = $this->getMockBuilder(AnswerController::class)
            ->onlyMethods(['getUser', 'denyAccessUnlessGranted', 'json'])
            ->setConstructorArgs([$this->serviceMock, $this->validatorMock, $this->serializerMock])
            ->getMock();

        $this->controller->method('json')->willReturnCallback(function ($data, $status = 200) {
            if ($data instanceof Answer) {
                // mimic serialization
                $data = ['content' => $data->getContent()];
            }

            return new JsonResponse($data, $status);
        });

        // Mock denyAccessUnlessGranted to do nothing
        $this->controller->method('denyAccessUnlessGranted')
            ->willReturnCallback(function () {
                /* do nothing */
            });
    }

    /**
     * Tests that the list endpoint returns paginated answers correctly.
     */
    public function testList(): void
    {
        $question = new Question();
        $reflection = new \ReflectionClass($question);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($question, 1);

        $filters = new ListFiltersDto();
        $filters->limit = 10;
        $filters->search = null;
        $filters->sort = null;
        $page = 1;

        $this->serviceMock->method('getPaginatedList')->with(
            $page,
            $filters->limit,
            $question,
            $filters->search,
            $filters->sort
        )->willReturn([
            'items' => [['id' => 1, 'content' => 'Test Answer']],
            'totalItems' => 1,
        ]);

        $response = $this->controller->list($question, $filters, $page);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(1, $data['pagination']['totalItems']);
        $this->assertEquals(1, $data['pagination']['questionId']);
        $this->assertEquals(10, $data['pagination']['limit']);
        $this->assertEquals([['id' => 1, 'content' => 'Test Answer']], $data['items']);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Tests that the show endpoint returns a single answer.
     */
    public function testShow(): void
    {
        $answer = new Answer();
        $answer->setContent('Show Answer');

        $response = $this->controller->show($answer);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('Show Answer', $data['content']);
    }

    /**
     * Tests that create endpoint successfully creates an answer for authenticated user.
     *
     * @throws ExceptionInterface
     */
    public function testCreate(): void
    {
        $user = new User();
        $dto = new CreateAnswerDto();

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());

        $answer = new Answer();
        $answer->setContent('Created Answer');
        $this->serviceMock->method('create')->willReturn($answer);

        $this->controller->method('getUser')->willReturn($user);

        $request = new Request([], [], [], [], [], [], json_encode(['content' => 'Created Answer']));
        $response = $this->controller->create($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('Created Answer', $data['content']);
    }

    /**
     * Tests that create endpoint works for anonymous user with nickname and email.
     *
     * @throws ExceptionInterface
     */
    public function testCreateNotAuthenticatedWithAnonymousData(): void
    {
        $this->controller->method('getUser')->willReturn(null);

        $dto = new CreateAnswerDto();
        $dto->authorNickname = 'Anon';
        $dto->authorEmail = 'anon@example.com';

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());

        $request = new Request([], [], [], [], [], [], json_encode(['content' => 'Anonymous Answer']));
        $response = $this->controller->create($request);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * Tests that create endpoint returns 400 when validation fails.
     *
     * @throws ExceptionInterface
     */
    public function testCreateValidationError(): void
    {
        $user = new User();
        $dto = new CreateAnswerDto();
        $violation = new ConstraintViolation('Invalid content', '', [], '', '', '');

        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList([$violation]));
        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->controller->method('getUser')->willReturn($user);

        $request = new Request([], [], [], [], [], [], json_encode(['content' => '']));
        $response = $this->controller->create($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid content', $data['error']);
    }

    /**
     * Tests that update endpoint successfully updates an answer.
     *
     * @throws ExceptionInterface
     */
    public function testUpdate(): void
    {
        $user = new User();
        $answer = new Answer();
        $answer->setContent('Updated Answer');

        $dto = new UpdateAnswerDto();
        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $this->serviceMock->method('update')->willReturn($answer);

        $this->controller->method('getUser')->willReturn($user);

        $request = new Request([], [], [], [], [], [], json_encode(['content' => 'Updated Answer']));
        $response = $this->controller->update($request, $answer);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('Updated Answer', $data['content']);
    }

    /**
     * Tests that update endpoint returns 400 when validation fails.
     *
     * @throws ExceptionInterface
     */
    public function testUpdateValidationError(): void
    {
        $user = new User();
        $answer = new Answer();
        $dto = new UpdateAnswerDto();
        $violation = new ConstraintViolation('Invalid content', '', [], '', '', '');

        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList([$violation]));
        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->controller->method('getUser')->willReturn($user);

        $request = new Request([], [], [], [], [], [], json_encode(['content' => '']));
        $response = $this->controller->update($request, $answer);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid content', $data['error']);
    }

    /**
     * Tests that delete endpoint deletes an answer and returns 204.
     */
    public function testDelete(): void
    {
        $user = new User();
        $answer = new Answer();

        $this->controller->method('getUser')->willReturn($user);
        $this->serviceMock->expects($this->once())->method('delete')->with($answer);

        $response = $this->controller->delete($answer);
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * Tests that markAsBest endpoint marks an answer as best and returns 200.
     */
    public function testMarkAsBest(): void
    {
        $user = new User();
        $answer = new Answer();

        $this->controller->method('getUser')->willReturn($user);
        $this->serviceMock->method('markAsBest')->willReturn($answer);

        $response = $this->controller->markAsBest($answer);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Tests that create endpoint returns 400 if anonymous user submits answer without nickname or email.
     *
     * @throws ExceptionInterface
     */
    public function testCreateAnonymousMissingNicknameOrEmail(): void
    {
        $this->controller->method('getUser')->willReturn(null);

        $dto = new CreateAnswerDto();
        $dto->authorNickname = '';
        $dto->authorEmail = '';

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());

        $request = new Request([], [], [], [], [], [], json_encode(['content' => 'Anonymous Answer']));
        $response = $this->controller->create($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Nickname and email are required for anonymous answers', $data['error']);
    }
}
