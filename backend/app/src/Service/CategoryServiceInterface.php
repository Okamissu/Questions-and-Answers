<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Service;

use App\Dto\CreateCategoryDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;

/**
 * Interface for managing Category entities.
 */
interface CategoryServiceInterface
{
    /**
     * Creates a new Category from the given DTO.
     *
     * @param CreateCategoryDto $dto DTO containing category data
     *
     * @return Category The created Category entity
     */
    public function create(CreateCategoryDto $dto): Category;

    /**
     * Updates an existing Category with values from the given DTO.
     *
     * @param Category          $category The Category entity to update
     * @param UpdateCategoryDto $dto      DTO containing updated values
     *
     * @return Category The updated Category entity
     */
    public function update(Category $category, UpdateCategoryDto $dto): Category;

    /**
     * Deletes the given Category entity.
     *
     * @param Category $category The Category entity to delete
     */
    public function delete(Category $category): void;

    /**
     * Returns a paginated list of Category entities with optional search and sorting.
     *
     * @param int         $page   Page number (1-based)
     * @param int         $limit  Number of items per page
     * @param string|null $search Optional search string to filter by category name
     * @param string|null $sort   Optional sort string, e.g. "name_ASC"
     *
     * @return array{items: Category[], totalItems: int} Paginated categories and total count
     */
    public function getPaginatedList(int $page, int $limit, ?string $search = null, ?string $sort = null): array;
}
