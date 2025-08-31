<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

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
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categories')]
/**
 * Controller responsible for managing Category entities.
 *
 * Provides endpoints for listing, creating, updating, and deleting categories.
 */
class CategoryController extends AbstractController
{
    /**
     * CategoryController constructor.
     *
     * @param CategoryServiceInterface $categoryService Service for managing categories
     * @param SerializerInterface      $serializer      Serializer for DTOs
     * @param ValidatorInterface       $validator       Validator for DTOs
     */
    public function __construct(private readonly CategoryServiceInterface $categoryService, private readonly SerializerInterface $serializer, private readonly ValidatorInterface $validator)
    {
    }

    /**
     * List categories with pagination, filtering, and sorting.
     *
     * @param ListFiltersDto $filters DTO containing optional filtering, sorting, and pagination parameters
     * @param int            $page    Page number (default: 1)
     *
     * @return JsonResponse Returns paginated categories along with metadata
     */
    #[Route('', methods: ['GET'])]
    public function list(#[MapQueryString(resolver: ListFiltersDtoResolver::class)] ListFiltersDto $filters, #[MapQueryParameter] int $page = 1): JsonResponse
    {
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

    /**
     * Retrieve a single category by its ID.
     *
     * @param Category $category The Category entity to retrieve
     *
     * @return JsonResponse Returns the requested category
     *
     * @throws ExceptionInterface
     */
    #[Route('/{id}', methods: ['GET'])]
    public function show(Category $category): JsonResponse
    {
        $json = $this->serializer->serialize($category, 'json', ['groups' => ['category:read']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * Create a new category.
     *
     * @param Request $request HTTP request containing the category payload
     *
     * @return JsonResponse Returns the created category or validation errors
     *
     * @throws ExceptionInterface
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(CategoryVoter::CREATE);

        $dto = $this->serializer->deserialize($request->getContent(), CreateCategoryDto::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $category = $this->categoryService->create($dto);
        $json = $this->serializer->serialize($category, 'json', ['groups' => ['category:read']]);

        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    /**
     * Update an existing category.
     *
     * @param Request  $request  HTTP request containing updated data
     * @param Category $category Category entity to update
     *
     * @return JsonResponse Returns the updated category or validation errors
     *
     * @throws ExceptionInterface
     */
    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, Category $category): JsonResponse
    {
        $this->denyAccessUnlessGranted(CategoryVoter::UPDATE, $category);

        $dto = $this->serializer->deserialize($request->getContent(), UpdateCategoryDto::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $updatedCategory = $this->categoryService->update($category, $dto);
        $json = $this->serializer->serialize($updatedCategory, 'json', ['groups' => ['category:read']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * Delete an existing category.
     *
     * @param Category $category Category entity to delete
     *
     * @return Response Returns HTTP 204 No Content on success
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Category $category): Response
    {
        $this->denyAccessUnlessGranted(CategoryVoter::DELETE, $category);

        $this->categoryService->delete($category);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
