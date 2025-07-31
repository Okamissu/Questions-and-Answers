<?php

namespace App\Tests\Service;

use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserServiceTest extends TestCase
{
    private $userRepository;
    private $passwordHasher;
    private $validator;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->userService = new UserService(
            $this->userRepository,
            $this->passwordHasher,
            $this->validator,
        );
    }

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
            ->with($this->callback(function (User $user) use ($dto) {
                return
                    $user->getEmail() === $dto->email
                    && $user->getNickname() === $dto->nickname
                    && 'hashed_password' === $user->getPassword();
            }));

        $user = $this->userService->createUser($dto);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($dto->email, $user->getEmail());
        $this->assertSame($dto->nickname, $user->getNickname());
        $this->assertSame('hashed_password', $user->getPassword());
    }

    public function testCreateUserValidationFails(): void
    {
        $dto = new CreateUserDto();
        $dto->email = 'invalid-email';
        $dto->nickname = '';
        $dto->plainPassword = '123';

        // Tworzymy mock violation, bo ConstraintViolationList wymaga obiektów ConstraintViolationInterface
        $violation = $this->createMock(ConstraintViolation::class);
        $violations = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->expectException(\InvalidArgumentException::class);

        $this->userService->createUser($dto);
    }

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

    public function testDeleteUserCallsRepository(): void
    {
        $user = new User();

        $this->userRepository->expects($this->once())
            ->method('delete')
            ->with($user);

        $this->userService->deleteUser($user);
    }

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
}
