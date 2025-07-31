<?php

namespace App\Tests\Dto;

use App\Dto\CreateUserDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserDtoValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidDtoPassesValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->email = 'user@example.com';
        $dto->plainPassword = 'strongPassword123';
        $dto->nickname = 'UserNick';

        $errors = $this->validator->validate($dto);
        $this->assertCount(0, $errors);
    }

    public function testBlankEmailFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->email = '';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    public function testInvalidEmailFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->email = 'invalid-email';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is not a valid email address', (string) $errors);
    }

    public function testBlankPasswordFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->plainPassword = '';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    public function testTooShortPasswordFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->plainPassword = '123';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    public function testBlankNicknameFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->nickname = '';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    public function testTooShortNicknameFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->nickname = 'ab';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    public function testTooLongNicknameFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->nickname = str_repeat('a', 300);

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too long', (string) $errors);
    }
}
