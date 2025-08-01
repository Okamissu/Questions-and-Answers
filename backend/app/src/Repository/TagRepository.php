<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Query all tags.
     *
     * @return QueryBuilder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->createQueryBuilder('tag')
            ->select('tag')
            ->orderBy('tag.name', 'ASC');
    }

    /**
     * Find tag by slug.
     *
     * @param string $slug
     *
     * @return Tag|null
     */
    public function findOneBySlug(string $slug): ?Tag
    {
        return $this->createQueryBuilder('tag')
            ->andWhere('tag.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Save tag entity.
     *
     * @param Tag $tag
     */
    public function save(Tag $tag): void
    {
        $em = $this->getEntityManager();
        $em->persist($tag);
        $em->flush();
    }

    /**
     * Delete tag entity.
     *
     * @param Tag $tag
     */
    public function delete(Tag $tag): void
    {
        $em = $this->getEntityManager();
        $em->remove($tag);
        $em->flush();
    }
}
