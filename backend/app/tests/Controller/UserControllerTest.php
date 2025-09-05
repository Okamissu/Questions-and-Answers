<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Controller;

use App\Controller\UserController;
use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Entity\User;
use App\Service\UserServiceInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserControllerTest.
 *
 * Tests CRUD operations and additional endpoints for UserController.
 *
 * @covers \App\Controller\UserController
 */
class UserControllerTest extends TestCase
{
    private UserServiceInterface|MockObject $serviceMock;
    private ValidatorInterface|MockObject $validatorMock;
    private SerializerInterface|MockObject $serializerMock;
    private UserController|MockObject $controller;

    /**
     * Sets up mocks and controller before each test.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->serviceMock = $this->createMock(UserServiceInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);

        $this->controller = $this->getMockBuilder(UserController::class)
            ->onlyMethods(['denyAccessUnlessGranted', 'json', 'getUser'])
            ->setConstructorArgs([$this->serviceMock, $this->validatorMock, $this->serializerMock])
            ->getMock();

        $this->controller->method('denyAccessUnlessGranted')
            ->willReturnCallback(fn () => null);

        $this->controller->method('json')->willReturnCallback(function ($data, $status = 200) {
            if ($data instanceof User) {
                $data = ['email' => $data->getEmail() ?? 'user@example.com'];
            }

            return new JsonResponse($data, $status);
        });
    }

    /**
     * Tests creating a new user successfully.
     *
     * @throws \Exception
     */
    public function testCreate(): void
    {
        $dto = new CreateUserDto();
        $user = new User();
        $user->setEmail('test@example.com');

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $this->serviceMock->method('createUser')->willReturn($user);
        $this->serializerMock->method('serialize')->willReturn(json_encode(['email' => $user->getEmail()]));

        $request = new Request([], [], [], [], [], [], json_encode(['email' => 'test@example.com']));
        $response = $this->controller->create($request);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('test@example.com', $data['email']);
        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * Tests creating a user with validation errors.
     *
     * @throws \Exception
     */
    public function testCreateValidationError(): void
    {
        $dto = new CreateUserDto();
        $violation = new ConstraintViolation('Invalid email', '', [], '', '', '');
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList([$violation]));
        $this->serializerMock->method('deserialize')->willReturn($dto);

        $request = new Request([], [], [], [], [], [], json_encode(['email' => '']));
        $response = $this->controller->create($request);

        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Invalid email', $data['errors'][0]['message']);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Tests showing a single user.
     *
     * @throws \Exception
     */
    public function testShow(): void
    {
        $user = new User();
        $user->setEmail('show@example.com');

        $this->serializerMock->method('serialize')->willReturn(json_encode(['email' => $user->getEmail()]));

        $response = $this->controller->show($user);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('show@example.com', $data['email']);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Tests updating a user successfully.
     *
     * @throws \Exception
     */
    public function testUpdate(): void
    {
        $user = new User();
        $user->setEmail('old@example.com');

        $dto = new UpdateUserDto();
        $updatedUser = new User();
        $updatedUser->setEmail('updated@example.com');

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $this->serviceMock->method('updateUser')->willReturn($updatedUser);
        $this->serializerMock->method('serialize')->willReturn(json_encode(['email' => $updatedUser->getEmail()]));

        $request = new Request([], [], [], [], [], [], json_encode(['email' => 'updated@example.com']));
        $response = $this->controller->update($request, $user);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('updated@example.com', $data['email']);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Tests updating a user with validation errors.
     *
     * @throws \Exception
     */
    public function testUpdateValidationError(): void
    {
        $user = new User();
        $dto = new UpdateUserDto();
        $violation = new ConstraintViolation('Invalid email', '', [], '', '', '');
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList([$violation]));
        $this->serializerMock->method('deserialize')->willReturn($dto);

        $request = new Request([], [], [], [], [], [], json_encode(['email' => '']));
        $response = $this->controller->update($request, $user);

        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Invalid email', $data['errors'][0]['message']);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Tests deleting a user successfully.
     *
     * @throws \Exception
     */
    public function testDelete(): void
    {
        $user = new User();

        $this->serviceMock->expects($this->once())->method('deleteUser')->with($user);

        $response = $this->controller->delete($user);
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * Tests that creating a user throws InvalidArgumentException.
     *
     * @throws \Exception
     */
    public function testCreateThrowsInvalidArgumentException(): void
    {
        $dto = new CreateUserDto();

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $this->serviceMock->method('createUser')
            ->willThrowException(new \InvalidArgumentException('User already exists'));

        $request = new Request([], [], [], [], [], [], json_encode(['email' => 'test@example.com']));
        $response = $this->controller->create($request);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User already exists', $data['error']);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Tests that updating a user throws InvalidArgumentException.
     *
     * @throws \Exception
     */
    public function testUpdateThrowsInvalidArgumentException(): void
    {
        $user = new User();
        $dto = new UpdateUserDto();

        $this->serializerMock->method('deserialize')->willReturn($dto);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $this->serviceMock->method('updateUser')
            ->willThrowException(new \InvalidArgumentException('Cannot update user'));

        $request = new Request([], [], [], [], [], [], json_encode(['email' => 'update@example.com']));
        $response = $this->controller->update($request, $user);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Cannot update user', $data['error']);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Tests listing users with default pagination.
     *
     * @throws \Exception
     */
    public function testListDefaultPagination(): void
    {
        $expectedData = ['users' => [['email' => 'u1@example.com']]];
        $this->serviceMock->expects($this->once())
            ->method('getUsers')
            ->with(1, 20, null)
            ->willReturn($expectedData);

        $this->serializerMock->method('serialize')->willReturn(json_encode($expectedData));

        $request = new Request(); // no query params → defaults
        $response = $this->controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(json_encode($expectedData), $response->getContent());
    }

    /**
     * Tests listing users with custom pagination and search.
     *
     * @throws \Exception
     */
    public function testListCustomPaginationAndSearch(): void
    {
        $expectedData = ['users' => [['email' => 'search@example.com']]];
        $this->serviceMock->expects($this->once())
            ->method('getUsers')
            ->with(2, 50, 'abc')
            ->willReturn($expectedData);

        $this->serializerMock->method('serialize')->willReturn(json_encode($expectedData));

        $request = new Request(['page' => 2, 'limit' => 50, 'search' => 'abc']);
        $response = $this->controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(json_encode($expectedData), $response->getContent());
    }

    /**
     * Tests listing users clamps limit to max 100.
     *
     * @throws \Exception
     */
    public function testListClampsLimit(): void
    {
        $this->serviceMock->expects($this->once())
            ->method('getUsers')
            ->with(1, 100, null)
            ->willReturn(['ok' => true]);

        $this->serializerMock->method('serialize')->willReturn(json_encode(['ok' => true]));

        $request = new Request(['limit' => 999]); // too big
        $response = $this->controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Tests "me" endpoint returns 401 when no authenticated user.
     *
     * @throws \Exception
     */
    public function testMeNotAuthenticated(): void
    {
        $this->controller->method('getUser')->willReturn(null);

        $response = $this->controller->me();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('Not authenticated', $data['error']);
    }

    /**
     * Tests "me" endpoint returns authenticated user data.
     *
     * @throws \Exception
     */
    public function testMeAuthenticated(): void
    {
        $user = new User();
        $user->setEmail('me@example.com');

        $this->controller->method('getUser')->willReturn($user);
        $this->serializerMock->method('serialize')->willReturn(json_encode(['email' => $user->getEmail()]));

        $response = $this->controller->me();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('me@example.com', $data['email']);
    }
}
