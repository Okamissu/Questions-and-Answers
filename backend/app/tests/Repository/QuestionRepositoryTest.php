<?php

namespace App\Tests\Repository;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class QuestionRepositoryTest extends TestCase
{
    private EntityManagerInterface $em;
    private ManagerRegistry $registry;
    private QuestionRepository $repository;
    private QueryBuilder $qb;

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

    public function testQueryWithFiltersDefaultSort(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('q.createdAt', 'DESC');

        $this->repository->queryWithFilters();
    }

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

    public function testQueryWithFiltersValidSort(): void
    {
        $sort = 'title_ASC';
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('q.title', 'ASC');

        $this->repository->queryWithFilters(null, $sort);
    }

    public function testQueryWithFiltersInvalidSortFallsBack(): void
    {
        $sort = 'invalid_sort';
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('q.createdAt', 'DESC');

        $this->repository->queryWithFilters(null, $sort);
    }

    public function testSave(): void
    {
        $question = new Question();

        $this->em->expects($this->once())->method('persist')->with($question);
        $this->em->expects($this->once())->method('flush');

        $this->repository->save($question);
    }

    public function testDelete(): void
    {
        $question = new Question();

        $this->em->expects($this->once())->method('remove')->with($question);
        $this->em->expects($this->once())->method('flush');

        $this->repository->delete($question);
    }
}
