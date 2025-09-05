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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a question posted by a user.
 *
 * @ORM\Entity(repositoryClass=QuestionRepository::class)
 *
 * @ORM\Table(name="questions")
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

    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'question', cascade: ['remove'], orphanRemoval: true)]
    #[Groups(['question:read'])]
    private Collection $answers;

    /**
     * Constructor initializes the collections.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->answers = new ArrayCollection();
    }

    // ---------- Getters & Setters ----------

    /**
     * Get the ID of the question.
     *
     * @return int|null The ID of the question
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the title of the question.
     *
     * @return string|null The title of the question
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the title of the question.
     *
     * @param string|null $title The title of the question
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Get the content of the question.
     *
     * @return string|null The content of the question
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the content of the question.
     *
     * @param string|null $content The content of the question
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * Get the creation date of the question.
     *
     * @return \DateTimeImmutable|null The creation date of the question
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get the last updated date of the question.
     *
     * @return \DateTimeImmutable|null The last updated date of the question
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Get the author of the question.
     *
     * @return User|null The author of the question
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Set the author of the question.
     *
     * @param User|null $author The author of the question
     */
    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    /**
     * Get the category of the question.
     *
     * @return Category|null The category of the question
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Set the category of the question.
     *
     * @param Category|null $category The category of the question
     */
    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    /**
     * Get the tags associated with the question.
     *
     * @return Collection The tags associated with the question
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

    /**
     * Get all answers for this question.
     *
     * @return Collection<int, Answer> The answers for the question
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    /**
     * Add an answer to this question.
     *
     * @param Answer $answer The answer to add
     */
    public function addAnswer(Answer $answer): void
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }
    }

    /**
     * Remove an answer from this question.
     *
     * @param Answer $answer The answer to remove
     */
    public function removeAnswer(Answer $answer): void
    {
        if ($this->answers->removeElement($answer)) {
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }
    }
}
