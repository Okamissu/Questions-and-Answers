<?php

namespace App\Repository;

use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class AnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    /**
     * Query answers with optional filters (e.g. by question, search text) and sorting.
     *
     * @param Question|null $question
     * @param string|null $search
     * @param string|null $sort
     * @return QueryBuilder
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
            // Assuming Answer has a 'content' field to search in
            $qb->andWhere('a.content LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if ($sort) {
            [$field, $direction] = explode('_', $sort);
            $allowedFields = ['content', 'createdAt'];
            if (in_array($field, $allowedFields, true) && in_array(strtoupper($direction), ['ASC', 'DESC'], true)) {
                $qb->orderBy('a.'.$field, strtoupper($direction));
            }
        } else {
            $qb->orderBy('a.createdAt', 'DESC');
        }

        return $qb;
    }

    /**
     * Save answer entity.
     */
    public function save(Answer $answer): void
    {
        $em = $this->getEntityManager();
        $em->persist($answer);
        $em->flush();
    }

    /**
     * Delete answer entity.
     */
    public function delete(Answer $answer): void
    {
        $em = $this->getEntityManager();
        $em->remove($answer);
        $em->flush();
    }
}
