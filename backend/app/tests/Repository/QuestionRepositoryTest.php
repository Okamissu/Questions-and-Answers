<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Repository;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Class QuestionRepositoryTest.
 */
class QuestionRepositoryTest extends TestCase
{
    private EntityManagerInterface $em;
    private ManagerRegistry $registry;
    private QuestionRepository $repository;
    private QueryBuilder $qb;

    /**
     * Function setUp.
     *
     * @test
     */
    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->qb = $this->createMock(QueryBuilder::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->name = Question::class;

        $this->em->method('getClassMetadata')->willReturn($metadata);
        $this->registry->method('getManagerForClass')->willReturn($this->em);

        $this->repository = $this->getMockBuilder(QuestionRepository::class)
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
            ->with('q.createdAt', 'DESC');

        $this->repository->queryWithFilters();
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
            ->with('q.title LIKE :search OR q.content LIKE :search');
        $this->qb->expects($this->once())
            ->method('setParameter')
            ->with('search', '%term%');

        $this->repository->queryWithFilters('term');
    }

    /**
     * Function testQueryWithFiltersWithCategory.
     *
     * @test
     */
    public function testQueryWithFiltersWithCategory(): void
    {
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with('c.id = :categoryId');
        $this->qb->expects($this->once())
            ->method('setParameter')
            ->with('categoryId', 42);

        $this->repository->queryWithFilters(null, null, 42);
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
            ->with('q.title', 'ASC');

        $this->repository->queryWithFilters(null, 'title_asc');
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
            ->with('q.createdAt', 'DESC');

        $this->repository->queryWithFilters(null, 'invalid_sort');
    }

    /**
     * Function testSave.
     *
     * @test
     */
    public function testSave(): void
    {
        $question = new Question();

        $this->em->expects($this->once())->method('persist')->with($question);
        $this->em->expects($this->once())->method('flush');

        $repo = new QuestionRepository($this->registry);
        $this->setProtectedProperty($repo, 'em', $this->em);

        $repo->save($question);
    }

    /**
     * Function testDelete.
     *
     * @test
     */
    public function testDelete(): void
    {
        $question = new Question();

        $this->em->expects($this->once())->method('remove')->with($question);
        $this->em->expects($this->once())->method('flush');

        $repo = new QuestionRepository($this->registry);
        $this->setProtectedProperty($repo, 'em', $this->em);

        $repo->delete($question);
    }

    /**
     * Function setProtectedProperty.
     *
     * @test
     */
    private function setProtectedProperty(object $object, string $property, $value): void
    {
        $refObject = new \ReflectionObject($object);

        while (!$refObject->hasProperty($property)) {
            $parent = $refObject->getParentClass();
            if (!$parent) {
                throw new \RuntimeException("Property {$property} not found");
            }
            $refObject = $parent;
        }

        $refProperty = $refObject->getProperty($property);
        /* @noinspection PhpExpressionResultUnusedInspection */
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $value);
    }
}
