<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Dto;

/**
 * Data Transfer Object for filtering and paginating questions.
 */
class QuestionListFiltersDto
{
    public ?string $search = null;
    public ?string $sort = null;
    public ?int $limit = 10;
    public ?int $categoryId = null;
    public ?int $tagId = null;
}
