<?php

namespace App\Service;

use App\Dto\CreateAnswerDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Entity\Question;
use App\Repository\AnswerRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AnswerService
{
    public function __construct(
        private AnswerRepository $answerRepository,
    ) {
    }

    public function create(CreateAnswerDto $dto): Answer
    {
        $answer = new Answer();
        $answer->setContent($dto->content);
        $answer->setQuestion($dto->question);
        $answer->setIsBest($dto->isBest);

        // author can be null for anonymous
        $answer->setAuthor($dto->author);
        $answer->setAuthorNickname($dto->authorNickname);
        $answer->setAuthorEmail($dto->authorEmail);

        $this->answerRepository->save($answer);

        return $answer;
    }

    public function update(Answer $answer, UpdateAnswerDto $dto): Answer
    {
        if (null !== $dto->content) {
            $answer->setContent($dto->content);
        }

        $answer->setIsBest($dto->isBest);

        $this->answerRepository->save($answer);

        return $answer;
    }

    public function delete(Answer $answer): void
    {
        $this->answerRepository->delete($answer);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createPaginator($qb): Paginator
    {
        return new Paginator($qb);
    }

    /**
     * Returns paginated list of answers with optional filters.
     *
     * @param Question|null $question Filter by question (optional)
     * @param string|null   $search   Search in content (optional)
     * @param string|null   $sort     Sort string, e.g. "createdAt_DESC" (optional)
     *
     * @return array ['items' => Answer[], 'totalItems' => int]
     */
    public function getPaginatedList(
        int $page,
        int $limit,
        ?Question $question = null,
        ?string $search = null,
        ?string $sort = null,
    ): array {
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
}
