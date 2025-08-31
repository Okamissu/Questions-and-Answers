<?php

namespace App\Tests\Dto;

use App\Dto\UpdateQuestionDto;
use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateQuestionDtoValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

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

    public function testTooShortTitleFails(): void
    {
        $dto = new UpdateQuestionDto();
        $dto->title = 'ab'; // poniżej min 3 znaków

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    public function testTooLongTitleFails(): void
    {
        $dto = new UpdateQuestionDto();
        $dto->title = str_repeat('a', 300); // ponad max 255 znaków

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too long', (string) $errors);
    }

    public function testTooShortContentFails(): void
    {
        $dto = new UpdateQuestionDto();
        $dto->content = 'short'; // poniżej min 10 znaków

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    public function testInvalidTagsTypeFails(): void
    {
        $dto = new UpdateQuestionDto();
        $dto->tags = ['not a tag instance'];

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should be of type', (string) $errors);
    }
}
