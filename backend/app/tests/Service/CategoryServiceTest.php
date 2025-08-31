<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Service;

use App\Dto\CreateCategoryDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class CategoryServiceTest.
 *
 * Tests CategoryService functionality including creating, updating, deleting,
 * and paginated list retrieval.
 */
class CategoryServiceTest extends TestCase
{
    // ----------------------
    // Create
    // ----------------------

    /**
     * Test that create() saves a new Category and returns it.
     *
     * @test
     *
     * @throws Exception
     */
    public function testCreate(): void
    {
        $dto = new CreateCategoryDto();
        $dto->name = 'Test Category';

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Category $category) {
                return 'Test Category' === $category->getName();
            }));

        $service = new CategoryService($categoryRepository);

        $category = $service->create($dto);

        $this->assertSame('Test Category', $category->getName());
    }

    // ----------------------
    // Update
    // ----------------------

    /**
     * Test that update() modifies the Category's name correctly.
     *
     * @test
     *
     * @throws Exception
     */
    public function testUpdate(): void
    {
        $dto = new UpdateCategoryDto();
        $dto->name = 'Updated Category';

        $category = new Category();
        $category->setName('Old Name');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Category $cat) {
                return 'Updated Category' === $cat->getName();
            }));

        $service = new CategoryService($categoryRepository);

        $updated = $service->update($category, $dto);

        $this->assertSame('Updated Category', $updated->getName());
    }

    // ----------------------
    // Delete
    // ----------------------

    /**
     * Test that delete() calls the repository delete method.
     *
     * @test
     *
     * @throws Exception
     */
    public function testDelete(): void
    {
        $category = new Category();
        $category->setName('To be deleted');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())
            ->method('delete')
            ->with($category);

        $service = new CategoryService($categoryRepository);

        $service->delete($category);
    }

    // ----------------------
    // Pagination
    // ----------------------

    /**
     * Test getPaginatedList() returns correct items and total count.
     *
     * @test
     *
     * @throws Exception
     */
    public function testGetPaginatedList(): void
    {
        $mockRepo = $this->createMock(CategoryRepository::class);

        $mockQb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuery', 'setFirstResult', 'setMaxResults'])
            ->getMock();
        $mockQb->method('setFirstResult')->willReturnSelf();
        $mockQb->method('setMaxResults')->willReturnSelf();

        $mockQuery = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResult'])
            ->getMock();
        $mockQuery->method('getResult')->willReturn([
            ['id' => 1, 'name' => 'Category 1'],
            ['id' => 2, 'name' => 'Category 2'],
        ]);
        $mockQb->method('getQuery')->willReturn($mockQuery);

        $mockRepo->method('queryWithFilters')->willReturn($mockQb);

        $mockPaginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['count', 'getIterator'])
            ->getMock();
        $mockPaginator->method('count')->willReturn(2);
        $mockPaginator->method('getIterator')->willReturn(new \ArrayIterator([
            ['id' => 1, 'name' => 'Category 1'],
            ['id' => 2, 'name' => 'Category 2'],
        ]));

        $service = $this->getMockBuilder(CategoryService::class)
            ->setConstructorArgs([$mockRepo])
            ->onlyMethods(['createPaginator'])
            ->getMock();
        $service->method('createPaginator')->willReturn($mockPaginator);

        $result = $service->getPaginatedList(1, 10);

        $this->assertCount(2, $result['items']);
        $this->assertEquals(2, $result['totalItems']);
    }
}
