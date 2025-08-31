<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Entity;

use App\Entity\Tag;
use PHPUnit\Framework\TestCase;

/**
 * Class TagTest.
 *
 * Tests basic getters and setters of the Tag entity.
 */
class TagTest extends TestCase
{
    /**
     * Test setting and getting the name.
     *
     * @test
     */
    public function testGetSetName(): void
    {
        $tag = new Tag();
        $tag->setName('Symfony');
        $this->assertSame('Symfony', $tag->getName());
    }

    /**
     * Test that timestamps are initially null.
     *
     * @test
     */
    public function testTimestampsInitiallyNull(): void
    {
        $tag = new Tag();
        $this->assertNull($tag->getCreatedAt());
        $this->assertNull($tag->getUpdatedAt());
    }

    /**
     * Test getting the ID property.
     *
     * @test
     */
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
