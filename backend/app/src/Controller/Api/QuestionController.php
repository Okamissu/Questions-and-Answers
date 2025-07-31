<?php
// src/Controller/Api/QuestionController.php

namespace App\Controller\Api;

use App\Entity\Question;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\QuestionRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/question')]
class QuestionController extends AbstractController
{
    public function __construct(
        private QuestionRepository $questionRepository,
        private EntityManagerInterface $em
    ) {}

    #[Route('', name: 'api_question_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $questions = $this->questionRepository->queryAll()->getQuery()->getResult();

        $data = [];
        foreach ($questions as $question) {
            $data[] = [
                'id' => $question->getId(),
                'title' => $question->getTitle(),
                'content' => $question->getContent(),
                'author' => $question->getAuthor()?->getUsername(), // TODO: obsłuż null lub pokaż więcej danych
                'category' => $question->getCategory()?->getName(),
                'tags' => array_map(fn($tag) => $tag->getName(), $question->getTags()->toArray()),
                'createdAt' => $question->getCreatedAt()?->format('Y-m-d H:i:s'), // TODO: użyj normalizatora później
            ];
        }

        return new JsonResponse($data); // TODO: zamień na $this->json($data) po konfiguracji serializera
    }

    #[Route('', name: 'api_question_create', methods: ['POST'])]
    public function create(
        Request $request,
        UserRepository $userRepo,
        CategoryRepository $categoryRepo,
        TagRepository $tagRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $question = new Question();
        $question->setTitle($data['title'] ?? '');
        $question->setContent($data['content'] ?? '');

        // TODO: zastąp tymczasowe pobieranie użytkownika metodą $this->getUser()
        $author = $userRepo->find(1); // <- tymczasowo zakładamy, że user #1 istnieje
        if (!$author) {
            return new JsonResponse(['error' => 'User not found'], 400); // TODO: lepsza obsługa błędów
        }
        $question->setAuthor($author);

        $category = $categoryRepo->find($data['category_id'] ?? 0);
        if (!$category) {
            return new JsonResponse(['error' => 'Category not found'], 400); // TODO: walidacja + obsługa błędów globalnie
        }
        $question->setCategory($category);

        if (!empty($data['tag_ids']) && is_array($data['tag_ids'])) {
            foreach ($data['tag_ids'] as $tagId) {
                $tag = $tagRepo->find($tagId);
                if ($tag) {
                    $question->addTag($tag);
                }
                // TODO: walidacja: co jeśli tag nie istnieje?
            }
        }

        $this->em->persist($question);
        $this->em->flush();

        return new JsonResponse(['id' => $question->getId()], 201); // TODO: dodaj więcej danych (np. createdAt) po zapisaniu
    }
}
