<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Resolver;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resolves User entities from request parameters for controller arguments.
 */
class UserValueResolver implements ValueResolverInterface
{
    /**
     * UserValueResolver constructor.
     *
     * @param UserRepository $userRepository Repository used to fetch User entities
     */
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * Resolves a User entity from the request.
     *
     * @param Request          $request  The current HTTP request
     * @param ArgumentMetadata $argument Metadata for the controller argument
     *
     * @return \Traversable<User> Yields the resolved User entity
     *
     * @throws NotFoundHttpException If the user with the given ID does not exist
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (User::class !== $argument->getType()) {
            return [];
        }

        $userId = $request->attributes->get('userId') ?? $request->get('userId');

        if (!$userId) {
            return [];
        }

        $user = $this->userRepository->find((int) $userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        yield $user;
    }
}
