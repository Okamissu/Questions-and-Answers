<?php

namespace App\Security\Voter;

use App\Entity\Tag;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TagVoter extends Voter
{
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    public function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::UPDATE, self::DELETE])
            && ($subject instanceof Tag || null === $subject);
    }

    public function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false; // nie zalogowany
        }

        // Admin może wszystko
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        // Reszta użytkowników nie ma dostępu
        return false;
    }
}
