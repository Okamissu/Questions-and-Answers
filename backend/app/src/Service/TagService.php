<?php

namespace App\Service;

use App\Dto\CreateTagDto;
use App\Dto\UpdateTagDto;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TagService implements TagServiceInterface
{
    public function __construct(
        protected TagRepository $tagRepository,
    ) {
    }

    /**
     * Creates a new tag from DTO.
     */
    public function create(CreateTagDto $dto): Tag
    {
        $tag = new Tag();
        $tag->setName($dto->name);

        $this->tagRepository->save($tag);

        return $tag;
    }

    /**
     * Updates an existing tag from DTO.
     */
    public function update(Tag $tag, UpdateTagDto $dto): Tag
    {
        if (null !== $dto->name) {
            $tag->setName($dto->name);
        }

        $this->tagRepository->save($tag);

        return $tag;
    }

    /**
     * Deletes a tag entity.
     */
    public function delete(Tag $tag): void
    {
        $this->tagRepository->delete($tag);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createPaginator($qb): Paginator
    {
        return new Paginator($qb);
    }

    public function getPaginatedList(
        int $page,
        int $limit,
        ?string $search = null,
        ?string $sort = null,
    ): array {
        $qb = $this->tagRepository->queryWithFilters($search, $sort);
        $paginator = $this->createPaginator($qb);

        $totalItems = count($paginator);

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return [
            'items' => $qb->getQuery()->getResult(),
            'totalItems' => $totalItems,
        ];
    }
}
