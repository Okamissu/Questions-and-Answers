<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for updating an existing user.
 * All fields are optional; only provided fields will be updated.
 */
class UpdateUserDto
{
    #[Assert\Email]
    public ?string $email = null; // optional

    #[Assert\Length(min: 6, max: 4096)]
    public ?string $plainPassword = null; // optional

    #[Assert\Length(min: 3, max: 255)]
    public ?string $nickname = null; // optional
}
