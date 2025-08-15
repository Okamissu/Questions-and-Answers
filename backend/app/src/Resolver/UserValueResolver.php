<?php

namespace App\Resolver;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserValueResolver implements ValueResolverInterface
{
    public function __construct(private UserRepository $userRepository) {}

    /**
     * @return \Traversable<User>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== User::class) {
            return [];
        }

        $userId = $request->attributes->get('userId') ?? $request->get('userId');

        if (!$userId) {
            return [];
        }

        $user = $this->userRepository->find((int)$userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        yield $user;
    }
}
