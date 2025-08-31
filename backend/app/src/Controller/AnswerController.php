<?php

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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/answers')]
class AnswerController extends AbstractController
{
    public function __construct(
        private AnswerServiceInterface $answerService,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(
        Question $question,
        #[MapQueryString(resolver: ListFiltersDtoResolver::class)] ListFiltersDto $filters,
        #[MapQueryParameter] int $page = 1,
    ): JsonResponse {
        $limit = max(1, min(100, $filters->limit ?? 10)); // domyślny limit 10
        $search = $filters->search ?? null;
        $sort = $filters->sort ?? null;

        $result = $this->answerService->getPaginatedList(
            $page,
            $limit,
            $question,
            $search,
            $sort
        );

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

    #[Route('/{id}', methods: ['GET'])]
    public function show(Answer $answer): JsonResponse
    {
        return $this->json($answer, Response::HTTP_OK, [], ['groups' => 'answer:read']);
    }

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
            return $this->json(['error' => 'Nickname and email are required for anonymous answers'], 400);
        }

        $answer = $this->answerService->create($dto);

        return $this->json($answer, Response::HTTP_CREATED, [], ['groups' => 'answer:read']);
    }

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

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Answer $answer): Response
    {
        $this->denyAccessUnlessGranted(AnswerVoter::DELETE, $answer);

        $this->answerService->delete($answer);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/mark-best', methods: ['POST'])]
    public function markAsBest(Answer $answer): JsonResponse
    {
        $this->denyAccessUnlessGranted(AnswerVoter::MARK_BEST, $answer);

        $answer = $this->answerService->markAsBest($answer);

        return $this->json($answer, Response::HTTP_OK, [], ['groups' => 'answer:read']);
    }
}
