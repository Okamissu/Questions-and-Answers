<?php

namespace App\DataFixtures;

use App\Entity\Category;

class CategoryFixtures extends AbstractBaseFixtures
{
    protected function loadData(): void
    {
        $this->createMany(10, 'category', function (int $i) {
            $category = new Category();
            $category->setName($this->faker->unique()->word());

            return $category;
        });
    }
}
