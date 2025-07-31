<?php
namespace App\Dto;

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
    public ?int $categoryId = null;

    // author nie podajemy tu — ustawimy ją w kontrolerze z aktualnie zalogowanego usera

    /**
     * @var int[]|null
     */
    #[Assert\All([
        new Assert\Type('int')
    ])]
    public ?array $tagIds = null;
}
