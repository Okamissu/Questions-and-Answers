<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Repository;

use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Answer entity.
 */
class AnswerRepository extends ServiceEntityRepository
{
    /**
     * AnswerRepository constructor.
     *
     * @param ManagerRegistry $registry The Doctrine manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    /**
     * Builds a QueryBuilder for answers with optional filters and sorting.
     *
     * @param Question|null $question Filter by a specific question
     * @param string|null   $search   Filter by content search
     * @param string|null   $sort     Sort field and direction, e.g. "content_ASC"
     *
     * @return QueryBuilder The Doctrine QueryBuilder instance
     */
    public function queryWithFilters(?Question $question = null, ?string $search = null, ?string $sort = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.question', 'q')
            ->addSelect('q');

        if ($question) {
            $qb->andWhere('a.question = :question')
                ->setParameter('question', $question);
        }

        if ($search) {
            $qb->andWhere('a.content LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        $allowedFields = ['content', 'createdAt'];

        if ($sort) {
            [$field, $direction] = explode('_', $sort) + [null, null];
            if (in_array($field, $allowedFields, true)
                && in_array(strtoupper($direction), ['ASC', 'DESC'], true)
            ) {
                $qb->orderBy('a.'.$field, strtoupper($direction));
            } else {
                $qb->orderBy('a.createdAt', 'DESC'); // fallback
            }
        } else {
            $qb->orderBy('a.createdAt', 'DESC'); // default
        }

        return $qb;
    }

    /**
     * Persists an Answer entity.
     *
     * @param Answer $answer The answer to save
     */
    public function save(Answer $answer): void
    {
        $em = $this->getEntityManager();
        $em->persist($answer);
        $em->flush();
    }

    /**
     * Removes an Answer entity.
     *
     * @param Answer $answer The answer to delete
     */
    public function delete(Answer $answer): void
    {
        $em = $this->getEntityManager();
        $em->remove($answer);
        $em->flush();
    }
}
