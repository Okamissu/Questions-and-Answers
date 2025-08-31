<?php

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
                $errorsArray[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
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
    public function show(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);

        $data = $this->serializer->serialize($user, 'json', ['groups' => ['user:read']]);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Request $request, User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::UPDATE, $user);

        $dto = $this->serializer->deserialize($request->getContent(), UpdateUserDto::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
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
    public function delete(User $user): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::DELETE, $user);

        $this->userService->deleteUser($user);

        return new Response(null, 204);
    }
}
