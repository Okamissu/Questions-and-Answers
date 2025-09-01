<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\DataFixtures;

use App\Entity\Tag;

/**
 * Class TagFixtures.
 *
 * Fixture for creating sample Tag entities.
 */
class TagFixtures extends AbstractBaseFixtures
{
    /**
     * Load data for tags.
     */
    protected function loadData(): void
    {
        $this->createMany(15, 'tag', function (int $i) {
            $tag = new Tag();
            $tag->setName($this->faker->unique()->word());

            return $tag;
        });
    }
}
