<?php

namespace App\Service;

use App\Dto\CreateQuestionDto;
use App\Dto\UpdateQuestionDto;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\QuestionRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class QuestionService
{
    public function __construct(
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

        $question->getTags()->clear();
        foreach ($dto->tags ?? [] as $tag) {
            $question->addTag($tag);
        }

        $this->questionRepository->save($question);

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

        $this->questionRepository->save($question);

        return $question;
    }

    public function delete(Question $question): void
    {
        $this->questionRepository->delete($question);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createPaginator($qb): Paginator
    {
        return new Paginator($qb);
    }

    public function getPaginatedList(int $page, int $limit, ?string $search = null, ?string $sort = null, ?int $categoryId = null): array
    {
        $qb = $this->questionRepository->queryWithFilters($search, $sort, $categoryId);
        $paginator = $this->createPaginator($qb);

        $totalItems = count($paginator);

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return [
            'items' => $qb->getQuery()->getResult(),
            'totalItems' => $totalItems,
        ];
    }
}
