<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Resolver;

use App\Dto\ListFiltersDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Resolves ListFiltersDto from the request query parameters for controller arguments.
 */
class ListFiltersDtoResolver implements ValueResolverInterface
{
    /**
     * Resolves a ListFiltersDto from the current request.
     *
     * @param Request          $request  The current HTTP request
     * @param ArgumentMetadata $argument Metadata for the controller argument
     *
     * @return \Traversable<ListFiltersDto> Yields the DTO populated from query parameters
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $dto = new ListFiltersDto();
        $dto->search = $request->query->get('search');
        $dto->sort = $request->query->get('sort');
        $dto->limit = (int) $request->query->get('limit', 10);

        yield $dto;
    }
}
