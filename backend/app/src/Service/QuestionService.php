<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Service;

use App\Dto\CreateQuestionDto;
use App\Dto\UpdateQuestionDto;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\QuestionRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Service responsible for managing Question entities.
 *
 * Provides methods to create, update, delete, and fetch paginated questions.
 */
class QuestionService implements QuestionServiceInterface
{
    /**
     * QuestionService constructor.
     *
     * @param QuestionRepository $questionRepository Repository for persisting Question entities
     */
    public function __construct(private readonly QuestionRepository $questionRepository)
    {
    }

    /**
     * Creates a new Question entity from the given DTO and author.
     *
     * @param CreateQuestionDto $dto    DTO containing question data
     * @param User              $author Author of the question
     *
     * @return Question The newly created Question entity with persisted ID
     */
    public function create(CreateQuestionDto $dto, User $author): Question
    {
        $question = new Question();
        $question->setTitle($dto->title);
        $question->setContent($dto->content);
        $question->setAuthor($author);
        $question->setCategory($dto->category);

        // Clear and add tags if any
        $question->getTags()->clear();
        foreach ($dto->tags ?? [] as $tag) {
            $question->addTag($tag);
        }

        $this->questionRepository->save($question);

        return $question;
    }

    /**
     * Updates an existing Question entity with values from the given DTO.
     *
     * Only non-null DTO fields are applied.
     *
     * @param Question          $question The question to update
     * @param UpdateQuestionDto $dto      DTO with updated values
     *
     * @return Question The updated Question entity with persisted changes
     */
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

    /**
     * Deletes the given Question entity.
     *
     * @param Question $question The question entity to delete
     *
     * @return void This method does not return a value
     */
    public function delete(Question $question): void
    {
        $this->questionRepository->delete($question);
    }

    /**
     * Returns a paginated list of questions with optional filters.
     *
     * The result contains both the list of Question entities and the total number of items
     * matching the filters, without pagination applied.
     *
     * @param int         $page       Page number (1-based)
     * @param int         $limit      Items per page
     * @param string|null $search     Optional search string
     * @param string|null $sort       Optional sort string (e.g., "createdAt_DESC")
     * @param int|null    $categoryId Optional category ID filter
     *
     * @return array{items: Question[], totalItems: int} Paginated questions and total count
     */
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

    /**
     * Creates a Doctrine paginator for the given QueryBuilder.
     *
     * This method is protected as it's only used internally for pagination logic.
     *
     * @param QueryBuilder $qb Doctrine QueryBuilder instance to paginate
     *
     * @return Paginator Doctrine paginator wrapping the query
     *
     * @codeCoverageIgnore
     */
    protected function createPaginator(QueryBuilder $qb): Paginator
    {
        return new Paginator($qb);
    }
}
