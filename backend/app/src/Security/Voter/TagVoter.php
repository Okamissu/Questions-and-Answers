<?php

namespace App\Security\Voter;

use App\Entity\Tag;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TagVoter extends Voter
{
    const CREATE = 'create';
    const UPDATE = 'update';
    const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::UPDATE, self::DELETE])
            && ($subject instanceof Tag || $subject === null);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
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
