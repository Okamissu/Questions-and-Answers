<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Security\Voter;

use App\Entity\Question;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for Question entity permissions.
 */
class QuestionVoter extends Voter
{
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    /**
     * Checks if the voter supports the given attribute and subject.
     *
     * @param string $attribute The action to check (update, delete)
     * @param mixed  $subject   The subject being voted on
     *
     * @return bool True if supported, false otherwise
     */
    public function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::UPDATE, self::DELETE], true)
            && $subject instanceof Question;
    }

    /**
     * Performs the access check on the given attribute, subject, and user.
     *
     * @param string         $attribute The action being checked
     * @param Question       $subject   The Question entity
     * @param TokenInterface $token     The security token containing the user
     *
     * @return bool True if the action is granted, false otherwise
     */
    public function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false; // user not logged in
        }

        // Admin can manage all questions
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        // Author of the question can update/delete
        return $subject->getAuthor() && $subject->getAuthor()->getId() === $user->getId();
    }
}
