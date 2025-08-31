<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Security\Voter;

use App\Entity\Answer;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for Answer entity permissions.
 */
class AnswerVoter extends Voter
{
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const MARK_BEST = 'mark_best';

    /**
     * Checks if the voter supports the given attribute and subject.
     *
     * @param string $attribute The action to check (update, delete, mark_best)
     * @param mixed  $subject   The subject being voted on
     *
     * @return bool True if supported, false otherwise
     */
    public function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::UPDATE, self::DELETE, self::MARK_BEST], true)
            && $subject instanceof Answer;
    }

    /**
     * Performs the access check on the given attribute, subject, and user.
     *
     * @param string         $attribute The action being checked
     * @param Answer         $subject   The Answer entity
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

        // Admin can perform any action
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        return match ($attribute) {
            self::UPDATE, self::DELETE => $this->isAuthor($subject, $user),
            self::MARK_BEST => $subject->getQuestion()->getAuthor()->getId() === $user->getId(),
            default => false,
        };
    }

    /**
     * Checks if the given user is the author of the answer.
     *
     * @param Answer $answer The answer entity
     * @param User   $user   The current user
     *
     * @return bool True if the user is the author, false otherwise
     */
    private function isAuthor(Answer $answer, User $user): bool
    {
        return $answer->getAuthor() && $answer->getAuthor()->getId() === $user->getId();
    }
}
