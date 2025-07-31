<?php
namespace App\Tests\Dto;

use App\Dto\CreateQuestionDto;
use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateQuestionDtoValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

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

    public function testBlankTitleFails(): void
    {
        $dto = new CreateQuestionDto();
        $dto->title = '';
        $dto->content = 'Valid content more than 10 chars';
        $dto->category = new Category();

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string)$errors);
    }

    public function testShortContentFails(): void
    {
        $dto = new CreateQuestionDto();
        $dto->title = 'Valid title';
        $dto->content = 'short';
        $dto->category = new Category();

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string)$errors);
    }

    public function testNullCategoryFails(): void
    {
        $dto = new CreateQuestionDto();
        $dto->title = 'Valid title';
        $dto->content = 'Valid content more than 10 chars';
        $dto->category = null;

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be null', (string)$errors);
    }

    public function testInvalidTagsTypeFails(): void
    {
        $dto = new CreateQuestionDto();
        $dto->title = 'Valid title';
        $dto->content = 'Valid content more than 10 chars';
        $dto->category = new Category();
        $dto->tags = ['not a tag instance'];

        $errors = $this->validator->validate($dto);

        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should be of type', (string)$errors);
    }
}
