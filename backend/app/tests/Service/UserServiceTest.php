<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Service;

use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserServiceTest.
 */
class UserServiceTest extends TestCase
{
    private UserRepository|MockObject $userRepository;
    private UserPasswordHasherInterface|MockObject $passwordHasher;
    private ValidatorInterface|MockObject $validator;
    private UserService $userService;

    // ----------------------
    // Setup
    // ----------------------

    /**
     * Sets up the test environment.
     *
     * @test
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->userService = new UserService(
            $this->userRepository,
            $this->passwordHasher,
            $this->validator
        );
    }

    // ----------------------
    // Create
    // ----------------------

    /**
     * Test successful creation of a user.
     *
     * @test
     */
    public function testCreateUserSuccess(): void
    {
        $dto = new CreateUserDto();
        $dto->email = 'test@example.com';
        $dto->nickname = 'tester';
        $dto->plainPassword = 'password123';

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(User::class), 'password123')
            ->willReturn('hashed_password');

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (User $user) => $user->getEmail() === $dto->email
                && $user->getNickname() === $dto->nickname
            && 'hashed_password' === $user->getPassword()));

        $user = $this->userService->createUser($dto);

        $this->assertSame($dto->email, $user->getEmail());
        $this->assertSame($dto->nickname, $user->getNickname());
        $this->assertSame('hashed_password', $user->getPassword());
    }

    /**
     * Test that validation errors during user creation throw exception.
     *
     * @test
     *
     * @throws Exception
     */
    public function testCreateUserValidationFails(): void
    {
        $dto = new CreateUserDto();
        $dto->email = 'invalid-email';
        $dto->nickname = '';
        $dto->plainPassword = '123';

        $violation = $this->createMock(ConstraintViolation::class);
        $violations = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->expectException(\InvalidArgumentException::class);

        $this->userService->createUser($dto);
    }

    // ----------------------
    // Update
    // ----------------------

    /**
     * Test successful update of a user.
     *
     * @test
     */
    public function testUpdateUserSuccess(): void
    {
        $user = new User();
        $user->setEmail('old@example.com');
        $user->setNickname('oldnick');
        $user->setPassword('oldhash');

        $dto = new UpdateUserDto();
        $dto->email = 'new@example.com';
        $dto->nickname = 'newnick';
        $dto->plainPassword = 'newpassword';

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'newpassword')
            ->willReturn('newhash');

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $updatedUser = $this->userService->updateUser($user, $dto);

        $this->assertSame('new@example.com', $updatedUser->getEmail());
        $this->assertSame('newnick', $updatedUser->getNickname());
        $this->assertSame('newhash', $updatedUser->getPassword());
    }

    /**
     * Test that validation errors during user update throw exception.
     *
     * @test
     *
     * @throws Exception
     */
    public function testUpdateUserValidationFails(): void
    {
        $user = new User();

        $dto = new UpdateUserDto();
        $dto->email = 'invalid-email';

        $violation = $this->createMock(ConstraintViolation::class);
        $violations = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->expectException(\InvalidArgumentException::class);

        $this->userService->updateUser($user, $dto);
    }

    // ----------------------
    // Delete
    // ----------------------

    /**
     * Test that deleteUser calls repository delete method.
     *
     * @test
     */
    public function testDeleteUserCallsRepository(): void
    {
        $user = new User();

        $this->userRepository->expects($this->once())
            ->method('delete')
            ->with($user);

        $this->userService->deleteUser($user);
    }

    // ----------------------
    // Find
    // ----------------------

    /**
     * Test findUserByEmailOrFail returns a user when found.
     *
     * @test
     */
    public function testFindUserByEmailOrFailFound(): void
    {
        $user = new User();
        $email = 'found@example.com';

        $this->userRepository->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn($user);

        $result = $this->userService->findUserByEmailOrFail($email);

        $this->assertSame($user, $result);
    }

    /**
     * Test findUserByEmailOrFail throws exception when user not found.
     *
     * @test
     */
    public function testFindUserByEmailOrFailNotFound(): void
    {
        $email = 'notfound@example.com';

        $this->userRepository->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->userService->findUserByEmailOrFail($email);
    }

    public function testGetUsersReturnsPaginatedData(): void
    {
        $page = 2;
        $limit = 10;
        $search = 'term';

        // Mock the repository
        $repositoryMock = $this->createMock(UserRepository::class);

        $expectedResult = [
            'items' => [new User(), new User()],
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => 3,
                'totalItems' => 25,
                'limit' => $limit,
            ],
        ];

        $repositoryMock->expects($this->once())
            ->method('findAllPaginated')
            ->with($page, $limit, $search)
            ->willReturn($expectedResult);

        $service = new UserService($repositoryMock, $this->createMock(UserPasswordHasherInterface::class), $this->createMock(ValidatorInterface::class));

        $result = $service->getUsers($page, $limit, $search);

        $this->assertSame($expectedResult, $result);
    }
}
