<?php

namespace App\Tests\Entity;

use App\Entity\Tag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    public function testGetSetName(): void
    {
        $tag = new Tag();
        $tag->setName('Symfony');
        $this->assertSame('Symfony', $tag->getName());
    }

    public function testTimestampsInitiallyNull(): void
    {
        $tag = new Tag();
        $this->assertNull($tag->getCreatedAt());
        $this->assertNull($tag->getUpdatedAt());
    }

    public function testGetId(): void
    {
        $tag = new Tag();
        $this->assertNull($tag->getId());

        // Simulate DB-assigned ID
        $reflection = new \ReflectionProperty(Tag::class, 'id');
        $reflection->setValue($tag, 42);

        $this->assertSame(42, $tag->getId());
    }
}
