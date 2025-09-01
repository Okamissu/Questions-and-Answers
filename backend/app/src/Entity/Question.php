<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Represents a question posted by a user.
 */
#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\Table(name: 'questions')]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['question:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['question:read', 'question:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['question:read', 'question:write'])]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['question:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['question:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['question:read'])]
    private ?User $author = null;

    #[ORM\ManyToOne(targetEntity: Category::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['question:read'])]
    private ?Category $category = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'questions_tags')]
    #[Groups(['question:read'])]
    private Collection $tags;

    /**
     * Constructor initializes the tags collection.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * Get the question ID.
     *
     * @return int|null The unique identifier of the question
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the question title.
     *
     * @return string|null The title of the question
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the question title.
     *
     * @param string|null $title The title to set for the question
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Get the question content.
     *
     * @return string|null The content/body of the question
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the question content.
     *
     * @param string|null $content The content/body to set for the question
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * Get creation timestamp.
     *
     * @return \DateTimeImmutable|null The date and time when the question was created
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get last update timestamp.
     *
     * @return \DateTimeImmutable|null The date and time when the question was last updated
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Get the author of the question.
     *
     * @return User|null The user who created the question
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Set the author of the question.
     *
     * @param User|null $author The user to set as author of the question
     */
    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    /**
     * Get the category of the question.
     *
     * @return Category|null The category this question belongs to
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Set the category of the question.
     *
     * @param Category|null $category The category to associate with this question
     */
    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    /**
     * Get all tags associated with the question.
     *
     * @return Collection<int, Tag> Collection of tags assigned to the question
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add a tag to the question.
     *
     * @param Tag $tag The tag to add
     */
    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    /**
     * Remove a tag from the question.
     *
     * @param Tag $tag The tag to remove
     */
    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }
}
