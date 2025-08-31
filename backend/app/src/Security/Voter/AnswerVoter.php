<?php

namespace App\Security\Voter;

use App\Entity\Answer;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AnswerVoter extends Voter
{
    const UPDATE = 'update';
    const DELETE = 'delete';
    const MARK_BEST = 'mark_best';

    public function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::UPDATE, self::DELETE, self::MARK_BEST])
            && $subject instanceof Answer;
    }

    public function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false; // not logged in
        }

        // Admin może wszystko
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        switch ($attribute) {
            case self::UPDATE:
            case self::DELETE:
                return $this->isAuthor($subject, $user);

            case self::MARK_BEST:
                // tylko autor pytania może oznaczyć najlepszą odpowiedź
                return $subject->getQuestion()->getAuthor()->getId() === $user->getId();
        }

        return false;
    }

    private function isAuthor(Answer $answer, User $user): bool
    {
        return $answer->getAuthor() && $answer->getAuthor()->getId() === $user->getId();
    }
}
