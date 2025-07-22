<?php

namespace App\Repository;

use App\Entity\Category;
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

    /**
     * Query all questions.
     *
     * @return QueryBuilder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->createQueryBuilder('question')
            ->select('question', 'author', 'category', 'tags')
            ->join('question.author', 'author')
            ->join('question.category', 'category')
            ->leftJoin('question.tags', 'tags')
            ->orderBy('question.createdAt', 'DESC');
    }

    /**
     * Find questions by category.
     *
     * @param Category $category
     *
     * @return Question[]
     */
    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('question')
            ->andWhere('question.category = :category')
            ->setParameter('category', $category)
            ->orderBy('question.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Save question entity.
     *
     * @param Question $question
     */
    public function save(Question $question): void
    {
        $em = $this->getEntityManager();
        $em->persist($question);
        $em->flush();
    }

    /**
     * Delete question entity.
     *
     * @param Question $question
     */
    public function delete(Question $question): void
    {
        $em = $this->getEntityManager();
        $em->remove($question);
        $em->flush();
    }
}
