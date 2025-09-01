<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Service;

use App\Dto\CreateAnswerDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Entity\Question;

/**
 * Interface defining the contract for managing Answer entities.
 */
interface AnswerServiceInterface
{
    /**
     * Creates a new Answer entity from the given DTO.
     *
     * @param CreateAnswerDto $dto DTO containing answer data
     *
     * @return Answer The created Answer entity
     */
    public function create(CreateAnswerDto $dto): Answer;

    /**
     * Updates an existing Answer entity with values from the given DTO.
     *
     * @param Answer          $answer The Answer entity to update
     * @param UpdateAnswerDto $dto    DTO containing updated values
     *
     * @return Answer The updated Answer entity
     */
    public function update(Answer $answer, UpdateAnswerDto $dto): Answer;

    /**
     * Deletes the given Answer entity.
     *
     * @param Answer $answer The Answer entity to delete
     */
    public function delete(Answer $answer): void;

    /**
     * Returns a paginated list of Answer entities with optional filters.
     *
     * @param int           $page     Current page number (1-based)
     * @param int           $limit    Number of items per page
     * @param Question|null $question Filter by Question entity (optional)
     * @param string|null   $search   Search string for content (optional)
     * @param string|null   $sort     Sort string, e.g. "createdAt_DESC" (optional)
     *
     * @return array{items: Answer[], totalItems: int} Paginated answers and total count
     */
    public function getPaginatedList(int $page, int $limit, ?Question $question = null, ?string $search = null, ?string $sort = null): array;

    /**
     * Marks the given Answer as the "best" answer for its Question.
     *
     * @param Answer $answer The Answer entity to mark as best
     *
     * @return Answer The updated Answer entity
     */
    public function markAsBest(Answer $answer): Answer;
}
