<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Dto;

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
    public ?int $categoryId = null; // <-- tylko ID

    /**
     * Optional tags for the question.
     *
     * @var int[]|null
     */
    #[Assert\All([new Assert\Type('int')])]
    public ?array $tagIds = null;
}
