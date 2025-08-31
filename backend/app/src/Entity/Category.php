<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Represents a category for questions or other entities.
 *
 * @author Kamil Kobylarz
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'categories')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['category:read', 'question:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['category:read', 'category:write', 'question:read'])]
    private ?string $name = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['category:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['category:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Get the category ID.
     *
     * @return int|null The unique identifier of the category
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the name of the category.
     *
     * @return string|null The name of the category
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name of the category.
     *
     * @param string $name The name to assign to the category
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the creation timestamp.
     *
     * @return \DateTimeImmutable|null The date and time when the category was created
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get the last update timestamp.
     *
     * @return \DateTimeImmutable|null The date and time when the category was last updated
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
