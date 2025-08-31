<?php

namespace App\Controller;

use App\Dto\CreateCategoryDto;
use App\Dto\ListFiltersDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;
use App\Resolver\ListFiltersDtoResolver;
use App\Security\Voter\CategoryVoter;
use App\Service\CategoryServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categories')]
class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryServiceInterface $categoryService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(
        #[MapQueryString(resolver: ListFiltersDtoResolver::class)] ListFiltersDto $filters,
        #[MapQueryParameter] int $page = 1,
    ): JsonResponse {
        $limit = max(1, min(100, $filters->limit ?? 10));
        $search = $filters->search ?? null;
        $sort = $filters->sort ?? null;

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
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(CategoryVoter::CREATE);

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
        $this->denyAccessUnlessGranted(CategoryVoter::UPDATE, $category);

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
        $this->denyAccessUnlessGranted(CategoryVoter::DELETE, $category);

        $this->categoryService->delete($category);

        return new Response(null, 204);
    }
}
