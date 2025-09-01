<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for creating a User.
 */
class CreateUserDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6, max: 4096)]
    public ?string $plainPassword = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $nickname = null;
}
