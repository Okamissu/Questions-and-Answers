<?php

namespace App\Service;

use App\Dto\CreateTagDto;
use App\Dto\UpdateTagDto;
use App\Entity\Tag;
use App\Repository\TagRepository;

class TagService
{
    public function __construct(
        private TagRepository $tagRepository,
    ) {
    }

    public function create(CreateTagDto $dto): Tag
    {
        $tag = new Tag();
        $tag->setName($dto->name);

        $this->tagRepository->save($tag);

        return $tag;
    }

    public function update(Tag $tag, UpdateTagDto $dto): Tag
    {
        if (null !== $dto->name) {
            $tag->setName($dto->name);
        }

        $this->tagRepository->save($tag);

        return $tag;
    }

    public function delete(Tag $tag): void
    {

        $this->tagRepository->delete($tag);
    }

    public function getAllTags(): array
    {
        return $this->tagRepository->queryAll()->getQuery()->getResult();
    }

}
