<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Dto;

use App\Dto\CreateCategoryDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CreateCategoryDtoValidationTest.
 *
 * Tests validation rules for CreateCategoryDto.
 */
class CreateCategoryDtoValidationTest extends KernelTestCase
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
        $dto = new CreateCategoryDto();
        $dto->name = 'Valid Category Name';

        $errors = $this->validator->validate($dto);

        $this->assertCount(0, $errors);
    }

    /**
     * Tests that an empty name fails validation.
     *
     * @throws \Exception
     */
    public function testEmptyNameFailsValidation(): void
    {
        $dto = new CreateCategoryDto();
        $dto->name = '';

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    /**
     * Tests that too short name fails validation.
     *
     * @throws \Exception
     */
    public function testTooShortNameFailsValidation(): void
    {
        $dto = new CreateCategoryDto();
        $dto->name = 'ab';

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    /**
     * Tests that too long name fails validation.
     *
     * @throws \Exception
     */
    public function testTooLongNameFailsValidation(): void
    {
        $dto = new CreateCategoryDto();
        $dto->name = str_repeat('a', 256);

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too long', (string) $errors);
    }
}
