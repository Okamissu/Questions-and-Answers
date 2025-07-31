<?php

namespace App\Tests\Dto;

use App\Dto\UpdateTagDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateTagDtoValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidDtoPassesValidation(): void
    {
        $dto = new UpdateTagDto();
        $dto->name = 'Valid Tag Name';

        $errors = $this->validator->validate($dto);

        $this->assertCount(0, $errors);
    }

    public function testEmptyNameFailsValidation(): void
    {
        $dto = new UpdateTagDto();
        $dto->name = '';

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    public function testTooShortNameFailsValidation(): void
    {
        $dto = new UpdateTagDto();
        $dto->name = 'a';

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    public function testTooLongNameFailsValidation(): void
    {
        $dto = new UpdateTagDto();
        $dto->name = str_repeat('a', 51);

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too long', (string) $errors);
    }
}
