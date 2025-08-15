<?php

namespace App\Resolver;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Traversable;

class TagValueResolver implements ValueResolverInterface
{
    public function __construct(
        private TagRepository $tagRepository
    ) {}

    /**
     * @return Traversable<Tag>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== Tag::class) {
            return [];
        }

        $tagId = $request->attributes->get('tagId')
            ?? $request->get('tagId')
            ?? null;

        if (!$tagId) {
            return [];
        }

        $tag = $this->tagRepository->find($tagId);

        if (!$tag) {
            throw new NotFoundHttpException('Tag not found');
        }

        yield $tag;
    }
}
