<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testGetSetName(): void
    {
        $category = new Category();
        $category->setName('Programming');
        $this->assertSame('Programming', $category->getName());
    }

    public function testTimestampsInitiallyNull(): void
    {
        $category = new Category();
        $this->assertNull($category->getCreatedAt());
        $this->assertNull($category->getUpdatedAt());
    }
}
