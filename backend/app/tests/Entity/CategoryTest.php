<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Entity;

use App\Entity\Category;
use PHPUnit\Framework\TestCase;

/**
 * Class CategoryTest.
 *
 * Tests basic getters and setters of the Category entity.
 */
class CategoryTest extends TestCase
{
    /**
     * Test setting and getting the name.
     *
     * @test
     */
    public function testGetSetName(): void
    {
        $category = new Category();
        $category->setName('Programming');
        $this->assertSame('Programming', $category->getName());
    }

    /**
     * Test that timestamps are initially null.
     *
     * @test
     */
    public function testTimestampsInitiallyNull(): void
    {
        $category = new Category();
        $this->assertNull($category->getCreatedAt());
        $this->assertNull($category->getUpdatedAt());
    }

    /**
     * Test getting the ID property.
     *
     * @test
     */
    public function testGetId(): void
    {
        $category = new Category();
        $this->assertNull($category->getId());

        $reflection = new \ReflectionProperty(Category::class, 'id');
        $reflection->setValue($category, 123);

        $this->assertSame(123, $category->getId());
    }
}
