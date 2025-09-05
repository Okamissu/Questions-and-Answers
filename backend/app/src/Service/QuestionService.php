<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Service;

use App\Dto\CreateQuestionDto;
use App\Dto\UpdateQuestionDto;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Tag;
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
        $category = $this->questionRepository
            ->getEntityManager()
            ->getRepository(Category::class)
            ->find($dto->categoryId);

        if (!$category) {
            throw new \InvalidArgumentException('Invalid category ID');
        }

        $question = new Question();
        $question->setTitle($dto->title);
        $question->setContent($dto->content);
        $question->setAuthor($author);
        $question->setCategory($category);

        // Tags
        $question->getTags()->clear();
        if ($dto->tagIds) {
            $tags = $this->questionRepository
                ->getEntityManager()
                ->getRepository(Tag::class)
                ->findBy(['id' => $dto->tagIds]);

            foreach ($tags as $tag) {
                $question->addTag($tag);
            }
        }

        $this->questionRepository->save($question);

        return $question;
    }

    /**
     * Updates an existing Question entity with new data.
     *
     * @param Question          $question The question entity to update
     * @param UpdateQuestionDto $dto      DTO containing the updated data
     *
     * @return Question The updated Question entity
     */
    public function update(Question $question, UpdateQuestionDto $dto): Question
    {
        if (null !== $dto->title) {
            $question->setTitle($dto->title);
        }

        if (null !== $dto->content) {
            $question->setContent($dto->content);
        }

        if (null !== $dto->categoryId) {
            $category = $this->questionRepository
                ->getEntityManager()
                ->getRepository(Category::class)
                ->find($dto->categoryId);

            if (!$category) {
                throw new \InvalidArgumentException('Invalid category ID');
            }

            $question->setCategory($category);
        }

        if (null !== $dto->tagIds) {
            $tags = $this->questionRepository
                ->getEntityManager()
                ->getRepository(Tag::class)
                ->findBy(['id' => $dto->tagIds]);

            $question->getTags()->clear();
            foreach ($tags as $tag) {
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
     * @param int|null    $tagId      Optional tag ID filter (if null, no filter will be applied)
     *
     * @return array{items: Question[], totalItems: int} Paginated questions and total count
     */
    public function getPaginatedList(int $page, int $limit, ?string $search = null, ?string $sort = null, ?int $categoryId = null, ?int $tagId = null): array
    {
        $qb = $this->questionRepository->queryWithFilters($search, $sort, $categoryId, $tagId);

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($qb, true); // true = fetch join collection

        return [
            'items' => iterator_to_array($paginator), // zamiast getQuery()->getResult()
            'totalItems' => count($paginator),
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
