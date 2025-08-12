<?php

namespace App\Controller;

use App\Dto\CreateCategoryDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categories')]
class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryService $categoryService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
    }

    // GET /api/categories
    // Queries: page, sort, search
    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', 10);
        $search = $request->query->get('search');
        $sort = $request->query->get('sort');

        $result = $this->categoryService->getPaginatedList($page, $limit, $search, $sort);

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
        ], Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Category $category): JsonResponse
    {
        $data = $this->serializer->serialize($category, 'json', ['groups' => ['category:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), CreateCategoryDto::class, 'json');
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], 400);
        }

        $category = $this->categoryService->create($dto);
        $json = $this->serializer->serialize($category, 'json', ['groups' => ['category:read']]);

        return new JsonResponse($json, 201, [], true);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, Category $category): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), UpdateCategoryDto::class, 'json');
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], 400);
        }

        $updatedCategory = $this->categoryService->update($category, $dto);
        $json = $this->serializer->serialize($updatedCategory, 'json', ['groups' => ['category:read']]);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Category $category): Response
    {
        $this->categoryService->delete($category);

        return new Response(null, 204);
    }
}
