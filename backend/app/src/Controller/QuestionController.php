<?php

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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/questions')]
class QuestionController extends AbstractController
{
    public function __construct(
        private QuestionServiceInterface $questionService,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(
        #[MapQueryString(resolver: QuestionListFiltersDtoResolver::class)] QuestionListFiltersDto $filters,
        #[MapQueryParameter] int $page = 1,
    ): JsonResponse {
        $limit = max(1, min(100, $filters->limit ?? 10));

        $result = $this->questionService->getPaginatedList(
            $page,
            $limit,
            $filters->search,
            $filters->sort,
            $filters->categoryId
        );

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

    #[Route('/{id}', methods: ['GET'])]
    public function show(Question $question): JsonResponse
    {
        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $dto = $this->serializer->deserialize($request->getContent(), CreateQuestionDto::class, 'json');
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], 400);
        }

        $question = $this->questionService->create($dto, $user);
        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);

        return new JsonResponse($data, 201, [], true);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, Question $question): JsonResponse
    {
        $this->denyAccessUnlessGranted(QuestionVoter::UPDATE, $question);

        $dto = $this->serializer->deserialize($request->getContent(), UpdateQuestionDto::class, 'json');
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], 400);
        }

        $question = $this->questionService->update($question, $dto);
        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Question $question): Response
    {
        $this->denyAccessUnlessGranted(QuestionVoter::DELETE, $question);

        $this->questionService->delete($question);

        return new Response(null, 204);
    }
}
