<?php

namespace App\Tests\Entity;

use App\Entity\Answer;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class AnswerTest extends TestCase
{
    public function testGetSetContent()
    {
        $answer = new Answer();
        $answer->setContent('Test content');
        $this->assertSame('Test content', $answer->getContent());
    }

    public function testIsBest()
    {
        $answer = new Answer();
        $this->assertFalse($answer->getIsBest());
        $answer->setIsBest(true);
        $this->assertTrue($answer->getIsBest());
    }

    public function testIsFromAnonymous()
    {
        $answer = new Answer();
        $this->assertTrue($answer->isFromAnonymous());

        $user = $this->createMock(User::class);
        $answer->setAuthor($user);
        $this->assertFalse($answer->isFromAnonymous());
    }

    public function testGetDisplayName()
    {
        $answer = new Answer();

        // Jeśli autor jest null, powinno zwrócić authorNickname
        $answer->setAuthor(null);
        $answer->setAuthorNickname('Anonim');
        $this->assertSame('Anonim', $answer->getDisplayName());

        // Jeśli autor jest ustawiony, powinno zwrócić nickname z obiektu User
        $user = $this->createMock(User::class);
        $user->method('getNickname')->willReturn('UserNick');

        $answer->setAuthor($user);
        $this->assertSame('UserNick', $answer->getDisplayName());
    }

    public function testGetId(): void
    {
        $answer = new Answer();
        $this->assertNull($answer->getId());

        // Optionally set id via reflection to simulate DB-assigned id
        $reflection = new \ReflectionProperty(Answer::class, 'id');
        $reflection->setValue($answer, 123);

        $this->assertSame(123, $answer->getId());
    }

    public function testGetCreatedAt(): void
    {
        $answer = new Answer();
        $this->assertNull($answer->getCreatedAt());

        // Optionally set createdAt via reflection to simulate DB-assigned timestamp
        $date = new \DateTimeImmutable('2025-08-15 12:00:00');
        $reflection = new \ReflectionProperty(Answer::class, 'createdAt');
        $reflection->setValue($answer, $date);

        $this->assertSame($date, $answer->getCreatedAt());
    }

}

