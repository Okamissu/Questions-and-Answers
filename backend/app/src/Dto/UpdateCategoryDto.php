<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateCategoryDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $name = null;
}
