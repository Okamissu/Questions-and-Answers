<?php

namespace App\Tests\Dto;

use App\Dto\CreateAnswerDto;
use App\Entity\Question;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateAnswerDtoValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    private function createValidQuestion(): Question
    {
        $question = new Question();
        $question->setTitle('Valid title');
        $question->setContent('Valid content with enough length');

        // You can skip author/category if not relevant for validation
        return $question;
    }

    private function createValidUser(): User
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword('hashedpassword');
        $user->setNickname('nickname');

        return $user;
    }

    public function testValidDtoPassesValidation(): void
    {
        $dto = new CreateAnswerDto();
        $dto->content = 'This is a valid answer content.';
        $dto->question = $this->createValidQuestion();
        $dto->author = $this->createValidUser();
        $dto->authorNickname = 'UserNick';
        $dto->authorEmail = 'user@example.com';
        $dto->isBest = true;

        $errors = $this->validator->validate($dto);
        $this->assertCount(0, $errors);
    }

    public function testEmptyContentFails(): void
    {
        $dto = new CreateAnswerDto();
        $dto->content = '';
        $dto->question = $this->createValidQuestion();

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be blank', (string) $errors);
    }

    public function testShortContentFails(): void
    {
        $dto = new CreateAnswerDto();
        $dto->content = 'short';
        $dto->question = $this->createValidQuestion();

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    public function testNullQuestionFails(): void
    {
        $dto = new CreateAnswerDto();
        $dto->content = 'Valid content here';
        $dto->question = null;

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be null', (string) $errors);
    }

    public function testInvalidEmailFails(): void
    {
        $dto = new CreateAnswerDto();
        $dto->content = 'Valid content here';
        $dto->question = $this->createValidQuestion();
        $dto->authorEmail = 'not-an-email';

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is not a valid email address', (string) $errors);
    }
}
