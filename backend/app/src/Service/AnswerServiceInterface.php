<?php

namespace App\Service;

use App\Dto\CreateAnswerDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Entity\Question;

interface AnswerServiceInterface
{
    public function create(CreateAnswerDto $dto): Answer;

    public function update(Answer $answer, UpdateAnswerDto $dto): Answer;

    public function delete(Answer $answer): void;

    /**
     * Returns paginated list of answers with optional filters.
     */
    public function getPaginatedList(
        int $page,
        int $limit,
        ?Question $question = null,
        ?string $search = null,
        ?string $sort = null,
    ): array;

    public function markAsBest(Answer $answer): Answer;
}
