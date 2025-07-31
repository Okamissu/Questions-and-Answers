<?php

namespace App\Service;

use App\Dto\CreateQuestionDto;
use App\Dto\UpdateQuestionDto;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;

class QuestionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private QuestionRepository $questionRepository,
    ) {
    }

    public function create(CreateQuestionDto $dto, User $author): Question
    {
        $question = new Question();
        $question->setTitle($dto->title);
        $question->setContent($dto->content);
        $question->setAuthor($author);
        $question->setCategory($dto->category);

        // tags to Collection, usuń wszystkie i dodaj nowe z DTO
        $question->getTags()->clear();
        if ($dto->tags) {
            foreach ($dto->tags as $tag) {
                $question->addTag($tag);
            }
        }

        $this->em->persist($question);
        $this->em->flush();

        return $question;
    }

    public function update(Question $question, UpdateQuestionDto $dto): Question
    {
        if (null !== $dto->title) {
            $question->setTitle($dto->title);
        }
        if (null !== $dto->content) {
            $question->setContent($dto->content);
        }
        if (null !== $dto->category) {
            $question->setCategory($dto->category);
        }

        if (null !== $dto->tags) {
            $question->getTags()->clear();
            foreach ($dto->tags as $tag) {
                $question->addTag($tag);
            }
        }

        $this->em->flush();

        return $question;
    }

    public function delete(Question $question): void
    {
        $this->em->remove($question);
        $this->em->flush();
    }

    // TODO: dodać metody do paginacji lub filtrów na repozytorium itd.
}
