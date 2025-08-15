<?php

namespace App\Service;

use App\Dto\CreateCategoryDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;

interface CategoryServiceInterface
{
    public function create(CreateCategoryDto $dto): Category;

    public function update(Category $category, UpdateCategoryDto $dto): Category;

    public function delete(Category $category): void;

    /**
     * Returns paginated list of categories with optional filters.
     *
     * @return array ['items' => Category[], 'totalItems' => int]
     */
    public function getPaginatedList(
        int $page,
        int $limit,
        ?string $search = null,
        ?string $sort = null,
    ): array;
}
