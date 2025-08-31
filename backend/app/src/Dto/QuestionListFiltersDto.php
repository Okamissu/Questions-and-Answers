<?php

namespace App\Dto;

class QuestionListFiltersDto
{
    public ?string $search = null;
    public ?string $sort = null;
    public ?int $limit = 10;
    public ?int $categoryId = null;
}
