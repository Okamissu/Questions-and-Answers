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
 * Test class for the QuestionRepository.
 *
 * Contains test cases for querying and manipulating Question entities
 * via the repository, including filtering by category, tag, and other properties.
 */
class QuestionRepositoryTest extends TestCase
{
    private EntityManagerInterface $em;
    private ManagerRegistry $registry;
    private QuestionRepository $repository;
    private QueryBuilder $qb;

    /**
     * Set up the test environment, mocking required dependencies.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->name = Question::class;
        $this->em->method('getClassMetadata')->willReturn($metadata);
        $this->registry->method('getManagerForClass')->willReturn($this->em);

        $this->repository = $this->getMockBuilder(QuestionRepository::class)
            ->setConstructorArgs([$this->registry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $this->qb = $this->createMock(QueryBuilder::class);

        $this->repository->method('createQueryBuilder')->willReturn($this->qb);

        $this->qb->method('select')->willReturnSelf();
        $this->qb->method('leftJoin')->willReturnSelf();
        $this->qb->method('addSelect')->willReturnSelf();
        $this->qb->method('andWhere')->willReturnSelf();
        $this->qb->method('setParameter')->willReturnSelf();
        $this->qb->method('orderBy')->willReturnSelf();
    }

    /**
     * Test the default sorting behavior in the query builder.
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
     * Test the query with search filtering applied.
     *
     * @test
     */
    public function testQueryWithFiltersWithSearch(): void
    {
        $search = 'test';
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with('q.title LIKE :search OR q.content LIKE :search');
        $this->qb->expects($this->once())
            ->method('setParameter')
            ->with('search', '%'.$search.'%');

        $this->repository->queryWithFilters($search);
    }

    /**
     * Test the query with category filtering applied.
     *
     * @test
     */
    public function testQueryWithFiltersWithCategory(): void
    {
        $categoryId = 42;
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with('c.id = :categoryId');
        $this->qb->expects($this->once())
            ->method('setParameter')
            ->with('categoryId', $categoryId);

        $this->repository->queryWithFilters(null, null, $categoryId);
    }

    /**
     * Test the query with tag filtering applied.
     *
     * @test
     */
    public function testQueryWithFiltersWithTag(): void
    {
        $tagId = 7;
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with(':tagId MEMBER OF q.tags');
        $this->qb->expects($this->once())
            ->method('setParameter')
            ->with('tagId', $tagId);

        $this->repository->queryWithFilters(null, null, null, $tagId);
    }

    /**
     * Test the query with a valid sorting parameter.
     *
     * @test
     */
    public function testQueryWithFiltersValidSort(): void
    {
        $sort = 'title_ASC';
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('q.title', 'ASC');

        $this->repository->queryWithFilters(null, $sort);
    }

    /**
     * Test the query with an invalid sorting parameter, ensuring it falls back to default.
     *
     * @test
     */
    public function testQueryWithFiltersInvalidSortFallsBack(): void
    {
        $sort = 'invalid_sort';
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('q.createdAt', 'DESC');

        $this->repository->queryWithFilters(null, $sort);
    }

    /**
     * Test saving a Question entity using the repository.
     *
     * @test
     */
    public function testSave(): void
    {
        $question = new Question();

        $this->em->expects($this->once())->method('persist')->with($question);
        $this->em->expects($this->once())->method('flush');

        $this->repository->save($question);
    }

    /**
     * Test deleting a Question entity using the repository.
     *
     * @test
     */
    public function testDelete(): void
    {
        $question = new Question();

        $this->em->expects($this->once())->method('remove')->with($question);
        $this->em->expects($this->once())->method('flush');

        $this->repository->delete($question);
    }
}
