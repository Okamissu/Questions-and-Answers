<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Category entity.
 *
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * CategoryRepository constructor.
     *
     * @param ManagerRegistry $registry The Doctrine manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Builds a QueryBuilder for categories with optional filtering and sorting.
     *
     * @param string|null $search Filter by category name or description
     * @param string|null $sort   Sort field and direction, e.g. "name_ASC"
     *
     * @return QueryBuilder The Doctrine QueryBuilder instance
     */
    public function queryWithFilters(?string $search = null, ?string $sort = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.name LIKE :search OR c.description LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        $allowedFields = ['name', 'createdAt'];

        if ($sort) {
            [$field, $direction] = explode('_', $sort) + [null, null];
            if (in_array($field, $allowedFields, true) && in_array(strtoupper($direction), ['ASC', 'DESC'], true)) {
                $qb->orderBy('c.'.$field, strtoupper($direction));
            } else {
                $qb->orderBy('c.createdAt', 'DESC'); // fallback
            }
        } else {
            $qb->orderBy('c.createdAt', 'DESC'); // default
        }

        return $qb;
    }

    /**
     * Persists a Category entity.
     *
     * @param Category $category The category to save
     */
    public function save(Category $category): void
    {
        $em = $this->getEntityManager();
        $em->persist($category);
        $em->flush();
    }

    /**
     * Removes a Category entity.
     *
     * @param Category $category The category to delete
     */
    public function delete(Category $category): void
    {
        $em = $this->getEntityManager();
        $em->remove($category);
        $em->flush();
    }
}
