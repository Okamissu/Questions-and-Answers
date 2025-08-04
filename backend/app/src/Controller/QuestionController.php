<?php

namespace App\Controller;

use App\Dto\CreateQuestionDto;
use App\Dto\UpdateQuestionDto;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Service\QuestionService;
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
        private QuestionService $questionService,
        private QuestionRepository $questionRepository,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    // GET /api/questions
    // Queries: page, sort, search
    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $sort = $request->query->get('sort', 'createdAt_desc');
        $search = $request->query->get('search', '');

        $qb = $this->questionRepository->createQueryBuilder('q');

        // Filtracja po tytule
        if ('' !== $search) {
            $qb->andWhere('q.title LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // Sortowanie
        [$sortField, $sortDirection] = explode('_', $sort) + [null, null];

        $allowedFields = ['createdAt', 'title'];
        $allowedDirections = ['asc', 'desc'];

        if (!in_array($sortField, $allowedFields, true)) {
            $sortField = 'createdAt';
        }
        if (!in_array(strtolower($sortDirection), $allowedDirections, true)) {
            $sortDirection = 'desc';
        }

        $qb->orderBy('q.'.$sortField, $sortDirection);

        // Liczenie totalItems
        $countQb = clone $qb;
        $totalItems = (int) $countQb->select('COUNT(q.id)')->getQuery()->getSingleScalarResult();

        // Paginacja
        $qb->setFirstResult($offset)->setMaxResults($limit);
        $questions = $qb->getQuery()->getResult();

        $jsonQuestions = json_decode(
            $this->serializer->serialize($questions, 'json', ['groups' => ['question:read']]),
            true
        );

        $responseData = [
            'items' => $jsonQuestions,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'totalItems' => $totalItems,
                'totalPages' => max(1, (int) ceil($totalItems / $limit)),
                'sort' => $sort,
                'search' => $search,
            ],
        ];

        return $this->json($responseData);
    }

    // GET /api/questions/{id}
    #[Route('/{id}', methods: ['GET'])]
    public function show(Question $question): JsonResponse
    {
        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    // GET /api/questions/{id}
    // Queries: page, sort, search
    #[Route('/by-category/{categoryId}', methods: ['GET'])]
    public function listByCategory(Request $request, Category $category): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $sort = $request->query->get('sort', 'createdAt_desc');
        $search = $request->query->get('search', '');

        $qb = $this->questionRepository->createQueryBuilder('q')
            ->andWhere('q.category = :category')
            ->setParameter('category', $category);

        if ('' !== $search) {
            $qb->andWhere('q.title LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        [$sortField, $sortDirection] = explode('_', $sort) + [null, null];

        $allowedFields = ['createdAt', 'title'];
        $allowedDirections = ['asc', 'desc'];

        if (!in_array($sortField, $allowedFields, true)) {
            $sortField = 'createdAt';
        }
        if (!in_array(strtolower($sortDirection), $allowedDirections, true)) {
            $sortDirection = 'desc';
        }

        $qb->orderBy('q.'.$sortField, $sortDirection);

        $countQb = clone $qb;
        $totalItems = (int) $countQb->select('COUNT(q.id)')->getQuery()->getSingleScalarResult();

        $qb->setFirstResult($offset)->setMaxResults($limit);
        $questions = $qb->getQuery()->getResult();

        $jsonQuestions = json_decode(
            $this->serializer->serialize($questions, 'json', ['groups' => ['question:read']]),
            true
        );

        $responseData = [
            'items' => $jsonQuestions,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'totalItems' => $totalItems,
                'totalPages' => max(1, (int) ceil($totalItems / $limit)),
                'sort' => $sort,
                'search' => $search,
            ],
        ];

        return $this->json($responseData);
    }

    // POST /api/questions
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), CreateQuestionDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse(['error' => $errorsString], 400);
        }

        /** @var User $user */
        $user = $this->getUser();

        $question = $this->questionService->create($dto, $user);

        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);

        return new JsonResponse($data, 201, [], true);
    }

    // PUT /api/questions/{id}
    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, Question $question): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), UpdateQuestionDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse(['error' => $errorsString], 400);
        }

        $question = $this->questionService->update($question, $dto);

        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    // DELETE /api/questions/{id}
    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Question $question): Response
    {
        $this->questionService->delete($question);

        return new Response(null, 204);
    }
}
