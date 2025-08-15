<?php

namespace App\Controller;

use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Entity\User;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(
        private UserServiceInterface $userService,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

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

            return new JsonResponse(['errors' => $errorsArray], 400);
        }

        try {
            $user = $this->userService->createUser($dto);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        $data = $this->serializer->serialize($user, 'json', ['groups' => ['user:read']]);

        return new JsonResponse($data, 201, [], true);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function showUser(User $user): JsonResponse
    {
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        if ($currentUser->getId() !== $user->getId() && !in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $data = $this->serializer->serialize($user, 'json', ['groups' => ['user:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Request $request, User $user): JsonResponse
    {
        $currentUser = $this->getUser();
        if ($currentUser !== $user && !in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $dto = $this->serializer->deserialize($request->getContent(), UpdateUserDto::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[] = ['field' => $error->getPropertyPath(), 'message' => $error->getMessage()];
            }

            return new JsonResponse(['errors' => $errorsArray], 400);
        }

        try {
            $updatedUser = $this->userService->updateUser($user, $dto);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        $data = $this->serializer->serialize($updatedUser, 'json', ['groups' => ['user:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(User $user): Response
    {
        $currentUser = $this->getUser();
        if ($currentUser !== $user && !in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $this->userService->deleteUser($user);

        return new Response(null, 204);
    }
}
