<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\DataFixtures;

use App\Entity\Category;

/**
 * Class CategoryFixtures.
 *
 * Fixture for creating sample Category entities.
 */
class CategoryFixtures extends AbstractBaseFixtures
{
    /**
     * Load data for categories.
     */
    protected function loadData(): void
    {
        $this->createMany(10, 'category', function (int $i) {
            $category = new Category();
            $category->setName($this->faker->unique()->word());

            return $category;
        });
    }
}
