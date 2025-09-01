<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Dto;

use App\Dto\CreateAnswerDto;
use App\Entity\Question;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CreateAnswerDtoValidationTest.
 *
 * Tests validation rules for CreateAnswerDto.
 */
class CreateAnswerDtoValidationTest extends KernelTestCase
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
     * Creates a valid Question entity for testing.
     */
    private function createValidQuestion(): Question
    {
        $question = new Question();
        $question->setTitle('Valid title');
        $question->setContent('Valid content with enough length');

        // Author/category can be skipped if not relevant for validation
        return $question;
    }

    /**
     * Creates a valid User entity for testing.
     */
    private function createValidUser(): User
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setPassword('hashedpassword');
        $user->setNickname('nickname');

        return $user;
    }

    /**
     * Tests that a valid DTO passes validation.
     *
     * @throws \Exception
     */
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

    /**
     * Tests that empty content fails validation.
     *
     * @throws \Exception
     */
    public function testEmptyContentFails(): void
    {
        $dto = new CreateAnswerDto();
        $dto->content = '';
        $dto->question = $this->createValidQuestion();

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
        $dto = new CreateAnswerDto();
        $dto->content = 'short';
        $dto->question = $this->createValidQuestion();

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value is too short', (string) $errors);
    }

    /**
     * Tests that a null question fails validation.
     *
     * @throws \Exception
     */
    public function testNullQuestionFails(): void
    {
        $dto = new CreateAnswerDto();
        $dto->content = 'Valid content here';
        $dto->question = null;

        $errors = $this->validator->validate($dto);
        $this->assertGreaterThan(0, count($errors));
        $this->assertStringContainsString('This value should not be null', (string) $errors);
    }

    /**
     * Tests that invalid email fails validation.
     *
     * @throws \Exception
     */
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
