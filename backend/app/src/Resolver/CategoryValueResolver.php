<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Resolver;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resolves Category entities from request parameters for controller arguments.
 */
class CategoryValueResolver implements ValueResolverInterface
{
    /**
     * CategoryValueResolver constructor.
     *
     * @param CategoryRepository $categoryRepository Repository used to fetch Category entities
     */
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

    /**
     * Resolves a Category entity from the request.
     *
     * @param Request          $request  The current HTTP request
     * @param ArgumentMetadata $argument Metadata for the controller argument
     *
     * @return \Traversable<Category> Yields the resolved Category entity
     *
     * @throws NotFoundHttpException If the category with the given ID does not exist
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Category::class !== $argument->getType()) {
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
