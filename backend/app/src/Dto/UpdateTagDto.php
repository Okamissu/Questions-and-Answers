<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTagDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    public ?string $name = null;
}
