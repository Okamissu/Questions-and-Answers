<?php

namespace App\Service;

use App\Dto\CreateCategoryDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class CategoryService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryRepository $categoryRepository,
    ) {
    }

    public function create(CreateCategoryDto $dto): Category
    {
        $category = new Category();
        $category->setName($dto->name);

        $this->em->persist($category);
        $this->em->flush();

        return $category;
    }

    public function update(Category $category, UpdateCategoryDto $dto): Category
    {
        if (null !== $dto->name) {
            $category->setName($dto->name);
        }

        $this->em->flush();

        return $category;
    }

    public function delete(Category $category): void
    {
        $this->em->remove($category);
        $this->em->flush();
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->categoryRepository->findBySlug($slug);
    }

    public function queryAll(
        ?string $search = null,
        string $sortField = 'name',
        string $sortDirection = 'asc',
        ?int $limit = null,
        ?int $offset = null,
    ): QueryBuilder {
        return $this->categoryRepository->queryAll($search, $sortField, $sortDirection, $limit, $offset);
    }
}
