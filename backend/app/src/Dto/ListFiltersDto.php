<?php

namespace App\Dto;

class ListFiltersDto
{
    public ?string $search = null;
    public ?string $sort = null;
    public ?int $limit = 10;
}
