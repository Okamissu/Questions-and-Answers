<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

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
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tags')]
/**
 * Controller responsible for managing Tag entities.
 *
 * Provides endpoints for listing, creating, updating, and deleting tags.
 */
class TagController extends AbstractController
{
    /**
     * TagController constructor.
     *
     * @param TagServiceInterface $tagService Service for managing tags
     * @param SerializerInterface $serializer Serializer for DTOs
     * @param ValidatorInterface  $validator  Validator for DTOs
     */
    public function __construct(private readonly TagServiceInterface $tagService, private readonly SerializerInterface $serializer, private readonly ValidatorInterface $validator)
    {
    }

    /**
     * List tags with pagination, filtering, and sorting.
     *
     * @param ListFiltersDto $filters DTO containing optional filtering, sorting, and pagination parameters
     * @param int            $page    Page number (default: 1)
     *
     * @return JsonResponse Returns paginated tags along with metadata
     */
    #[Route('', methods: ['GET'])]
    public function list(#[MapQueryString(resolver: ListFiltersDtoResolver::class)] ListFiltersDto $filters, #[MapQueryParameter] int $page = 1): JsonResponse
    {
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

    /**
     * Retrieve a single tag by its ID.
     *
     * @param Tag $tag The Tag entity to retrieve
     *
     * @return JsonResponse Returns the requested tag
     *
     * @throws ExceptionInterface
     */
    #[Route('/{id}', methods: ['GET'])]
    public function show(Tag $tag): JsonResponse
    {
        $json = $this->serializer->serialize($tag, 'json', ['groups' => ['tag:read']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * Create a new tag.
     *
     * @param Request $request HTTP request containing the tag payload
     *
     * @return JsonResponse Returns the created tag or validation errors
     *
     * @throws ExceptionInterface
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $dto = $this->serializer->deserialize($request->getContent(), CreateTagDto::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $tag = $this->tagService->create($dto);
        $json = $this->serializer->serialize($tag, 'json', ['groups' => ['tag:read']]);

        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    /**
     * Update an existing tag.
     *
     * @param Request $request HTTP request containing updated data
     * @param Tag     $tag     Tag entity to update
     *
     * @return JsonResponse Returns the updated tag or validation errors
     *
     * @throws ExceptionInterface
     */
    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $dto = $this->serializer->deserialize($request->getContent(), UpdateTagDto::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $updatedTag = $this->tagService->update($tag, $dto);
        $json = $this->serializer->serialize($updatedTag, 'json', ['groups' => ['tag:read']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * Delete an existing tag.
     *
     * @param Tag $tag Tag entity to delete
     *
     * @return Response Returns HTTP 204 No Content on success
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Tag $tag): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $this->tagService->delete($tag);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
