<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;


class UpdateAnswerDto
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 10)]
    public ?string $content = null;

    public bool $isBest = false;
}
