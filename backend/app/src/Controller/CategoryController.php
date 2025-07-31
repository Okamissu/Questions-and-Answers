<?php

namespace App\Controller;

use App\Dto\CreateCategoryDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    public function list(): JsonResponse
    {
        $categories = $this->categoryService->getAllCategories();
        $json = $this->serializer->serialize($categories, 'json', ['groups' => ['category:read']]);

        return new JsonResponse($json, 200, [], true);
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
    public function delete(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);

        return new JsonResponse(null, 204);
    }
}
