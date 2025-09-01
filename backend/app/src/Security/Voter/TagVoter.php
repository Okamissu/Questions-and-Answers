<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Security\Voter;

use App\Entity\Tag;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for Tag entity permissions.
 */
class TagVoter extends Voter
{
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    /**
     * Checks if the voter supports the given attribute and subject.
     *
     * @param string $attribute The action to check (create, update, delete)
     * @param mixed  $subject   The subject being voted on (Tag entity or null for create)
     *
     * @return bool True if supported, false otherwise
     */
    public function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::UPDATE, self::DELETE], true)
            && ($subject instanceof Tag || null === $subject);
    }

    /**
     * Performs the access check on the given attribute, subject, and user.
     *
     * @param string         $attribute The action being checked
     * @param Tag|null       $subject   The Tag entity (or null for create)
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

        // Admin can manage all tags
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        // Other users are not allowed
        return false;
    }
}
