<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Repository;

use App\Entity\Answer;
use App\Entity\Question;
use App\Repository\AnswerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class AnswerRepositoryTest.
 */
class AnswerRepositoryTest extends TestCase
{
    private EntityManagerInterface $em;
    private ManagerRegistry $registry;
    private AnswerRepository $repository;
    private QueryBuilder $qb;

    /**
     * Function setUp.
     *
     * @test
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->qb = $this->createMock(QueryBuilder::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->name = Answer::class;

        $this->em->method('getClassMetadata')->willReturn($metadata);
        $this->registry->method('getManagerForClass')->willReturn($this->em);

        // Create partial mock to override createQueryBuilder()
        $this->repository = $this->getMockBuilder(AnswerRepository::class)
            ->setConstructorArgs([$this->registry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $this->repository->method('createQueryBuilder')->willReturn($this->qb);

        $this->qb->method('leftJoin')->willReturnSelf();
        $this->qb->method('addSelect')->willReturnSelf();
        $this->qb->method('andWhere')->willReturnSelf();
        $this->qb->method('setParameter')->willReturnSelf();
        $this->qb->method('orderBy')->willReturnSelf();
    }

    /**
     * Function testQueryWithFiltersDefaultSort.
     *
     * @test
     */
    public function testQueryWithFiltersDefaultSort(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('a.createdAt', 'DESC');

        $this->repository->queryWithFilters();
    }

    /**
     * Function testQueryWithFiltersWithQuestion.
     *
     * @test
     */
    public function testQueryWithFiltersWithQuestion(): void
    {
        $question = new Question();

        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with('a.question = :question');
        $this->qb->expects($this->once())
            ->method('setParameter')
            ->with('question', $question);

        $this->repository->queryWithFilters($question);
    }

    /**
     * Function testQueryWithFiltersWithSearch.
     *
     * @test
     */
    public function testQueryWithFiltersWithSearch(): void
    {
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with('a.content LIKE :search');
        $this->qb->expects($this->once())
            ->method('setParameter')
            ->with('search', '%term%');

        $this->repository->queryWithFilters(null, 'term');
    }

    /**
     * Function testQueryWithFiltersValidSort.
     *
     * @test
     */
    public function testQueryWithFiltersValidSort(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('a.content', 'ASC');

        $this->repository->queryWithFilters(null, null, 'content_asc');
    }

    /**
     * Function testQueryWithFiltersInvalidSortFallsBack.
     *
     * @test
     */
    public function testQueryWithFiltersInvalidSortFallsBack(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('a.createdAt', 'DESC');

        $this->repository->queryWithFilters(null, null, 'invalid_sort');
    }

    /**
     * Function testSave.
     *
     * @test
     */
    public function testSave(): void
    {
        $answer = new Answer();

        $this->em->expects($this->once())->method('persist')->with($answer);
        $this->em->expects($this->once())->method('flush');

        // call directly — uses real $this->em
        $repo = new AnswerRepository($this->registry);
        $this->setProtectedProperty($repo, $this->em);

        $repo->save($answer);
    }

    /**
     * Function testDelete.
     *
     * @test
     */
    public function testDelete(): void
    {
        $answer = new Answer();

        $this->em->expects($this->once())->method('remove')->with($answer);
        $this->em->expects($this->once())->method('flush');

        $repo = new AnswerRepository($this->registry);
        $this->setProtectedProperty($repo, $this->em);

        $repo->delete($answer);
    }

    /**
     * Function setProtectedProperty.
     * Helper to set a protected property via reflection.
     *
     * @test
     *
     * @param object $object The object to modify
     * @param mixed  $value  The value to set for the property
     */
    private function setProtectedProperty(object $object, $value): void
    {
        $refObject = new \ReflectionObject($object);

        while (!$refObject->hasProperty('em')) {
            $parent = $refObject->getParentClass();
            if (!$parent) {
                throw new \RuntimeException('Property em not found');
            }
            $refObject = $parent;
        }

        $refProperty = $refObject->getProperty('em');
        /* @noinspection PhpExpressionResultUnusedInspection */
        $refProperty->setAccessible(true); // side effect: allows access
        $refProperty->setValue($object, $value);
    }
}
