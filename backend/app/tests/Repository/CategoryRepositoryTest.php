<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class CategoryRepositoryTest.
 *
 * Tests core repository functions of CategoryRepository.
 */
class CategoryRepositoryTest extends TestCase
{
    private EntityManagerInterface $em;
    private ManagerRegistry $registry;
    private CategoryRepository $repository;
    private QueryBuilder $qb;

    /**
     * Set up mocks and repository.
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

    /**
     * Test default sort order in queryWithFilters().
     *
     * @test
     */
    public function testQueryWithFiltersDefaultSort(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('c.createdAt', 'DESC');

        $this->repository->queryWithFilters();
    }

    /**
     * Test queryWithFilters() with a search term.
     *
     * @test
     */
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

    /**
     * Test valid sort in queryWithFilters().
     *
     * @test
     */
    public function testQueryWithFiltersValidSort(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('c.name', 'ASC');

        $this->repository->queryWithFilters(null, 'name_asc');
    }

    /**
     * Test invalid sort in queryWithFilters() falls back to default.
     *
     * @test
     */
    public function testQueryWithFiltersInvalidSortFallsBack(): void
    {
        $this->qb->expects($this->once())
            ->method('orderBy')
            ->with('c.createdAt', 'DESC');

        $this->repository->queryWithFilters(null, 'invalid_sort');
    }

    /**
     * Test saving a Category entity.
     *
     * @test
     */
    public function testSave(): void
    {
        $category = new Category();

        $this->em->expects($this->once())->method('persist')->with($category);
        $this->em->expects($this->once())->method('flush');

        $repo = new CategoryRepository($this->registry);
        $this->setProtectedProperty($repo, $this->em);

        $repo->save($category);
    }

    /**
     * Test deleting a Category entity.
     *
     * @test
     */
    public function testDelete(): void
    {
        $category = new Category();

        $this->em->expects($this->once())->method('remove')->with($category);
        $this->em->expects($this->once())->method('flush');

        $repo = new CategoryRepository($this->registry);
        $this->setProtectedProperty($repo, $this->em);

        $repo->delete($category);
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
    private function setProtectedProperty(object $object, mixed $value): void
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
        $refProperty->setValue($object, $value);
    }
}
