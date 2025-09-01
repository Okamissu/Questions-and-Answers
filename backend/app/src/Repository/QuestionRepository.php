<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Question entity.
 */
class QuestionRepository extends ServiceEntityRepository
{
    /**
     * QuestionRepository constructor.
     *
     * @param ManagerRegistry $registry The Doctrine manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * Builds a QueryBuilder for questions with optional filtering by search and category, and sorting.
     *
     * @param string|null $search     Filter by question title or content
     * @param string|null $sort       Sort field and direction, e.g. "title_ASC"
     * @param int|null    $categoryId Filter by category ID
     *
     * @return QueryBuilder The Doctrine QueryBuilder instance
     */
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

        $allowedFields = ['title', 'createdAt'];

        if ($sort) {
            [$field, $direction] = explode('_', $sort) + [null, null];
            if (in_array($field, $allowedFields, true) && in_array(strtoupper($direction), ['ASC', 'DESC'], true)) {
                $qb->orderBy('q.'.$field, strtoupper($direction));
            } else {
                $qb->orderBy('q.createdAt', 'DESC'); // fallback
            }
        } else {
            $qb->orderBy('q.createdAt', 'DESC'); // default
        }

        return $qb;
    }

    /**
     * Persists a Question entity.
     *
     * @param Question $question The question to save
     */
    public function save(Question $question): void
    {
        $em = $this->getEntityManager();
        $em->persist($question);
        $em->flush();
    }

    /**
     * Removes a Question entity.
     *
     * @param Question $question The question to delete
     */
    public function delete(Question $question): void
    {
        $em = $this->getEntityManager();
        $em->remove($question);
        $em->flush();
    }
}
