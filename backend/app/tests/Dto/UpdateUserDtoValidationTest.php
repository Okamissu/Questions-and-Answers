<?php

namespace App\Tests\Dto;

use App\Dto\UpdateUserDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateUserDtoValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidDtoPassesValidation(): void
    {
        $dto = new UpdateUserDto();
        $dto->email = 'valid@example.com';
        $dto->plainPassword = 'securePassword123';
        $dto->nickname = 'ValidNick';

        $errors = $this->validator->validate($dto);
        $this->assertCount(0, $errors);
    }

    public function testNullFieldsPassValidation(): void
    {
        $dto = new UpdateUserDto();
        // Wszystkie pola null, bo są opcjonalne
        $errors = $this->validator->validate($dto);
        $this->assertCount(0, $errors);
    }

    public function testInvalidEmailFailsValidation(): void
    {
        $dto = new UpdateUserDto();
        $dto->email = 'not-an-email';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is not a valid email address', (string) $errors);
    }

    public function testTooShortPasswordFailsValidation(): void
    {
        $dto = new UpdateUserDto();
        $dto->plainPassword = 'short';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    public function testTooLongPasswordFailsValidation(): void
    {
        $dto = new UpdateUserDto();
        $dto->plainPassword = str_repeat('a', 5000);

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too long', (string) $errors);
    }

    public function testTooShortNicknameFailsValidation(): void
    {
        $dto = new UpdateUserDto();
        $dto->nickname = 'ab';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    public function testTooLongNicknameFailsValidation(): void
    {
        $dto = new UpdateUserDto();
        $dto->nickname = str_repeat('a', 300);

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too long', (string) $errors);
    }
}
