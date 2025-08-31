<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Dto;

use App\Dto\CreateUserDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CreateUserDtoValidationTest.
 *
 * Tests validation rules for CreateUserDto.
 */
class CreateUserDtoValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    /**
     * Boot the kernel and get validator service.
     *
     * @throws \Exception
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * Tests that a valid DTO passes validation.
     *
     * @throws \Exception
     */
    public function testValidDtoPassesValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->email = 'user@example.com';
        $dto->plainPassword = 'strongPassword123';
        $dto->nickname = 'UserNick';

        $errors = $this->validator->validate($dto);
        $this->assertCount(0, $errors);
    }

    /**
     * Tests that blank email fails validation.
     *
     * @throws \Exception
     */
    public function testBlankEmailFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->email = '';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    /**
     * Tests that an invalid email fails validation.
     *
     * @throws \Exception
     */
    public function testInvalidEmailFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->email = 'invalid-email';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is not a valid email address', (string) $errors);
    }

    /**
     * Tests that blank password fails validation.
     *
     * @throws \Exception
     */
    public function testBlankPasswordFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->plainPassword = '';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    /**
     * Tests that a too short password fails validation.
     *
     * @throws \Exception
     */
    public function testTooShortPasswordFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->plainPassword = '123';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    /**
     * Tests that blank nickname fails validation.
     *
     * @throws \Exception
     */
    public function testBlankNicknameFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->nickname = '';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    /**
     * Tests that a too short nickname fails validation.
     *
     * @throws \Exception
     */
    public function testTooShortNicknameFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->nickname = 'ab';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    /**
     * Tests that a too long nickname fails validation.
     *
     * @throws \Exception
     */
    public function testTooLongNicknameFailsValidation(): void
    {
        $dto = new CreateUserDto();
        $dto->nickname = str_repeat('a', 300);

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too long', (string) $errors);
    }
}
