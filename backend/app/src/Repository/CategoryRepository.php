<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Returns a query builder to fetch all categories with optional filtering, sorting, and pagination.
     */
    public function queryWithFilters(?string $search = null, ?string $sort = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.name LIKE :search OR c.description LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if ($sort) {
            [$field, $direction] = explode('_', $sort);
            $allowedFields = ['name', 'createdAt'];
            if (in_array($field, $allowedFields, true) && in_array(strtoupper($direction), ['ASC', 'DESC'], true)) {
                $qb->orderBy('c.'.$field, strtoupper($direction));
            }
        } else {
            $qb->orderBy('c.createdAt', 'DESC');
        }

        return $qb;
    }

    /**
     * Saves a category entity.
     */
    public function save(Category $category): void
    {
        $em = $this->getEntityManager();
        $em->persist($category);
        $em->flush();
    }

    /**
     * Deletes a category entity.
     */
    public function delete(Category $category): void
    {
        $em = $this->getEntityManager();
        $em->remove($category);
        $em->flush();
    }
}
