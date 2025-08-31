<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Dto;

/**
 * Data Transfer Object for filtering and paginating lists.
 */
class ListFiltersDto
{
    public ?string $search = null;
    public ?string $sort = null;
    public ?int $limit = 10;
}
