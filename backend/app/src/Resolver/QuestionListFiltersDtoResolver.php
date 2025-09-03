<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Resolver;

use App\Dto\QuestionListFiltersDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Resolves QuestionListFiltersDto from request query parameters for controller arguments.
 */
class QuestionListFiltersDtoResolver implements ValueResolverInterface
{
    /**
     * Resolves a QuestionListFiltersDto from the current request.
     *
     * @param Request          $request  The current HTTP request
     * @param ArgumentMetadata $argument Metadata for the controller argument
     *
     * @return \Traversable<QuestionListFiltersDto> Yields the DTO populated from query parameters
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $dto = new QuestionListFiltersDto();
        $dto->search = $request->query->get('search');
        $dto->sort = $request->query->get('sort');
        $dto->limit = (int) $request->query->get('limit', 10);
        $dto->categoryId = $request->query->get('categoryId');
        $dto->tagId = $request->query->get('tagId');


        yield $dto;
    }
}
