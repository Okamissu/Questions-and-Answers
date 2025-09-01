<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Dto;

use App\Dto\UpdateAnswerDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UpdateAnswerDtoValidationTest.
 *
 * Tests validation rules for UpdateAnswerDto.
 */
class UpdateAnswerDtoValidationTest extends KernelTestCase
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
        $dto = new UpdateAnswerDto();
        $dto->content = 'This is a valid updated answer content.';
        $dto->isBest = true;

        $errors = $this->validator->validate($dto);
        $this->assertCount(0, $errors);
    }

    /**
     * Tests that empty content fails validation.
     *
     * @throws \Exception
     */
    public function testEmptyContentFailsValidation(): void
    {
        $dto = new UpdateAnswerDto();
        $dto->content = '';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    /**
     * Tests that too short content fails validation.
     *
     * @throws \Exception
     */
    public function testShortContentFailsValidation(): void
    {
        $dto = new UpdateAnswerDto();
        $dto->content = 'short';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    /**
     * Tests that null content fails validation.
     *
     * @throws \Exception
     */
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
