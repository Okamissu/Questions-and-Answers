<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Dto;

use App\Entity\Question;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for creating an Answer.
 */
class CreateAnswerDto
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 10)]
    public ?string $content = null;

    #[Assert\NotNull]
    public ?Question $question = null;

    #[Assert\Type(User::class)]
    public ?User $author = null;

    #[Assert\Type('string')]
    #[Assert\NotBlank(allowNull: true)]
    public ?string $authorNickname = null;

    #[Assert\Email]
    #[Assert\NotBlank(allowNull: true)]
    public ?string $authorEmail = null;

    public bool $isBest = false;
}
