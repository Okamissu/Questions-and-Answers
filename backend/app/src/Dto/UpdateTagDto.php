<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for updating an existing tag.
 */
class UpdateTagDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    public ?string $name = null;
}
