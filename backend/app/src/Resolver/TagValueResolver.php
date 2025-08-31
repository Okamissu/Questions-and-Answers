<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Resolver;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resolves Tag entities from request parameters for controller arguments.
 */
class TagValueResolver implements ValueResolverInterface
{
    /**
     * TagValueResolver constructor.
     *
     * @param TagRepository $tagRepository Repository used to fetch Tag entities
     */
    public function __construct(private readonly TagRepository $tagRepository)
    {
    }

    /**
     * Resolves a Tag entity from the request.
     *
     * @param Request          $request  The current HTTP request
     * @param ArgumentMetadata $argument Metadata for the controller argument
     *
     * @return \Traversable<Tag> Yields the resolved Tag entity
     *
     * @throws NotFoundHttpException If the tag with the given ID does not exist
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Tag::class !== $argument->getType()) {
            return [];
        }

        $tagId = $request->attributes->get('tagId') ?? $request->get('tagId');

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
