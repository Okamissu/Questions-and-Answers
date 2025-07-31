<?php

namespace App\Service;

use App\Dto\CreateTagDto;
use App\Dto\UpdateTagDto;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

class TagService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TagRepository $tagRepository,
    ) {
    }

    public function create(CreateTagDto $dto): Tag
    {
        $tag = new Tag();
        $tag->setName($dto->name);

        $this->em->persist($tag);
        $this->em->flush();

        return $tag;
    }

    public function update(Tag $tag, UpdateTagDto $dto): Tag
    {
        if (null !== $dto->name) {
            $tag->setName($dto->name);
        }

        $this->em->flush();

        return $tag;
    }

    public function delete(Tag $tag): void
    {
        $this->em->remove($tag);
        $this->em->flush();
    }

    public function findBySlug(string $slug): ?Tag
    {
        return $this->tagRepository->findOneBySlug($slug);
    }

    public function getAllTags(): array
    {
        return $this->tagRepository->queryAll()->getQuery()->getResult();
    }

}
