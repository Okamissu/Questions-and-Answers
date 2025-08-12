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
    public function __construct(
        private UserRepository $userRepository
    )
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== User::class) {
            return [];
        }

        // Spróbuj pobrać userId z atrybutów routingu lub requestu
        $userId = $request->attributes->get('userId')
            ?? $request->get('userId')
            ?? null;

        if (!$userId) {
            // Brak ID — zwracamy pusty iterator (np. argument może być nullable)
            return [];
        }

        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        yield $user;
    }
}
