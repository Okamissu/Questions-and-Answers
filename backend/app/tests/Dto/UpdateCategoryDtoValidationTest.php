<?php

namespace App\Tests\Dto;

use App\Dto\UpdateCategoryDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateCategoryDtoValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidDtoPassesValidation(): void
    {
        $dto = new UpdateCategoryDto();
        $dto->name = 'Valid Category Name';

        $errors = $this->validator->validate($dto);

        $this->assertCount(0, $errors);
    }

    public function testEmptyNameFailsValidation(): void
    {
        $dto = new UpdateCategoryDto();
        $dto->name = '';

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    public function testTooShortNameFailsValidation(): void
    {
        $dto = new UpdateCategoryDto();
        $dto->name = 'ab';

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    public function testTooLongNameFailsValidation(): void
    {
        $dto = new UpdateCategoryDto();
        $dto->name = str_repeat('a', 256);

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too long', (string) $errors);
    }
}
