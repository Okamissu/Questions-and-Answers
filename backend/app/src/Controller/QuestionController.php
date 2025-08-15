<?php
namespace App\Controller;

use App\Dto\CreateQuestionDto;
use App\Dto\UpdateQuestionDto;
use App\Entity\Question;
use App\Entity\User;
use App\Service\QuestionServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', 10);
        $search = $request->query->get('search');
        $sort = $request->query->get('sort');
        $categoryId = $request->query->get('category');

        $result = $this->questionService->getPaginatedList($page, $limit, $search, $sort, $categoryId);

        return $this->json([
            'items' => $result['items'],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'totalItems' => $result['totalItems'],
                'totalPages' => max(1, (int) ceil($result['totalItems'] / $limit)),
                'sort' => $sort,
                'search' => $search,
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
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), CreateQuestionDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], 400);
        }

        /** @var User $user */
        $user = $this->getUser();

        $question = $this->questionService->create($dto, $user);

        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);

        return new JsonResponse($data, 201, [], true);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, Question $question): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), UpdateQuestionDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], 400);
        }

        $question = $this->questionService->update($question, $dto);

        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Question $question): Response
    {
        $this->questionService->delete($question);

        return new Response(null, 204);
    }
}
