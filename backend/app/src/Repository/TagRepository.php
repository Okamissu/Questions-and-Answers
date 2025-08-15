<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Returns a query builder to fetch all tags with optional filtering and sorting.
     */
    public function queryWithFilters(?string $search = null, ?string $sort = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t');

        if ($search) {
            $qb->andWhere('t.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if ($sort) {
            [$field, $direction] = explode('_', $sort);
            $allowedFields = ['name', 'createdAt', 'updatedAt'];
            if (in_array($field, $allowedFields, true) && in_array(strtoupper($direction), ['ASC', 'DESC'], true)) {
                $qb->orderBy('t.'.$field, strtoupper($direction));
            } else {
                // fallback if sort is invalid
                $qb->orderBy('t.createdAt', 'DESC');
            }
        } else {
            $qb->orderBy('t.createdAt', 'DESC');
        }

        return $qb;
    }

    /**
     * Saves a tag entity.
     */
    public function save(Tag $tag): void
    {
        $em = $this->getEntityManager();
        $em->persist($tag);
        $em->flush();
    }

    /**
     * Deletes a tag entity.
     */
    public function delete(Tag $tag): void
    {
        $em = $this->getEntityManager();
        $em->remove($tag);
        $em->flush();
    }
}
