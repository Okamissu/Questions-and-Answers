<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Dto;

use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for creating a Question.
 */
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
     * Optional tags for the question.
     *
     * @var Tag[]|null
     */
    #[Assert\All([
        new Assert\Type(Tag::class),
    ])]
    public ?array $tags = null;
}
