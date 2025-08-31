<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Service;

use App\Dto\CreateQuestionDto;
use App\Dto\UpdateQuestionDto;
use App\Entity\Question;
use App\Entity\User;

/**
 * Interface for managing Question entities.
 */
interface QuestionServiceInterface
{
    /**
     * Creates a new Question entity from the given DTO and author.
     *
     * @param CreateQuestionDto $dto    DTO containing question data
     * @param User              $author Author of the question
     *
     * @return Question The created question
     */
    public function create(CreateQuestionDto $dto, User $author): Question;

    /**
     * Updates an existing Question entity with values from the given DTO.
     *
     * @param Question          $question The question to update
     * @param UpdateQuestionDto $dto      DTO with updated values
     *
     * @return Question The updated question
     */
    public function update(Question $question, UpdateQuestionDto $dto): Question;

    /**
     * Deletes the given Question entity.
     *
     * @param Question $question The question to delete
     */
    public function delete(Question $question): void;

    /**
     * Returns a paginated list of questions with optional filters.
     *
     * @param int         $page       Page number (1-based)
     * @param int         $limit      Items per page
     * @param string|null $search     Search string (optional)
     * @param string|null $sort       Sort string, e.g. "createdAt_DESC" (optional)
     * @param int|null    $categoryId Filter by category ID (optional)
     *
     * @return array{items: Question[], totalItems: int} Paginated questions and total count
     */
    public function getPaginatedList(int $page, int $limit, ?string $search = null, ?string $sort = null, ?int $categoryId = null): array;
}
