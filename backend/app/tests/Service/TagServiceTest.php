<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Service;

use App\Dto\CreateTagDto;
use App\Dto\UpdateTagDto;
use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\TagService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class TagServiceTest.
 *
 * Tests creating, updating, deleting, and paginating Tags.
 */
class TagServiceTest extends TestCase
{
    // ----------------------
    // Create
    // ----------------------

    /**
     * Test that create() saves a new Tag and returns it.
     *
     * @test
     *
     * @throws Exception
     */
    public function testCreate(): void
    {
        $dto = new CreateTagDto();
        $dto->name = 'Test Tag';

        $tagRepository = $this->createMock(TagRepository::class);
        $tagRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Tag $tag) => 'Test Tag' === $tag->getName()));

        $service = new TagService($tagRepository);

        $tag = $service->create($dto);

        $this->assertSame('Test Tag', $tag->getName());
    }

    // ----------------------
    // Update
    // ----------------------

    /**
     * Test that update() modifies a Tag correctly.
     *
     * @test
     *
     * @throws Exception
     */
    public function testUpdate(): void
    {
        $dto = new UpdateTagDto();
        $dto->name = 'Updated Tag';

        $tag = new Tag();
        $tag->setName('Old Tag');

        $tagRepository = $this->createMock(TagRepository::class);
        $tagRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (Tag $t) => 'Updated Tag' === $t->getName()));

        $service = new TagService($tagRepository);

        $updated = $service->update($tag, $dto);

        $this->assertSame('Updated Tag', $updated->getName());
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
        $tag = new Tag();
        $tag->setName('To be deleted');

        $tagRepository = $this->createMock(TagRepository::class);
        $tagRepository->expects($this->once())
            ->method('delete')
            ->with($tag);

        $service = new TagService($tagRepository);

        $service->delete($tag);
    }

    // ----------------------
    // Pagination
    // ----------------------

    /**
     * Test getPaginatedList() returns items and total count correctly.
     *
     * @test
     *
     * @throws Exception
     */
    public function testGetPaginatedList(): void
    {
        $mockRepo = $this->createMock(TagRepository::class);

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
            ['id' => 1, 'name' => 'Tag 1'],
            ['id' => 2, 'name' => 'Tag 2'],
        ]);

        $mockQb->method('getQuery')->willReturn($mockQuery);
        $mockRepo->method('queryWithFilters')->willReturn($mockQb);

        $mockPaginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['count', 'getIterator'])
            ->getMock();

        $mockPaginator->method('count')->willReturn(2);
        $mockPaginator->method('getIterator')->willReturn(new \ArrayIterator([
            ['id' => 1, 'name' => 'Tag 1'],
            ['id' => 2, 'name' => 'Tag 2'],
        ]));

        $service = $this->getMockBuilder(TagService::class)
            ->setConstructorArgs([$mockRepo])
            ->onlyMethods(['createPaginator'])
            ->getMock();

        $service->method('createPaginator')->willReturn($mockPaginator);

        $result = $service->getPaginatedList(1, 10);

        $this->assertCount(2, $result['items']);
        $this->assertEquals(2, $result['totalItems']);
    }
}
