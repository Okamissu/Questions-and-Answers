<?php

namespace App\Resolver;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(private CategoryRepository $categoryRepository)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return Category::class === $argument->getType() &&
            ($request->attributes->has('categoryId') || $request->get('categoryId') !== null);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $categoryId = $request->attributes->get('categoryId')
            ?? $request->get('categoryId');

        if (!$categoryId) {
            return [];
        }

        $category = $this->categoryRepository->find($categoryId);

        if (!$category) {
            throw new NotFoundHttpException('Category not found');
        }

        yield $category;
    }
}
