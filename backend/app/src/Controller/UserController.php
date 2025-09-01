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

#[Route('/api/users')]
/**
 * Controller responsible for managing User entities.
 *
 * Provides endpoints for creating, retrieving, updating, and deleting users.
 */
class UserController extends AbstractController
{
    /**
     * UserController constructor.
     *
     * @param UserServiceInterface $userService Service for managing users
     * @param ValidatorInterface   $validator   Validator for DTOs
     * @param SerializerInterface  $serializer  Serializer for DTOs
     */
    public function __construct(private readonly UserServiceInterface $userService, private readonly ValidatorInterface $validator, private readonly SerializerInterface $serializer)
    {
    }

    /**
     * Create a new user.
     *
     * @param Request $request HTTP request containing the user payload
     *
     * @return JsonResponse Returns the created user or validation errors
     *
     * @throws ExceptionInterface
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), CreateUserDto::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[] = ['field' => $error->getPropertyPath(), 'message' => $error->getMessage()];
            }

            return $this->json(['errors' => $errorsArray], Response::HTTP_BAD_REQUEST);
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
     * Retrieve a single user by its ID.
     *
     * @param User $user The User entity to retrieve
     *
     * @return JsonResponse Returns the requested user
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
     * @param Request $request HTTP request containing updated data
     * @param User    $user    User entity to update
     *
     * @return JsonResponse Returns the updated user or validation errors
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
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[] = ['field' => $error->getPropertyPath(), 'message' => $error->getMessage()];
            }

            return $this->json(['errors' => $errorsArray], Response::HTTP_BAD_REQUEST);
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
     * Delete an existing user.
     *
     * @param User $user User entity to delete
     *
     * @return Response Returns HTTP 204 No Content on success
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(User $user): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::DELETE, $user);
        $this->userService->deleteUser($user);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
