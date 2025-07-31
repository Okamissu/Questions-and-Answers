<?php
namespace App\Dto;

use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateQuestionDto
{
    #[Assert\Length(min: 3, max: 255)]
    public ?string $title = null;

    #[Assert\Length(min: 10)]
    public ?string $content = null;

    public ?Category $category = null;

    /**
     * @var Tag[]|null
     */
    public ?array $tags = null;
}
