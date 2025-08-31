<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Dto;

use App\Dto\UpdateUserDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UpdateUserDtoValidationTest.
 *
 * Tests validation rules for UpdateUserDto.
 */
class UpdateUserDtoValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    /**
     * Boot kernel and get validator service.
     *
     * @throws \Exception
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * Test valid DTO passes validation.
     *
     * @throws \Exception
     */
    public function testValidDtoPassesValidation(): void
    {
        $dto = new UpdateUserDto();
        $dto->email = 'valid@example.com';
        $dto->plainPassword = 'securePassword123';
        $dto->nickname = 'ValidNick';

        $errors = $this->validator->validate($dto);
        $this->assertCount(0, $errors);
    }

    /**
     * Test all null fields pass validation (optional fields).
     *
     * @throws \Exception
     */
    public function testNullFieldsPassValidation(): void
    {
        $dto = new UpdateUserDto();
        $errors = $this->validator->validate($dto);
        $this->assertCount(0, $errors);
    }

    /**
     * Test invalid email fails validation.
     *
     * @throws \Exception
     */
    public function testInvalidEmailFailsValidation(): void
    {
        $dto = new UpdateUserDto();
        $dto->email = 'not-an-email';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is not a valid email address', (string) $errors);
    }

    /**
     * Test too short password fails validation.
     *
     * @throws \Exception
     */
    public function testTooShortPasswordFailsValidation(): void
    {
        $dto = new UpdateUserDto();
        $dto->plainPassword = 'short';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    /**
     * Test too long password fails validation.
     *
     * @throws \Exception
     */
    public function testTooLongPasswordFailsValidation(): void
    {
        $dto = new UpdateUserDto();
        $dto->plainPassword = str_repeat('a', 5000);

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too long', (string) $errors);
    }

    /**
     * Test too short nickname fails validation.
     *
     * @throws \Exception
     */
    public function testTooShortNicknameFailsValidation(): void
    {
        $dto = new UpdateUserDto();
        $dto->nickname = 'ab';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    /**
     * Test too long nickname fails validation.
     *
     * @throws \Exception
     */
    public function testTooLongNicknameFailsValidation(): void
    {
        $dto = new UpdateUserDto();
        $dto->nickname = str_repeat('a', 300);

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too long', (string) $errors);
    }
}
