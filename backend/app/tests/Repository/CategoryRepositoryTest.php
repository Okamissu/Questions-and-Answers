<?php

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class CategoryRepositoryTest extends TestCase
{
    private EntityManagerInterface $em;
    private ManagerRegistry $registry;
    private CategoryRepository $repository;
    private QueryBuilder $qb;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->qb = $this->createMock(QueryBuilder::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->name = Category::class;

        $this->em->method('getClassMetadata')->willReturn($metadata);
        $this->registry->method('getManagerForClass')->willReturn($this->em);

        $this->repository = $this->getMockBuilder(CategoryRepository::class)
            ->setConstructorArgs([$this->registry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $this->repository->method('createQueryBuilder')->willReturn($this->qb);

        $this->qb->method('andWhere')->willReturnSelf();
        $this->qb->method('setParameter')->willReturnSelf();
        $this->qb->method('orderBy')->willReturnSelf();
    }

    public function testQueryWithFiltersDefaultSort(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('c.createdAt', 'DESC');

        $this->repository->queryWithFilters();
    }

    public function testQueryWithFiltersWithSearch(): void
    {
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with('c.name LIKE :search OR c.description LIKE :search');
        $this->qb->expects($this->once())
            ->method('setParameter')
            ->with('search', '%term%');

        $this->repository->queryWithFilters('term');
    }

    public function testQueryWithFiltersValidSort(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('c.name', 'ASC');

        $this->repository->queryWithFilters(null, 'name_asc');
    }

    public function testQueryWithFiltersInvalidSortFallsBack(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('c.createdAt', 'DESC');

        $this->repository->queryWithFilters(null, 'invalid_sort');
    }

    public function testSave(): void
    {
        $category = new Category();

        $this->em->expects($this->once())->method('persist')->with($category);
        $this->em->expects($this->once())->method('flush');

        $repo = new CategoryRepository($this->registry);
        $this->setProtectedProperty($repo, 'em', $this->em);

        $repo->save($category);
    }

    public function testDelete(): void
    {
        $category = new Category();

        $this->em->expects($this->once())->method('remove')->with($category);
        $this->em->expects($this->once())->method('flush');

        $repo = new CategoryRepository($this->registry);
        $this->setProtectedProperty($repo, 'em', $this->em);

        $repo->delete($category);
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
        /* @noinspection PhpExpressionResultUnusedInspection */
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $value);
    }
}
