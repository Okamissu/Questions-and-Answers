<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateCategoryDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $name = null;
}

