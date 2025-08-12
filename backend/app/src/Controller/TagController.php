<?php

namespace App\Controller;

use App\Dto\CreateTagDto;
use App\Dto\UpdateTagDto;
use App\Entity\Tag;
use App\Service\TagService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tags')]
class TagController extends AbstractController
{
    public function __construct(
        private TagService $tagService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
    }

    // GET /api/tags
    // Queries: page, sort, search
    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', 10);
        $search = $request->query->get('search');
        $sort = $request->query->get('sort');

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

    // GET /api/tags/{id}
    #[Route('/{id}', methods: ['GET'])]
    public function show(Tag $tag): JsonResponse
    {
        $json = $this->serializer->serialize($tag, 'json', ['groups' => ['tag:read']]);

        return new JsonResponse($json, 200, [], true);
    }

    // POST /api/tags
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), CreateTagDto::class, 'json');
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], 400);
        }

        $tag = $this->tagService->create($dto);
        $json = $this->serializer->serialize($tag, 'json', ['groups' => ['tag:read']]);

        return new JsonResponse($json, 201, [], true);
    }

    // PUT /api/tags/{id}
    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), UpdateTagDto::class, 'json');
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], 400);
        }

        $updatedTag = $this->tagService->update($tag, $dto);
        $json = $this->serializer->serialize($updatedTag, 'json', ['groups' => ['tag:read']]);

        return new JsonResponse($json, 200, [], true);
    }

    // DELETE /api/tags/{id}
    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Tag $tag): Response
    {
        $this->tagService->delete($tag);

        return new Response(null, 204);
    }
}
