<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserDto
{
    #[Assert\Email]
    public ?string $email = null; // opcjonalne, można aktualizować lub nie

    #[Assert\Length(min: 6, max: 4096)]
    public ?string $plainPassword = null; // opcjonalne

    #[Assert\Length(min: 3, max: 255)]
    public ?string $nickname = null; // opcjonalne
}
