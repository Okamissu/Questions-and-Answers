<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Service;

use App\Dto\CreateTagDto;
use App\Dto\UpdateTagDto;
use App\Entity\Tag;

/**
 * Interface TagServiceInterface.
 *
 * Provides contract for managing Tag entities.
 */
interface TagServiceInterface
{
    /**
     * Creates a new Tag entity from the given DTO.
     *
     * @param CreateTagDto $dto Data transfer object containing tag data
     *
     * @return Tag The created tag entity
     */
    public function create(CreateTagDto $dto): Tag;

    /**
     * Updates an existing Tag entity with values from the given DTO.
     *
     * @param Tag          $tag The tag entity to update
     * @param UpdateTagDto $dto Data transfer object containing updated values
     *
     * @return Tag The updated tag entity
     */
    public function update(Tag $tag, UpdateTagDto $dto): Tag;

    /**
     * Deletes the given Tag entity.
     *
     * @param Tag $tag The tag entity to delete
     */
    public function delete(Tag $tag): void;

    /**
     * Returns a paginated list of tags with optional search and sort parameters.
     *
     * @param int         $page   Page number (1-based)
     * @param int         $limit  Number of items per page
     * @param string|null $search Optional search string
     * @param string|null $sort   Optional sort string, e.g., "name_ASC"
     *
     * @return array{items: Tag[], totalItems: int} Paginated tags and total count
     */
    public function getPaginatedList(int $page, int $limit, ?string $search = null, ?string $sort = null): array;
}
