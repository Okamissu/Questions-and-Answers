<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Tag;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class QuestionTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $question = new Question();

        $question->setTitle('Example Title');
        $question->setContent('This is the content of the question.');

        $this->assertEquals('Example Title', $question->getTitle());
        $this->assertEquals('This is the content of the question.', $question->getContent());
    }

    public function testSetAuthor(): void
    {
        $question = new Question();
        $author = $this->createMock(User::class);
        $question->setAuthor($author);

        $this->assertSame($author, $question->getAuthor());
    }

    public function testSetCategory(): void
    {
        $question = new Question();
        $category = $this->createMock(Category::class);
        $question->setCategory($category);

        $this->assertSame($category, $question->getCategory());
    }

    public function testAddTag(): void
    {
        $question = new Question();
        $tag = $this->createMock(Tag::class);

        $this->assertCount(0, $question->getTags());

        $question->addTag($tag);
        $this->assertCount(1, $question->getTags());
        $this->assertTrue($question->getTags()->contains($tag));

        // Adding same tag again should not duplicate
        $question->addTag($tag);
        $this->assertCount(1, $question->getTags());
    }

    public function testRemoveTag(): void
    {
        $question = new Question();
        $tag = $this->createMock(Tag::class);

        $question->addTag($tag);
        $this->assertCount(1, $question->getTags());

        $question->removeTag($tag);
        $this->assertCount(0, $question->getTags());
    }
}
