<?php

namespace App\Tests\Dto;

use App\Dto\UpdateAnswerDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateAnswerDtoValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidDtoPassesValidation(): void
    {
        $dto = new UpdateAnswerDto();
        $dto->content = 'This is a valid updated answer content.';
        $dto->isBest = true;

        $errors = $this->validator->validate($dto);

        $this->assertCount(0, $errors);
    }

    public function testEmptyContentFailsValidation(): void
    {
        $dto = new UpdateAnswerDto();
        $dto->content = '';

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    public function testShortContentFailsValidation(): void
    {
        $dto = new UpdateAnswerDto();
        $dto->content = 'short';

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    public function testNullContentFailsValidation(): void
    {
        $dto = new UpdateAnswerDto();
        $dto->content = null;

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        // NotBlank should catch null as well
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }
}
