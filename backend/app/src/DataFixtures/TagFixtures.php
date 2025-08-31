<?php

namespace App\DataFixtures;

use App\Entity\Tag;

class TagFixtures extends AbstractBaseFixtures
{
    protected function loadData(): void
    {
        $this->createMany(15, 'tag', function (int $i) {
            $tag = new Tag();
            $tag->setName($this->faker->unique()->word());

            return $tag;
        });
    }
}
