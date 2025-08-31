<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Service;

use App\Dto\CreateTagDto;
use App\Dto\UpdateTagDto;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Service responsible for managing Tag entities.
 */
class TagService implements TagServiceInterface
{
    /**
     * TagService constructor.
     *
     * @param TagRepository $tagRepository Repository used to persist Tag entities
     */
    public function __construct(protected TagRepository $tagRepository)
    {
    }

    /**
     * Creates a new Tag entity from the given DTO.
     *
     * @param CreateTagDto $dto DTO containing tag data
     *
     * @return Tag The created tag
     */
    public function create(CreateTagDto $dto): Tag
    {
        $tag = new Tag();
        $tag->setName($dto->name);

        $this->tagRepository->save($tag);

        return $tag;
    }

    /**
     * Updates an existing Tag entity with values from the given DTO.
     *
     * @param Tag          $tag The tag to update
     * @param UpdateTagDto $dto DTO with updated values
     *
     * @return Tag The updated tag
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
     * Deletes the given Tag entity.
     *
     * @param Tag $tag The tag to delete
     */
    public function delete(Tag $tag): void
    {
        $this->tagRepository->delete($tag);
    }

    /**
     * Returns a paginated list of tags with optional search and sort.
     *
     * @param int         $page   Page number (1-based)
     * @param int         $limit  Items per page
     * @param string|null $search Search string (optional)
     * @param string|null $sort   Sort string, e.g. "name_ASC" (optional)
     *
     * @return array{items: Tag[], totalItems: int} Paginated tags and total count
     */
    public function getPaginatedList(int $page, int $limit, ?string $search = null, ?string $sort = null): array
    {
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

    /**
     * Creates a Doctrine paginator for the given query builder.
     *
     * @param QueryBuilder $qb QueryBuilder instance
     *
     * @return Paginator The paginator
     *
     * @codeCoverageIgnore
     */
    protected function createPaginator(QueryBuilder $qb): Paginator
    {
        return new Paginator($qb);
    }
}
