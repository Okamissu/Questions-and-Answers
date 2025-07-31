<?php

namespace App\Service;

use App\Dto\CreateAnswerDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Repository\AnswerRepository;
use Doctrine\ORM\EntityManagerInterface;

class AnswerService
{
    public function __construct(
        private EntityManagerInterface $em,
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

        $this->em->persist($answer);
        $this->em->flush();

        return $answer;
    }

    public function update(Answer $answer, UpdateAnswerDto $dto): Answer
    {
        if (null !== $dto->content) {
            $answer->setContent($dto->content);
        }
        $answer->setIsBest($dto->isBest);

        $this->em->flush();

        return $answer;
    }

    public function delete(Answer $answer): void
    {
        $this->em->remove($answer);
        $this->em->flush();
    }

    /**
     *  oznacz najlepszą odpowiedź (np. dla administratora lub autora pytania).
     */
    public function markAsBest(Answer $answer): Answer
    {
        // TODO: tu dodać logikę np. usunięcia flagi isBest z innych odpowiedzi dla tego pytania
        // albo zostawić to do osobnego serwisu

        $answer->setIsBest(true);
        $this->em->flush();

        return $answer;
    }
}
