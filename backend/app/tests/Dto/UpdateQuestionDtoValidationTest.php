<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Dto;

use App\Dto\UpdateQuestionDto;
use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UpdateQuestionDtoValidationTest.
 *
 * Tests validation rules for UpdateQuestionDto.
 */
class UpdateQuestionDtoValidationTest extends KernelTestCase
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
     * Tests a valid DTO with all fields set passes validation.
     *
     * @throws \Exception
     */
    public function testValidDtoWithAllFields(): void
    {
        $dto = new UpdateQuestionDto();
        $dto->title = 'Valid Title';
        $dto->content = 'This content is definitely longer than 10 characters.';
        $dto->category = new Category();
        $dto->tags = [new Tag()];

        $errors = $this->validator->validate($dto);
        $this->assertCount(0, $errors);
    }

    /**
     * Tests a valid DTO with nullable fields passes validation.
     *
     * @throws \Exception
     */
    public function testValidDtoWithNullFields(): void
    {
        $dto = new UpdateQuestionDto();
        $dto->title = null;
        $dto->content = null;
        $dto->category = null;
        $dto->tags = null;

        $errors = $this->validator->validate($dto);
        $this->assertCount(0, $errors);
    }

    /**
     * Tests that too short title fails validation.
     *
     * @throws \Exception
     */
    public function testTooShortTitleFails(): void
    {
        $dto = new UpdateQuestionDto();
        $dto->title = 'ab'; // below min 3 characters

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    /**
     * Tests that too long title fails validation.
     *
     * @throws \Exception
     */
    public function testTooLongTitleFails(): void
    {
        $dto = new UpdateQuestionDto();
        $dto->title = str_repeat('a', 300); // over max 255 characters

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too long', (string) $errors);
    }

    /**
     * Tests that too short content fails validation.
     *
     * @throws \Exception
     */
    public function testTooShortContentFails(): void
    {
        $dto = new UpdateQuestionDto();
        $dto->content = 'short'; // below min 10 characters

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    /**
     * Tests that invalid tags type fails validation.
     *
     * @throws \Exception
     */
    public function testInvalidTagsTypeFails(): void
    {
        $dto = new UpdateQuestionDto();
        $dto->tags = ['not a tag instance'];

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should be of type', (string) $errors);
    }
}
