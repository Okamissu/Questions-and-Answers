<?php

namespace App\Service;

use App\Dto\CreateTagDto;
use App\Dto\UpdateTagDto;
use App\Entity\Tag;

interface TagServiceInterface
{
    public function create(CreateTagDto $dto): Tag;

    public function update(Tag $tag, UpdateTagDto $dto): Tag;

    public function delete(Tag $tag): void;

    /**
     * Returns paginated list of tags with optional search and sorting.
     *
     * @return array{items: Tag[], totalItems: int}
     */
    public function getPaginatedList(
        int $page,
        int $limit,
        ?string $search = null,
        ?string $sort = null,
    ): array;
}
