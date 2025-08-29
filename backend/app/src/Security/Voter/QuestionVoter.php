<?php

namespace App\Security\Voter;

use App\Entity\Question;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class QuestionVoter extends Voter
{
    const UPDATE = 'update';
    const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::UPDATE, self::DELETE])
            && $subject instanceof Question;
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

        // Autor pytania może update/delete
        return $subject->getAuthor() && $subject->getAuthor()->getId() === $user->getId();
    }
}
