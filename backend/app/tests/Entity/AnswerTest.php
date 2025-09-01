<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Entity;

use App\Entity\Answer;
use App\Entity\User;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class AnswerTest.
 *
 * Tests basic getters and setters of the Answer entity.
 */
class AnswerTest extends TestCase
{
    /**
     * Test setting and getting content.
     *
     * @test
     */
    public function testGetSetContent(): void
    {
        $answer = new Answer();
        $answer->setContent('Test content');
        $this->assertSame('Test content', $answer->getContent());
    }

    /**
     * Test setting and getting isBest flag.
     *
     * @test
     */
    public function testIsBest(): void
    {
        $answer = new Answer();
        $this->assertFalse($answer->getIsBest());
        $answer->setIsBest(true);
        $this->assertTrue($answer->getIsBest());
    }

    /**
     * Test detection of anonymous answers.
     *
     * @test
     *
     * @throws Exception
     */
    public function testIsFromAnonymous(): void
    {
        $answer = new Answer();
        $this->assertTrue($answer->isFromAnonymous());

        $user = $this->createMock(User::class);
        $answer->setAuthor($user);
        $this->assertFalse($answer->isFromAnonymous());
    }

    /**
     * Test retrieving the display name of the author.
     *
     * @test
     *
     * @throws Exception
     */
    public function testGetDisplayName(): void
    {
        $answer = new Answer();

        // Author is null -> returns authorNickname
        $answer->setAuthor(null);
        $answer->setAuthorNickname('Anonim');
        $this->assertSame('Anonim', $answer->getDisplayName());

        // Author is set -> returns nickname from User
        $user = $this->createMock(User::class);
        $user->method('getNickname')->willReturn('UserNick');

        $answer->setAuthor($user);
        $this->assertSame('UserNick', $answer->getDisplayName());
    }

    /**
     * Test getting the ID property.
     *
     * @test
     */
    public function testGetId(): void
    {
        $answer = new Answer();
        $this->assertNull($answer->getId());

        $reflection = new \ReflectionProperty(Answer::class, 'id');
        $reflection->setValue($answer, 123);

        $this->assertSame(123, $answer->getId());
    }

    /**
     * Test getting the createdAt property.
     *
     * @test
     */
    public function testGetCreatedAt(): void
    {
        $answer = new Answer();
        $this->assertNull($answer->getCreatedAt());

        $date = new \DateTimeImmutable('2025-08-15 12:00:00');
        $reflection = new \ReflectionProperty(Answer::class, 'createdAt');
        $reflection->setValue($answer, $date);

        $this->assertSame($date, $answer->getCreatedAt());
    }
}
