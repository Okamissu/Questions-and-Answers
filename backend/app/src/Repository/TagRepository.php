<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Tag entity.
 *
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    /**
     * TagRepository constructor.
     *
     * @param ManagerRegistry $registry The Doctrine manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Builds a QueryBuilder for tags with optional filtering and sorting.
     *
     * @param string|null $search Filter by tag name
     * @param string|null $sort   Sort field and direction, e.g. "name_ASC"
     *
     * @return QueryBuilder The Doctrine QueryBuilder instance
     */
    public function queryWithFilters(?string $search = null, ?string $sort = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t');

        if ($search) {
            $qb->andWhere('t.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        $allowedFields = ['name', 'createdAt', 'updatedAt'];

        if ($sort) {
            [$field, $direction] = explode('_', $sort) + [null, null];
            if (in_array($field, $allowedFields, true) && in_array(strtoupper($direction), ['ASC', 'DESC'], true)) {
                $qb->orderBy('t.'.$field, strtoupper($direction));
            } else {
                $qb->orderBy('t.createdAt', 'DESC'); // fallback
            }
        } else {
            $qb->orderBy('t.createdAt', 'DESC'); // default
        }

        return $qb;
    }

    /**
     * Persists a Tag entity.
     *
     * @param Tag $tag The tag to save
     */
    public function save(Tag $tag): void
    {
        $em = $this->getEntityManager();
        $em->persist($tag);
        $em->flush();
    }

    /**
     * Removes a Tag entity.
     *
     * @param Tag $tag The tag to delete
     */
    public function delete(Tag $tag): void
    {
        $em = $this->getEntityManager();
        $em->remove($tag);
        $em->flush();
    }
}
