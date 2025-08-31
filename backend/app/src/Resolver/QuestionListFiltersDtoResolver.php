<?php

namespace App\Resolver;

use App\Dto\QuestionListFiltersDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class QuestionListFiltersDtoResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $dto = new QuestionListFiltersDto();
        $dto->search = $request->query->get('search');
        $dto->sort = $request->query->get('sort');
        $dto->limit = (int) $request->query->get('limit', 10);
        $dto->categoryId = $request->query->get('category');

        yield $dto;
    }
}
