<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Service;

use App\Dto\CreateAnswerDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Entity\Question;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Service responsible for managing Answer entities,
 * including creation, update, deletion, pagination,
 * and handling "best answer" logic.
 */

class AnswerService implements AnswerServiceInterface
{
    private AnswerRepository $answerRepository;
    private QuestionRepository $questionRepository; // poprawne typowanie

    public function __construct(
        AnswerRepository $answerRepository,
        QuestionRepository $questionRepository // wstrzykujemy repozytorium
    ) {
        $this->answerRepository = $answerRepository;
        $this->questionRepository = $questionRepository;
    }

    public function create(CreateAnswerDto $dto): Answer
    {
        // Znajdź Question po ID z DTO
        $question = $this->questionRepository->find($dto->questionId);
        if (!$question) {
            throw new \InvalidArgumentException('Question not found');
        }

        $answer = new Answer();
        $answer->setContent($dto->content);
        $answer->setQuestion($question);
        $answer->setIsBest($dto->isBest);

        $answer->setAuthor($dto->author);
        $answer->setAuthorNickname($dto->authorNickname);
        $answer->setAuthorEmail($dto->authorEmail);

        $this->answerRepository->save($answer);

        return $answer;
    }



    /**
     * Updates an existing Answer entity with values from the given DTO.
     *
     * @param Answer          $answer The Answer to update
     * @param UpdateAnswerDto $dto    DTO containing updated values
     *
     * @return Answer The updated Answer entity
     */
    public function update(Answer $answer, UpdateAnswerDto $dto): Answer
    {
        if (null !== $dto->content) {
            $answer->setContent($dto->content);
        }

        $answer->setIsBest($dto->isBest);

        $this->answerRepository->save($answer);

        return $answer;
    }

    /**
     * Deletes the given Answer entity.
     *
     * @param Answer $answer The Answer to delete
     */
    public function delete(Answer $answer): void
    {
        $this->answerRepository->delete($answer);
    }

    /**
     * Returns a paginated list of Answer entities with optional filters.
     *
     * @param int           $page     Current page number (1-based)
     * @param int           $limit    Number of items per page
     * @param Question|null $question Filter by Question entity (optional)
     * @param string|null   $search   Search string for content (optional)
     * @param string|null   $sort     Sort string, e.g. "createdAt_DESC" (optional)
     *
     * @return array{items: Answer[], totalItems: int} Array with paginated answers and total count
     */
    public function getPaginatedList(int $page, int $limit, ?Question $question = null, ?string $search = null, ?string $sort = null): array
    {
        $qb = $this->answerRepository->queryWithFilters($question, $search, $sort);
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
     * Marks the given Answer as the "best" answer for its Question.
     * Ensures only one answer per Question is marked as best.
     *
     * @param Answer $answer The Answer to mark as best
     *
     * @return Answer The updated Answer entity
     */
    public function markAsBest(Answer $answer): Answer
    {
        $question = $answer->getQuestion();

        $bestAnswers = $this->answerRepository->findBy([
            'question' => $question,
            'isBest' => true,
        ]);

        foreach ($bestAnswers as $bestAnswer) {
            if ($bestAnswer->getId() !== $answer->getId()) {
                $bestAnswer->setIsBest(false);
                $this->answerRepository->save($bestAnswer);
            }
        }

        $answer->setIsBest(true);
        $this->answerRepository->save($answer);

        return $answer;
    }

    /**
     * Creates a Doctrine paginator for a given query builder.
     *
     * @codeCoverageIgnore
     *
     * @param QueryBuilder $qb Doctrine QueryBuilder
     *
     * @return Paginator Paginator instance
     */
    protected function createPaginator(QueryBuilder $qb): Paginator
    {
        return new Paginator($qb);
    }
}
