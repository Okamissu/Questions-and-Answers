<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Dto;

use App\Dto\CreateQuestionDto;
use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CreateQuestionDtoValidationTest.
 *
 * Tests validation rules for CreateQuestionDto.
 */
class CreateQuestionDtoValidationTest extends KernelTestCase
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
    public function testValidDto(): void
    {
        $dto = new CreateQuestionDto();
        $dto->title = 'Valid title';
        $dto->content = 'This content is definitely longer than 10 chars.';
        $dto->category = new Category();
        $dto->tags = [new Tag()];

        $errors = $this->validator->validate($dto);
        $this->assertCount(0, $errors);
    }

    /**
     * Tests that a blank title fails validation.
     *
     * @throws \Exception
     */
    public function testBlankTitleFails(): void
    {
        $dto = new CreateQuestionDto();
        $dto->title = '';
        $dto->content = 'Valid content more than 10 chars';
        $dto->category = new Category();

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    /**
     * Tests that too short content fails validation.
     *
     * @throws \Exception
     */
    public function testShortContentFails(): void
    {
        $dto = new CreateQuestionDto();
        $dto->title = 'Valid title';
        $dto->content = 'short';
        $dto->category = new Category();

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    /**
     * Tests that a null category fails validation.
     *
     * @throws \Exception
     */
    public function testNullCategoryFails(): void
    {
        $dto = new CreateQuestionDto();
        $dto->title = 'Valid title';
        $dto->content = 'Valid content more than 10 chars';
        $dto->category = null;

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be null', (string) $errors);
    }

    /**
     * Tests that invalid tags type fails validation.
     *
     * @throws \Exception
     */
    public function testInvalidTagsTypeFails(): void
    {
        $dto = new CreateQuestionDto();
        $dto->title = 'Valid title';
        $dto->content = 'Valid content more than 10 chars';
        $dto->category = new Category();
        $dto->tags = ['not a tag instance'];

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should be of type', (string) $errors);
    }
}
