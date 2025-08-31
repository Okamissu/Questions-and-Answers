<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Tag;
use App\Entity\User;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class QuestionTest.
 *
 * Tests basic getters and setters of the Question entity.
 */
class QuestionTest extends TestCase
{
    /**
     * Test setting and getting title and content.
     *
     * @test
     */
    public function testSettersAndGetters(): void
    {
        $question = new Question();

        $question->setTitle('Example Title');
        $question->setContent('This is the content of the question.');

        $this->assertEquals('Example Title', $question->getTitle());
        $this->assertEquals('This is the content of the question.', $question->getContent());
    }

    /**
     * Test setting and getting author.
     *
     * @test
     *
     * @throws Exception
     */
    public function testSetAuthor(): void
    {
        $question = new Question();
        $author = $this->createMock(User::class);
        $question->setAuthor($author);

        $this->assertSame($author, $question->getAuthor());
    }

    /**
     * Test setting and getting category.
     *
     * @test
     *
     * @throws Exception
     */
    public function testSetCategory(): void
    {
        $question = new Question();
        $category = $this->createMock(Category::class);
        $question->setCategory($category);

        $this->assertSame($category, $question->getCategory());
    }

    /**
     * Test adding tags.
     *
     * @test
     *
     * @throws Exception
     */
    public function testAddTag(): void
    {
        $question = new Question();
        $tag = $this->createMock(Tag::class);

        $this->assertCount(0, $question->getTags());

        $question->addTag($tag);
        $this->assertCount(1, $question->getTags());
        $this->assertTrue($question->getTags()->contains($tag));

        // Adding the same tag again should not duplicate
        $question->addTag($tag);
        $this->assertCount(1, $question->getTags());
    }

    /**
     * Test removing tags.
     *
     * @test
     *
     * @throws Exception
     */
    public function testRemoveTag(): void
    {
        $question = new Question();
        $tag = $this->createMock(Tag::class);

        $question->addTag($tag);
        $this->assertCount(1, $question->getTags());

        $question->removeTag($tag);
        $this->assertCount(0, $question->getTags());
    }

    /**
     * Test getting the ID property.
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
     * Test getting the createdAt property.
     *
     * @test
     */
    public function testGetCreatedAt(): void
    {
        $question = new Question();
        $this->assertNull($question->getCreatedAt());

        $date = new \DateTimeImmutable('2025-08-15 12:00:00');
        $reflection = new \ReflectionProperty(Question::class, 'createdAt');
        $reflection->setValue($question, $date);

        $this->assertSame($date, $question->getCreatedAt());
    }

    /**
     * Test getting the updatedAt property.
     *
     * @test
     */
    public function testGetUpdatedAt(): void
    {
        $question = new Question();
        $this->assertNull($question->getUpdatedAt());

        $date = new \DateTimeImmutable('2025-08-15 13:00:00');
        $reflection = new \ReflectionProperty(Question::class, 'updatedAt');
        $reflection->setValue($question, $date);

        $this->assertSame($date, $question->getUpdatedAt());
    }
}
