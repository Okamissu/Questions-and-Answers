<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Dto;

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
    #[Assert\Type('integer')]
    public ?int $questionId = null;

    public ?User $author = null;

    public ?string $authorNickname = null;

    public ?string $authorEmail = null;

    public bool $isBest = false;
}
