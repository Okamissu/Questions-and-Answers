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
}
