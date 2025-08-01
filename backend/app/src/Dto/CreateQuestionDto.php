<?php

namespace App\Dto;

use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Component\Validator\Constraints as Assert;

class CreateQuestionDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public ?string $title = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    public ?string $content = null;

    #[Assert\NotNull]
    public ?Category $category = null;

    /**
     * @var Tag[]|null
     */
    #[Assert\All([
        new Assert\Type(Tag::class),
    ])]
    public ?array $tags = null;

    // Author nie jest tutaj —  w kontrolerze z zalogowanego usera
}

