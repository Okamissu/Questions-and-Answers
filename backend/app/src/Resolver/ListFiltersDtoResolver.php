<?php

namespace App\Resolver;

use App\Dto\ListFiltersDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ListFiltersDtoResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $dto = new ListFiltersDto();
        $dto->search = $request->query->get('search');
        $dto->sort = $request->query->get('sort');
        $dto->limit = (int) $request->query->get('limit', 10);

        yield $dto;
    }
}
