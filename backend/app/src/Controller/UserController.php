<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Controller;

use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Entity\User;
use App\Security\Voter\UserVoter;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * UserController handles actions related to users, including listing, creating, updating, and deleting users.
 *
 * @Route("/api/users")
 */
#[Route('/api/users')]
class UserController extends AbstractController
{
    /**
     * UserController constructor.
     *
     * @param UserServiceInterface $userService service to handle user operations
     * @param ValidatorInterface   $validator   validator for input data
     * @param SerializerInterface  $serializer  serializer to convert data to/from JSON
     */
    public function __construct(private readonly UserServiceInterface $userService, private readonly ValidatorInterface $validator, private readonly SerializerInterface $serializer)
    {
    }

    /**
     * List users with optional pagination and search.
     *
     * @Route("", methods={"GET"})
     *
     * @param Request $request the HTTP request
     *
     * @return JsonResponse the list of users in JSON format
     *
     * @throws ExceptionInterface
     */
    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
        $search = $request->query->get('search');

        $usersData = $this->userService->getUsers($page, $limit, $search);
        $json = $this->serializer->serialize($usersData, 'json', ['groups' => ['user:read']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * Create a new user.
     *
     * @Route("", methods={"POST"})
     *
     * @param Request $request the HTTP request containing user data
     *
     * @return JsonResponse the created user data in JSON format
     *
     * @throws ExceptionInterface
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), CreateUserDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json([
                'errors' => array_map(fn ($e) => ['field' => $e->getPropertyPath(), 'message' => $e->getMessage()], iterator_to_array($errors)),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->createUser($dto);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $json = $this->serializer->serialize($user, 'json', ['groups' => ['user:read']]);

        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    /**
     * Get the currently authenticated user.
     *
     * @Route("/me", methods={"GET"})
     *
     * @return JsonResponse the current user data in JSON format
     *
     * @throws ExceptionInterface
     */
    #[Route('/me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $json = $this->serializer->serialize($user, 'json', ['groups' => ['user:read']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * Show a specific user by ID.
     *
     * @Route("/{id}", methods={"GET"})
     *
     * @param User $user the user entity
     *
     * @return JsonResponse the user data in JSON format
     *
     * @throws ExceptionInterface
     */
    #[Route('/{id}', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);
        $json = $this->serializer->serialize($user, 'json', ['groups' => ['user:read']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * Update an existing user.
     *
     * @Route("/{id}", methods={"PUT"})
     *
     * @param Request $request the HTTP request containing updated user data
     * @param User    $user    the user to update
     *
     * @return JsonResponse the updated user data in JSON format
     *
     * @throws ExceptionInterface
     */
    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::UPDATE, $user);

        $dto = $this->serializer->deserialize($request->getContent(), UpdateUserDto::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json([
                'errors' => array_map(fn ($e) => ['field' => $e->getPropertyPath(), 'message' => $e->getMessage()], iterator_to_array($errors)),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $updatedUser = $this->userService->updateUser($user, $dto);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $json = $this->serializer->serialize($updatedUser, 'json', ['groups' => ['user:read']]);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * Delete a user by ID.
     *
     * @Route("/{id}", methods={"DELETE"})
     *
     * @param User $user the user to delete
     *
     * @return Response the response after deleting the user
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(User $user): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::DELETE, $user);
        $this->userService->deleteUser($user);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
