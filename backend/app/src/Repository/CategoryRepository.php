<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Query all categories.
     */
    public function queryAll(
        ?string $search = null,
        string $sortField = 'name',
        string $sortDirection = 'asc',
        ?int $limit = null,
        ?int $offset = null
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        $allowedFields = ['name'];
        $allowedDirections = ['asc', 'desc'];

        if (!in_array($sortField, $allowedFields, true)) {
            $sortField = 'name';
        }

        if (!in_array(strtolower($sortDirection), $allowedDirections, true)) {
            $sortDirection = 'asc';
        }

        $qb->orderBy('c.'.$sortField, $sortDirection);

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $qb;
    }


    /**
     * Save category entity.
     */
    public function save(Category $category): void
    {
        $em = $this->getEntityManager();
        $em->persist($category);
        $em->flush();
    }

    /**
     * Delete category entity.
     */
    public function delete(Category $category): void
    {
        $em = $this->getEntityManager();
        $em->remove($category);
        $em->flush();
    }

    /**
     * Find one category by slug.
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->createQueryBuilder('category')
            ->andWhere('category.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
