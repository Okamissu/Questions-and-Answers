<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for managing access to User entities.
 */
class UserVoter extends Voter
{
    public const VIEW = 'view';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    /**
     * Determines if this voter should handle the given attribute and subject.
     *
     * @param string $attribute The action being checked (view, update, delete)
     * @param mixed  $subject   The subject of the permission check (must be a User)
     *
     * @return bool True if this voter supports the attribute and subject, false otherwise
     */
    public function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::UPDATE, self::DELETE], true)
            && $subject instanceof User;
    }

    /**
     * Performs the access check for the given attribute, subject, and user.
     *
     * @param string         $attribute The action being checked
     * @param User           $subject   The User entity being accessed
     * @param TokenInterface $token     The security token containing the current user
     *
     * @return bool True if access is granted, false otherwise
     */
    public function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof User) {
            return false; // not logged in
        }

        // Admin can manage all users
        if (in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            return true;
        }

        return match ($attribute) {
            self::VIEW, self::UPDATE, self::DELETE => $currentUser->getId() === $subject->getId(),
            default => false,
        };
    }
}
