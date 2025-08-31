<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const VIEW = 'view';
    const UPDATE = 'update';
    const DELETE = 'delete';

    public function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::UPDATE, self::DELETE])
            && $subject instanceof User;
    }

    public function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof User) {
            return false; // nie zalogowany
        }

        // Admin może wszystko
        if (in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
            case self::UPDATE:
            case self::DELETE:
                // Użytkownik może operować tylko na sobie
                return $currentUser->getId() === $subject->getId();
        }

        return false;
    }
}
