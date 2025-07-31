<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Tag;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QuestionValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    private function createValidUser(): User
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setNickname('testuser');
        $user->setPassword('hashed-password'); // zakładamy, że walidacja hasła jest na plainPassword
        $user->setPlainPassword('secure123');
        return $user;
    }

    private function createValidCategory(): Category
    {
        $category = new Category();
        $category->setName('Programming');
        return $category;
    }

    private function createValidTag(): Tag
    {
        $tag = new Tag();
        $tag->setName('Symfony');
        return $tag;
    }

    private function createValidQuestion(): Question
    {
        $question = new Question();
        $question->setTitle('Valid Question Title');
        $question->setContent('This is a valid question content that has enough length.');
        $question->setAuthor($this->createValidUser());
        $question->setCategory($this->createValidCategory());
        $question->addTag($this->createValidTag());
        return $question;
    }

    public function testValidQuestionPassesValidation(): void
    {
        $question = $this->createValidQuestion();
        $errors = $this->validator->validate($question);

        foreach ($errors as $error) {
            echo $error->getPropertyPath() . ': ' . $error->getMessage() . "\n";
        }

        $this->assertCount(0, $errors);
    }

    public function testTitleIsRequired(): void
    {
        $question = $this->createValidQuestion();
        $question->setTitle(null);
        $errors = $this->validator->validate($question);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testTitleTooShort(): void
    {
        $question = $this->createValidQuestion();
        $question->setTitle('Hi');
        $errors = $this->validator->validate($question);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testContentTooShort(): void
    {
        $question = $this->createValidQuestion();
        $question->setContent('Too short');
        $errors = $this->validator->validate($question);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testAuthorIsRequired(): void
    {
        $question = $this->createValidQuestion();
        $question->setAuthor(null);
        $errors = $this->validator->validate($question);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testCategoryIsRequired(): void
    {
        $question = $this->createValidQuestion();
        $question->setCategory(null);
        $errors = $this->validator->validate($question);
        $this->assertGreaterThan(0, count($errors));
    }
}
