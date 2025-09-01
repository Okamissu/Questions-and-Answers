<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for updating an existing answer.
 */
class UpdateAnswerDto
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 10)]
    public ?string $content = null;

    public bool $isBest = false;
}
