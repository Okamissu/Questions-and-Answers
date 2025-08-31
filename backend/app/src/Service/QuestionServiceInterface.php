<?php

namespace App\Service;

use App\Dto\CreateQuestionDto;
use App\Dto\UpdateQuestionDto;
use App\Entity\Question;
use App\Entity\User;

interface QuestionServiceInterface
{
    public function create(CreateQuestionDto $dto, User $author): Question;

    public function update(Question $question, UpdateQuestionDto $dto): Question;

    public function delete(Question $question): void;

    /**
     * Returns paginated list of questions with optional search, sort, and category filter.
     *
     * @return array{items: Question[], totalItems: int}
     */
    public function getPaginatedList(
        int $page,
        int $limit,
        ?string $search = null,
        ?string $sort = null,
        ?int $categoryId = null,
    ): array;
}
