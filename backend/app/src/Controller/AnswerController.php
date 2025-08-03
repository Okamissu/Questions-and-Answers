<?php

namespace App\Controller;

use App\Dto\CreateAnswerDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Repository\AnswerRepository;
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
        private AnswerRepository $answerRepository,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    // GET /api/answers?questionId=123
    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $questionId = $request->query->get('questionId');
        if (!$questionId) {
            return new JsonResponse(['error' => 'Missing questionId parameter'], 400);
        }

        $answers = $this->answerRepository->createQueryBuilder('a')
            ->andWhere('a.question = :questionId')
            ->setParameter('questionId', $questionId)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $data = $this->serializer->serialize($answers, 'json', ['groups' => ['answer:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    // GET /api/answers/{id}
    #[Route('/{id}', methods: ['GET'])]
    public function show(Answer $answer): JsonResponse
    {
        $data = $this->serializer->serialize($answer, 'json', ['groups' => ['answer:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    // POST /api/answers
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), CreateAnswerDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], 400);
        }

        $answer = $this->answerService->create($dto);

        $data = $this->serializer->serialize($answer, 'json', ['groups' => ['answer:read']]);

        return new JsonResponse($data, 201, [], true);
    }

    // PUT /api/answers/{id}
    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, Answer $answer): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), UpdateAnswerDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], 400);
        }

        $answer = $this->answerService->update($answer, $dto);

        $data = $this->serializer->serialize($answer, 'json', ['groups' => ['answer:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    // DELETE /api/answers/{id}
    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Answer $answer): Response
    {
        $this->answerService->delete($answer);

        return new Response(null, 204);
    }

    // POST /api/answers/{id}/mark-best
    #[Route('/{id}/mark-best', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markAsBest(Answer $answer): JsonResponse
    {
        $answer = $this->answerService->markAsBest($answer);

        $data = $this->serializer->serialize($answer, 'json', ['groups' => ['answer:read']]);

        return new JsonResponse($data, 200, [], true);
    }
}
