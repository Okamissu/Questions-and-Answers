<?php

namespace App\Service;

use App\Dto\CreateCategoryDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        protected CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * Creates a new category from DTO.
     */
    public function create(CreateCategoryDto $dto): Category
    {
        $category = new Category();
        $category->setName($dto->name);

        $this->categoryRepository->save($category);

        return $category;
    }

    /**
     * Updates an existing category from DTO.
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
     * Deletes a category entity.
     */
    public function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createPaginator($qb): Paginator
    {
        return new Paginator($qb);
    }

    public function getPaginatedList(
        int $page,
        int $limit,
        ?string $search = null,
        ?string $sort = null,
    ): array {
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
}
