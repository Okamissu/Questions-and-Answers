<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Entity;

use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Tag;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Class QuestionTest.
 *
 * Tests all getters, setters, and collection handling of the Question entity.
 *
 * @covers \App\Entity\Question
 */
class QuestionTest extends TestCase
{
    /**
     * Test constructor initializes collections.
     *
     * @test
     */
    public function testConstructorInitializesCollections(): void
    {
        $question = new Question();
        $this->assertCount(0, $question->getTags());
        $this->assertCount(0, $question->getAnswers());
    }

    /**
     * Test ID getter.
     *
     * @test
     */
    public function testGetId(): void
    {
        $question = new Question();
        $this->assertNull($question->getId());

        $reflection = new \ReflectionProperty(Question::class, 'id');
        $reflection->setValue($question, 42);

        $this->assertSame(42, $question->getId());
    }

    /**
     * Test title getter and setter.
     *
     * @test
     */
    public function testGetSetTitle(): void
    {
        $question = new Question();
        $this->assertNull($question->getTitle());

        $question->setTitle('Sample title');
        $this->assertSame('Sample title', $question->getTitle());
    }

    /**
     * Test content getter and setter.
     *
     * @test
     */
    public function testGetSetContent(): void
    {
        $question = new Question();
        $this->assertNull($question->getContent());

        $question->setContent('Some content');
        $this->assertSame('Some content', $question->getContent());
    }

    /**
     * Test createdAt getter.
     *
     * @test
     */
    public function testGetCreatedAt(): void
    {
        $question = new Question();
        $this->assertNull($question->getCreatedAt());

        $date = new \DateTimeImmutable();
        $reflection = new \ReflectionProperty(Question::class, 'createdAt');
        $reflection->setValue($question, $date);

        $this->assertSame($date, $question->getCreatedAt());
    }

    /**
     * Test updatedAt getter.
     *
     * @test
     */
    public function testGetUpdatedAt(): void
    {
        $question = new Question();
        $this->assertNull($question->getUpdatedAt());

        $date = new \DateTimeImmutable();
        $reflection = new \ReflectionProperty(Question::class, 'updatedAt');
        $reflection->setValue($question, $date);

        $this->assertSame($date, $question->getUpdatedAt());
    }

    /**
     * Test author getter and setter.
     *
     * @test
     */
    public function testGetSetAuthor(): void
    {
        $question = new Question();
        $this->assertNull($question->getAuthor());

        $user = new User();
        $question->setAuthor($user);

        $this->assertSame($user, $question->getAuthor());
    }

    /**
     * Test category getter and setter.
     *
     * @test
     */
    public function testGetSetCategory(): void
    {
        $question = new Question();
        $this->assertNull($question->getCategory());

        $category = new Category();
        $question->setCategory($category);

        $this->assertSame($category, $question->getCategory());
    }

    /**
     * Test tag collection handling.
     *
     * @test
     */
    public function testGetAddRemoveTags(): void
    {
        $question = new Question();
        $tag = new Tag();

        $this->assertCount(0, $question->getTags());

        $question->addTag($tag);
        $this->assertCount(1, $question->getTags());
        $this->assertTrue($question->getTags()->contains($tag));

        // Adding the same tag twice does not duplicate it
        $question->addTag($tag);
        $this->assertCount(1, $question->getTags());

        $question->removeTag($tag);
        $this->assertCount(0, $question->getTags());
    }

    /**
     * Test answers collection handling.
     *
     * @test
     */
    public function testGetAddRemoveAnswers(): void
    {
        $question = new Question();
        $answer = new Answer();

        $this->assertCount(0, $question->getAnswers());

        // Add
        $question->addAnswer($answer);
        $this->assertCount(1, $question->getAnswers());
        $this->assertSame($question, $answer->getQuestion());

        // Add same answer again -> still only 1
        $question->addAnswer($answer);
        $this->assertCount(1, $question->getAnswers());

        // Remove
        $question->removeAnswer($answer);
        $this->assertCount(0, $question->getAnswers());
        $this->assertNull($answer->getQuestion());
    }
}
