<?php

namespace App\Controller;

use App\Dto\CreateTagDto;
use App\Dto\UpdateTagDto;
use App\Entity\Tag;
use App\Service\TagService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tags')]
class TagController extends AbstractController
{
    public function __construct(
        private TagService $tagService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $tags = $this->tagService->getAllTags(); 
        $json = $this->serializer->serialize($tags, 'json', ['groups' => ['tag:read']]);
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('', methods: ['POST'])]
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

    #[Route('/{id}', methods: ['PUT'])]
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

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Tag $tag): Response
    {
        $this->tagService->delete($tag);
        return new Response(null, 204);
    }
}
