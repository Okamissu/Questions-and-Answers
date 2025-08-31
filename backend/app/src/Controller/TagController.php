<?php

namespace App\Controller;

use App\Dto\CreateTagDto;
use App\Dto\ListFiltersDto;
use App\Dto\UpdateTagDto;
use App\Entity\Tag;
use App\Resolver\ListFiltersDtoResolver;
use App\Service\TagServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tags')]
class TagController extends AbstractController
{
    public function __construct(
        private TagServiceInterface $tagService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(
        #[MapQueryString(resolver: ListFiltersDtoResolver::class)] ListFiltersDto $filters,
        #[MapQueryParameter] int $page = 1,
    ): JsonResponse {
        $limit = max(1, min(100, $filters->limit ?? 10));
        $search = $filters->search ?? null;
        $sort = $filters->sort ?? null;

        $result = $this->tagService->getPaginatedList($page, $limit, $search, $sort);

        return $this->json([
            'items' => $result['items'],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'totalItems' => $result['totalItems'],
                'totalPages' => max(1, (int) ceil($result['totalItems'] / $limit)),
                'sort' => $sort,
                'search' => $search,
            ],
        ], Response::HTTP_OK, [], ['groups' => 'tag:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Tag $tag): JsonResponse
    {
        $json = $this->serializer->serialize($tag, 'json', ['groups' => ['tag:read']]);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $dto = $this->serializer->deserialize($request->getContent(), CreateTagDto::class, 'json');
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], 400);
        }

        $tag = $this->tagService->create($dto);
        $json = $this->serializer->serialize($tag, 'json', ['groups' => ['tag:read']]);

        return new JsonResponse($json, 201, [], true);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $dto = $this->serializer->deserialize($request->getContent(), UpdateTagDto::class, 'json');
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], 400);
        }

        $updatedTag = $this->tagService->update($tag, $dto);
        $json = $this->serializer->serialize($updatedTag, 'json', ['groups' => ['tag:read']]);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Tag $tag): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $this->tagService->delete($tag);

        return new Response(null, 204);
    }
}
