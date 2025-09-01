<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Service;

use App\Dto\CreateCategoryDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Service responsible for managing Category entities.
 */
class CategoryService implements CategoryServiceInterface
{
    /**
     * CategoryService constructor.
     *
     * @param CategoryRepository $categoryRepository Repository used to persist Category entities
     */
    public function __construct(protected CategoryRepository $categoryRepository)
    {
    }

    /**
     * Creates a new Category entity from the given DTO and saves it.
     *
     * @param CreateCategoryDto $dto DTO containing category data
     *
     * @return Category The created Category entity
     */
    public function create(CreateCategoryDto $dto): Category
    {
        $category = new Category();
        $category->setName($dto->name);

        $this->categoryRepository->save($category);

        return $category;
    }

    /**
     * Updates an existing Category entity with values from the given DTO.
     *
     * @param Category          $category The Category entity to update
     * @param UpdateCategoryDto $dto      DTO containing updated values
     *
     * @return Category The updated Category entity
     */
    public function update(Category $category, UpdateCategoryDto $dto): Category
    {
        if (null !== $dto->name) {
            $category->setName($dto->name);
        }

        $this->categoryRepository->save($category);

        return $category;
    }

    /**
     * Deletes the given Category entity.
     *
     * @param Category $category The Category entity to delete
     */
    public function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
    }

    /**
     * Returns a paginated list of Category entities with optional search and sorting.
     *
     * @param int         $page   Page number (1-based)
     * @param int         $limit  Number of items per page
     * @param string|null $search Search string to filter by category name
     * @param string|null $sort   Sort string, e.g. "name_ASC" (optional)
     *
     * @return array{items: Category[], totalItems: int} Paginated categories and total count
     */
    public function getPaginatedList(int $page, int $limit, ?string $search = null, ?string $sort = null): array
    {
        $qb = $this->categoryRepository->queryWithFilters($search, $sort);
        $paginator = $this->createPaginator($qb);

        $totalItems = count($paginator);

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return [
            'items' => $qb->getQuery()->getResult(),
            'totalItems' => $totalItems,
        ];
    }

    /**
     * Creates a Doctrine paginator for a query builder.
     *
     * @param QueryBuilder $qb QueryBuilder instance
     *
     * @return Paginator The paginator
     *
     * @codeCoverageIgnore
     */
    protected function createPaginator(QueryBuilder $qb): Paginator
    {
        return new Paginator($qb);
    }
}
