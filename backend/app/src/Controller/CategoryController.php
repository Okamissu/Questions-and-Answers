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

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $sort = $request->query->get('sort', 'name_asc');
        $search = $request->query->get('search', '');

        [$sortField, $sortDirection] = explode('_', $sort) + [null, null];

        // QueryBuilder z repozytorium
        $qb = $this->categoryService->queryAll($search, $sortField, $sortDirection, $limit, $offset);

        // Total count bez paginacji
        $countQb = clone $qb;
        $totalItems = (int) $countQb->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();

        $categories = $qb->getQuery()->getResult();

        $json = json_decode(
            $this->serializer->serialize($categories, 'json', ['groups' => ['category:read']]),
            true
        );

        return $this->json([
            'items' => $json,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'totalItems' => $totalItems,
                'totalPages' => max(1, (int) ceil($totalItems / $limit)),
                'sort' => $sort,
                'search' => $search,
            ],
        ]);
    }


    #[Route('', methods: ['POST'])]
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
