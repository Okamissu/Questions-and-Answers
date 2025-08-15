<?php

namespace App\Resolver;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Traversable;

class CategoryValueResolver implements ValueResolverInterface
{
    public function __construct(private CategoryRepository $categoryRepository)
    {
    }

    /**
     * @return Traversable<Category>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== Category::class) {
            return [];
        }

        $categoryId = $request->attributes->get('categoryId') ?? $request->get('categoryId');

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
