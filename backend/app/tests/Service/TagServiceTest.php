<?php

namespace App\Tests\Service;

use App\Dto\CreateTagDto;
use App\Dto\UpdateTagDto;
use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\TagService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TagServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private TagRepository $tagRepository;
    private TagService $tagService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->tagRepository = $this->createMock(TagRepository::class);

        $this->tagService = new TagService(
            $this->entityManager,
            $this->tagRepository,
        );
    }

    public function testCreate(): void
    {
        $dto = new CreateTagDto();
        $dto->name = 'example-tag';

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Tag $tag) use ($dto) {
                return $tag->getName() === $dto->name;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $tag = $this->tagService->create($dto);

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertSame('example-tag', $tag->getName());
    }

    public function testUpdate(): void
    {
        $tag = new Tag();
        $tag->setName('old-name');

        $dto = new UpdateTagDto();
        $dto->name = 'new-name';

        $this->entityManager->expects($this->once())
            ->method('flush');

        $updatedTag = $this->tagService->update($tag, $dto);

        $this->assertSame('new-name', $updatedTag->getName());
    }

    public function testDelete(): void
    {
        $tag = new Tag();

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($tag);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->tagService->delete($tag);
    }

    public function testFindBySlug(): void
    {
        $slug = 'example-slug';
        $tag = new Tag();
        $tag->setName('Example');

        $this->tagRepository->expects($this->once())
            ->method('findOneBySlug')
            ->with($slug)
            ->willReturn($tag);

        $result = $this->tagService->findBySlug($slug);

        $this->assertSame($tag, $result);
    }
}
