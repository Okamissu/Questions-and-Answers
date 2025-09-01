<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Represents a tag that can be assigned to questions.
 */
#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tags')]
class Tag
{
    /**
     * @var int|null the unique identifier of the tag
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['tag:read', 'question:read'])]
    private ?int $id = null;

    /**
     * @var string|null the name of the tag
     */
    #[ORM\Column(length: 255)]
    #[Groups(['tag:read', 'tag:write', 'question:read'])]
    private ?string $name = null;

    /**
     * @var \DateTimeImmutable|null creation timestamp
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['tag:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var \DateTimeImmutable|null last update timestamp
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['tag:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Get the tag ID.
     *
     * @return int|null The unique identifier of the tag
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the tag name.
     *
     * @return string|null The name of the tag
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the tag name.
     *
     * @param string $name The name to set for the tag
     *
     * @return static Returns the current Tag instance for method chaining
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the creation timestamp.
     *
     * @return \DateTimeImmutable|null Creation timestamp of the tag
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get the last update timestamp.
     *
     * @return \DateTimeImmutable|null Last update timestamp of the tag
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
