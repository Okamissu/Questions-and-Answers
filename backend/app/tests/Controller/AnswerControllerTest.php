<?php

namespace App\Tests\Controller;

use App\Controller\AnswerController;
use App\Dto\ListFiltersDto;
use App\Dto\CreateAnswerDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Service\AnswerServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AnswerControllerTest extends TestCase
{
    private AnswerServiceInterface|MockObject $serviceMock;
    private ValidatorInterface|MockObject $validatorMock;
    private SerializerInterface|MockObject $serializerMock;
    private AnswerController|MockObject $controller;

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

    public function testList(): void
    {
        // Prepare Question entity
        $question = new Question();
        $reflection = new \ReflectionClass($question);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($question, 1);

        // Prepare DTO manually
        $filters = new ListFiltersDto();
        $filters->limit = 10;
        $filters->search = null;
        $filters->sort = null;

        $page = 1;

        // Mock the service
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

        // Call the controller method
        $response = $this->controller->list($question, $filters, $page);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(1, $data['pagination']['totalItems']);
        $this->assertEquals(1, $data['pagination']['questionId']);
        $this->assertEquals(10, $data['pagination']['limit']);
        $this->assertEquals([['id' => 1, 'content' => 'Test Answer']], $data['items']);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShow(): void
    {
        $answer = new Answer();
        $answer->setContent('Show Answer');

        $response = $this->controller->show($answer);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Show Answer', $data['content']);
    }

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

    public function testCreateNotAuthenticatedWithAnonymousData(): void
    {
        // Use the same controller mock with json() already mocked
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

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Invalid content', $data['error']);
    }

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

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Invalid content', $data['error']);
    }

    public function testDelete(): void
    {
        $user = new User();
        $answer = new Answer();

        $this->controller->method('getUser')->willReturn($user);

        $this->serviceMock->expects($this->once())->method('delete')->with($answer);

        $response = $this->controller->delete($answer);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testMarkAsBest(): void
    {
        $user = new User();
        $answer = new Answer();

        $this->controller->method('getUser')->willReturn($user);
        $this->serviceMock->method('markAsBest')->willReturn($answer);

        $response = $this->controller->markAsBest($answer);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateAnonymousMissingNicknameOrEmail(): void
    {
        // Anonymous user
        $this->controller->method('getUser')->willReturn(null);

        $dto = new CreateAnswerDto();
        // Missing nickname and email
        $dto->authorNickname = '';
        $dto->authorEmail = '';

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());

        $request = new Request([], [], [], [], [], [], json_encode(['content' => 'Anonymous Answer']));
        $response = $this->controller->create($request);

        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Nickname and email are required for anonymous answers', $data['error']);
    }
}
