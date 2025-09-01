<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Controller;

use App\Dto\CreateAnswerDto;
use App\Dto\ListFiltersDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Resolver\ListFiltersDtoResolver;
use App\Security\Voter\AnswerVoter;
use App\Service\AnswerServiceInterface;
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

#[Route('/api/answers')]
/**
 * Controller responsible for managing Answer entities.
 *
 * Provides endpoints for listing, creating, updating, deleting, and marking answers as best.
 */
class AnswerController extends AbstractController
{
    /**
     * AnswerController constructor.
     *
     * @param AnswerServiceInterface $answerService Service for managing answers
     * @param ValidatorInterface     $validator     Validator for DTOs
     * @param SerializerInterface    $serializer    Serializer for DTOs
     */
    public function __construct(private readonly AnswerServiceInterface $answerService, private readonly ValidatorInterface $validator, private readonly SerializerInterface $serializer)
    {
    }

    /**
     * List answers for a specific question with pagination, filtering, and sorting.
     *
     * @param Question       $question The Question entity for which answers are retrieved
     * @param ListFiltersDto $filters  DTO containing optional filtering, sorting, and pagination parameters
     * @param int            $page     Page number (default: 1)
     *
     * @return JsonResponse Returns paginated answers along with metadata
     */
    #[Route('/question/{question}', methods: ['GET'])]
    public function list(
        Question $question,
        #[MapQueryString(resolver: ListFiltersDtoResolver::class)] ListFiltersDto $filters,
        #[MapQueryParameter] int $page = 1,
    ): JsonResponse {
        $limit = max(1, min(100, $filters->limit ?? 10));
        $search = $filters->search ?? null;
        $sort = $filters->sort ?? null;

        $result = $this->answerService->getPaginatedList($page, $limit, $question, $search, $sort);

        return $this->json([
            'items' => $result['items'],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'totalItems' => $result['totalItems'],
                'totalPages' => max(1, (int) ceil($result['totalItems'] / $limit)),
                'sort' => $sort,
                'search' => $search,
                'questionId' => $question->getId(),
            ],
        ], Response::HTTP_OK, [], ['groups' => 'answer:read']);
    }

    /**
     * Retrieve a single answer by its ID.
     *
     * @param Answer $answer The Answer entity to retrieve
     *
     * @return JsonResponse Returns the requested answer
     */
    #[Route('/{id}', methods: ['GET'])]
    public function show(Answer $answer): JsonResponse
    {
        return $this->json($answer, Response::HTTP_OK, [], ['groups' => 'answer:read']);
    }

    /**
     * Create a new answer.
     *
     * Handles both authenticated and anonymous users.
     *
     * @param Request $request HTTP request containing the answer payload
     *
     * @return JsonResponse Returns the created answer or validation errors
     *
     * @throws ExceptionInterface If deserialization fails
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), CreateAnswerDto::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if ($user) {
            $dto->author = $user;
            $dto->authorNickname = $user->getNickname();
            $dto->authorEmail = $user->getEmail();
        } elseif (empty($dto->authorNickname) || empty($dto->authorEmail)) {
            return $this->json(['error' => 'Nickname and email are required for anonymous answers'], Response::HTTP_BAD_REQUEST);
        }

        $answer = $this->answerService->create($dto);

        return $this->json($answer, Response::HTTP_CREATED, [], ['groups' => 'answer:read']);
    }

    /**
     * Update an existing answer.
     *
     * Access is restricted via AnswerVoter.
     *
     * @param Request $request HTTP request containing updated data
     * @param Answer  $answer  Answer entity to update
     *
     * @return JsonResponse Returns the updated answer or validation errors
     *
     * @throws ExceptionInterface
     */
    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, Answer $answer): JsonResponse
    {
        $this->denyAccessUnlessGranted(AnswerVoter::UPDATE, $answer);

        $dto = $this->serializer->deserialize($request->getContent(), UpdateAnswerDto::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $answer = $this->answerService->update($answer, $dto);

        return $this->json($answer, Response::HTTP_OK, [], ['groups' => 'answer:read']);
    }

    /**
     * Delete an existing answer.
     *
     * Access is restricted via AnswerVoter.
     *
     * @param Answer $answer Answer entity to delete
     *
     * @return Response Returns HTTP 204 No Content on success
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Answer $answer): Response
    {
        $this->denyAccessUnlessGranted(AnswerVoter::DELETE, $answer);

        $this->answerService->delete($answer);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Mark an answer as the best answer for its question.
     *
     * Access is restricted via AnswerVoter.
     *
     * @param Answer $answer Answer entity to mark as best
     *
     * @return JsonResponse Returns the updated answer
     */
    #[Route('/{id}/mark-best', methods: ['POST'])]
    public function markAsBest(Answer $answer): JsonResponse
    {
        $this->denyAccessUnlessGranted(AnswerVoter::MARK_BEST, $answer);

        $answer = $this->answerService->markAsBest($answer);

        return $this->json($answer, Response::HTTP_OK, [], ['groups' => 'answer:read']);
    }
}
