<?php

namespace App\Controller;

use App\Dto\CreateQuestionDto;
use App\Dto\UpdateQuestionDto;
use App\Entity\Question;
use App\Entity\User;
use App\Service\QuestionService;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/questions')]
class QuestionController extends AbstractController
{
    public function __construct(
        private QuestionService $questionService,
        private QuestionRepository $questionRepository,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {}

    // GET /api/questions
    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $qb = $this->questionRepository->queryAll();

        // prosta paginacja
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $qb->setFirstResult($offset)->setMaxResults($limit);
        $questions = $qb->getQuery()->getResult();

        $data = $this->serializer->serialize($questions, 'json', ['groups' => ['question:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    // GET /api/questions/{id}
    #[Route('/{id}', methods: ['GET'])]
    public function show(Question $question): JsonResponse
    {
        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
        return new JsonResponse($data, 200, [], true);
    }

    // POST /api/questions
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), CreateQuestionDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
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
            $errorsString = (string)$errors;
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
