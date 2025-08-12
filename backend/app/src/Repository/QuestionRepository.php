<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function queryWithFilters(?string $search = null, ?string $sort = null, ?int $categoryId = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.category', 'c')
            ->addSelect('c');

        if ($search) {
            $qb->andWhere('q.title LIKE :search OR q.content LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if ($categoryId) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($sort) {
            [$field, $direction] = explode('_', $sort);
            $allowedFields = ['title', 'createdAt'];
            if (in_array($field, $allowedFields) && in_array(strtoupper($direction), ['ASC', 'DESC'])) {
                $qb->orderBy('q.'.$field, strtoupper($direction));
            }
        } else {
            $qb->orderBy('q.createdAt', 'DESC');
        }

        return $qb;
    }

    /**
     * Save question entity.
     */
    public function save(Question $question): void
    {
        $em = $this->getEntityManager();
        $em->persist($question);
        $em->flush();
    }

    public function delete(Question $question): void
    {
        $em = $this->getEntityManager();
        $em->remove($question);
        $em->flush();
    }
}
