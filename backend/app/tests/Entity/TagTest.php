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

    public function testSlugInitiallyNull(): void
    {
        $tag = new Tag();
        $this->assertNull($tag->getSlug());
    }

    public function testTimestampsInitiallyNull(): void
    {
        $tag = new Tag();
        $this->assertNull($tag->getCreatedAt());
        $this->assertNull($tag->getUpdatedAt());
    }
}
