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

    public function testQueryWithFiltersDefaultSort(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('q.createdAt', 'DESC');

        $this->repository->queryWithFilters();
    }

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

    public function testQueryWithFiltersValidSort(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('q.title', 'ASC');

        $this->repository->queryWithFilters(null, 'title_asc');
    }

    public function testQueryWithFiltersInvalidSortFallsBack(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('q.createdAt', 'DESC');

        $this->repository->queryWithFilters(null, 'invalid_sort');
    }

    public function testSave(): void
    {
        $question = new Question();

        $this->em->expects($this->once())->method('persist')->with($question);
        $this->em->expects($this->once())->method('flush');

        $repo = new QuestionRepository($this->registry);
        $this->setProtectedProperty($repo, 'em', $this->em);

        $repo->save($question);
    }

    public function testDelete(): void
    {
        $question = new Question();

        $this->em->expects($this->once())->method('remove')->with($question);
        $this->em->expects($this->once())->method('flush');

        $repo = new QuestionRepository($this->registry);
        $this->setProtectedProperty($repo, 'em', $this->em);

        $repo->delete($question);
    }

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
        /** @noinspection PhpExpressionResultUnusedInspection */
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $value);
    }
}
