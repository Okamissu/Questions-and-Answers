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
     * Query all answers.
     *
     * @return QueryBuilder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->createQueryBuilder('answer')
            ->select('answer', 'question')
            ->join('answer.question', 'question');
    }

    /**
     * Find answers by question.
     *
     * @param Question $question
     *
     * @return Answer[]
     */
    public function findByQuestion(Question $question): array
    {
        return $this->createQueryBuilder('answer')
            ->andWhere('answer.question = :question')
            ->setParameter('question', $question)
            ->orderBy('answer.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Save answer entity.
     *
     * @param Answer $answer
     */
    public function save(Answer $answer): void
    {
        $em = $this->getEntityManager();
        $em->persist($answer);
        $em->flush();
    }

    /**
     * Delete answer entity.
     *
     * @param Answer $answer
     */
    public function delete(Answer $answer): void
    {
        $em = $this->getEntityManager();
        $em->remove($answer);
        $em->flush();
    }
}
