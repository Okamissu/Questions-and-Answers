<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Controller;

use App\Dto\CreateQuestionDto;
use App\Dto\QuestionListFiltersDto;
use App\Dto\UpdateQuestionDto;
use App\Entity\Question;
use App\Entity\User;
use App\Resolver\QuestionListFiltersDtoResolver;
use App\Security\Voter\QuestionVoter;
use App\Service\QuestionServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/questions')]
/**
 * Controller responsible for managing Question entities.
 *
 * Provides endpoints for listing, creating, updating, and deleting questions.
 */
class QuestionController extends AbstractController
{
    /**
     * QuestionController constructor.
     *
     * @param QuestionServiceInterface $questionService Service for managing questions
     * @param ValidatorInterface       $validator       Validator for DTOs
     * @param SerializerInterface      $serializer      Serializer for DTOs
     */
    public function __construct(private readonly QuestionServiceInterface $questionService, private readonly ValidatorInterface $validator, private readonly SerializerInterface $serializer)
    {
    }

    /**
     * List questions with pagination, filtering, and sorting.
     *
     * @param QuestionListFiltersDto $filters DTO containing optional filtering, sorting, and pagination parameters
     * @param int                    $page    Page number (default: 1)
     *
     * @return JsonResponse Returns paginated questions along with metadata
     */
    #[Route('', methods: ['GET'])]
    public function list(#[MapQueryString(resolver: QuestionListFiltersDtoResolver::class)] QuestionListFiltersDto $filters, #[MapQueryParameter] int $page = 1): JsonResponse
    {
        $limit = max(1, min(100, $filters->limit ?? 10));

        $result = $this->questionService->getPaginatedList($page, $limit, $filters->search, $filters->sort, $filters->categoryId);

        return $this->json([
            'items' => $result['items'],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'totalItems' => $result['totalItems'],
                'totalPages' => max(1, (int) ceil($result['totalItems'] / $limit)),
                'sort' => $filters->sort,
                'search' => $filters->search,
                'categoryId' => $filters->categoryId,
            ],
        ], Response::HTTP_OK, [], ['groups' => 'question:read']);
    }

    /**
     * Retrieve a single question by its ID.
     *
     * @param Question $question The Question entity to retrieve
     *
     * @return JsonResponse Returns the requested question
     *
     * @throws ExceptionInterface
     */
    #[Route('/{id}', methods: ['GET'])]
    public function show(Question $question): JsonResponse
    {
        $json = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * Create a new question.
     *
     * @param Request $request HTTP request containing the question payload
     *
     * @return JsonResponse Returns the created question or validation errors
     *
     * @throws ExceptionInterface
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $dto = $this->serializer->deserialize($request->getContent(), CreateQuestionDto::class, 'json');
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $question = $this->questionService->create($dto, $user);
        $json = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);

        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    /**
     * Update an existing question.
     *
     * @param Request  $request  HTTP request containing updated data
     * @param Question $question Question entity to update
     *
     * @return JsonResponse Returns the updated question or validation errors
     *
     * @throws ExceptionInterface
     */
    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, Question $question): JsonResponse
    {
        $this->denyAccessUnlessGranted(QuestionVoter::UPDATE, $question);

        $dto = $this->serializer->deserialize($request->getContent(), UpdateQuestionDto::class, 'json');
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $question = $this->questionService->update($question, $dto);
        $json = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * Delete an existing question.
     *
     * @param Question $question Question entity to delete
     *
     * @return Response Returns HTTP 204 No Content on success
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Question $question): Response
    {
        $this->denyAccessUnlessGranted(QuestionVoter::DELETE, $question);

        $this->questionService->delete($question);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
