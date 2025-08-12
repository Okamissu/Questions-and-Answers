<?php

namespace App\Controller;

use App\Dto\CreateAnswerDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Service\AnswerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/answers')]
class AnswerController extends AbstractController
{
    public function __construct(
        private AnswerService $answerService,
        private QuestionRepository $questionRepository,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    // GET /api/answers?questionId=123&page=1&limit=10&search=foo&sort=createdAt_DESC
    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $questionId = $request->query->get('questionId');
        if (!$questionId) {
            return $this->json(['error' => 'Missing questionId parameter'], Response::HTTP_BAD_REQUEST);
        }

        /** @var Question|null $question */
        $question = $this->questionRepository->find($questionId);
        if (!$question) {
            return $this->json(['error' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 10))); // limit between 1 and 100
        $search = $request->query->get('search');
        $sort = $request->query->get('sort');

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
                'questionId' => $questionId,
            ],
        ], Response::HTTP_OK, [], ['groups' => 'answer:read']);
    }

    // GET /api/answers/{id}
    #[Route('/{id}', methods: ['GET'])]
    public function show(Answer $answer): JsonResponse
    {
        return $this->json($answer, Response::HTTP_OK, [], ['groups' => 'answer:read']);
    }

    // POST /api/answers
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), CreateAnswerDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($user) {
            $dto->author = $user;
            $dto->authorNickname = $user->getNickname();
            $dto->authorEmail = $user->getEmail();
        }

        $answer = $this->answerService->create($dto);

        return $this->json($answer, Response::HTTP_CREATED, [], ['groups' => 'answer:read']);
    }

    // PUT /api/answers/{id}
    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, Answer $answer): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), UpdateAnswerDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $answer = $this->answerService->update($answer, $dto);

        return $this->json($answer, Response::HTTP_OK, [], ['groups' => 'answer:read']);
    }

    // DELETE /api/answers/{id}
    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Answer $answer): Response
    {
        $this->answerService->delete($answer);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    // POST /api/answers/{id}/mark-best
    #[Route('/{id}/mark-best', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markAsBest(Answer $answer): JsonResponse
    {
        $answer = $this->answerService->markAsBest($answer);

        return $this->json($answer, Response::HTTP_OK, [], ['groups' => 'answer:read']);
    }
}
