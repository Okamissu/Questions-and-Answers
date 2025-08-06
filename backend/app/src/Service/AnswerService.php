<?php

namespace App\Service;

use App\Dto\CreateAnswerDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Repository\AnswerRepository;

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
        $answer->setAuthor($dto->author);
        $answer->setAuthorNickname($dto->authorNickname);
        $answer->setAuthorEmail($dto->authorEmail);
        $answer->setIsBest($dto->isBest);

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
     *  oznacz najlepszą odpowiedź (np. dla administratora lub autora pytania).
     */
    public function markAsBest(Answer $answer): Answer
    {
        // TODO: tu dodać logikę np. usunięcia flagi isBest z innych odpowiedzi dla tego pytania
        // albo zostawić to do osobnego serwisu

        $answer->setIsBest(true);
        $this->answerRepository->save($answer);

        return $answer;
    }
}
