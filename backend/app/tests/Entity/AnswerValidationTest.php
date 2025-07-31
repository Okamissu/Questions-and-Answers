<?php

namespace App\Tests\Entity;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AnswerValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    private function createValidQuestion(): Question
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashedPassword');
        $user->setRoles(['ROLE_USER']);
        $user->setNickname('TestUser');

        $category = new Category();
        $category->setName('General');
        $question = new Question();
        $question->setTitle('Valid question title');
        $question->setContent('This is a valid question content with more than 10 characters.');
        $question->setAuthor($user);
        $question->setCategory($category);

        return $question;
    }

    public function testValidAnswerPassesValidation(): void
    {
        $answer = new Answer();
        $answer->setContent('This is a valid answer.');
        $answer->setQuestion($this->createValidQuestion());

        $errors = $this->validator->validate($answer);

        $this->assertCount(0, $errors);
    }

    public function testEmptyContentFailsValidation(): void
    {
        $answer = new Answer();
        $answer->setContent('');
        $answer->setQuestion($this->createValidQuestion());

        $errors = $this->validator->validate($answer);

        $this->assertGreaterThan(0, count($errors));
        $this->assertSame('This value should not be blank.', $errors[0]->getMessage());
    }

    public function testMissingQuestionFailsValidation(): void
    {
        $answer = new Answer();
        $answer->setContent('Some content');
        $answer->setQuestion(null);

        $errors = $this->validator->validate($answer);

        $this->assertGreaterThan(0, count($errors));
        $this->assertSame('This value should not be null.', $errors[0]->getMessage());
    }

    public function testInvalidEmailFailsValidation(): void
    {
        $answer = new Answer();
        $answer->setContent('Some content');
        $answer->setQuestion($this->createValidQuestion());
        $answer->setAuthorEmail('invalid-email');

        $errors = $this->validator->validate($answer);

        $this->assertGreaterThan(0, count($errors));
        $this->assertSame('This value is not a valid email address.', $errors[0]->getMessage());
    }
}
