<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Security\Voter;

use App\Entity\Category;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for Category entity permissions.
 */
class CategoryVoter extends Voter
{
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    /**
     * Checks if the voter supports the given attribute and subject.
     *
     * @param string $attribute The action to check (create, update, delete)
     * @param mixed  $subject   The subject being voted on
     *
     * @return bool True if supported, false otherwise
     */
    public function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::UPDATE, self::DELETE], true)
            && ($subject instanceof Category || self::CREATE === $attribute);
    }

    /**
     * Performs the access check on the given attribute, subject, and user.
     *
     * @param string         $attribute The action being checked
     * @param Category|null  $subject   The Category entity (null for create)
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

        // Admin can manage categories
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        return false;
    }
}
