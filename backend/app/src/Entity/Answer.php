<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents an answer to a question.
 *
 * @author Kamil Kobylarz
 */
#[ORM\Entity(repositoryClass: AnswerRepository::class)]
#[ORM\Table(name: 'answers')]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['answer:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['answer:read', 'answer:write'])]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['answer:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['answer:read', 'answer:write'])]
    private bool $isBest = false;

    #[ORM\ManyToOne(targetEntity: Question::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['answer:read'])]
    private ?Question $question = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['answer:read'])]
    private ?User $author = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['answer:read', 'answer:write'])]
    private ?string $authorNickname = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['answer:read', 'answer:write'])]
    private ?string $authorEmail = null;

    /**
     * Get the unique identifier of the answer.
     *
     * @return int|null The ID of the answer, or null if not persisted
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the content of the answer.
     *
     * @return string|null The text content of the answer, or null if not set
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the content of the answer.
     *
     * @param string|null $content The text content to set for the answer
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * Get the creation timestamp.
     *
     * @return \DateTimeImmutable|null The date and time the answer was created
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Check if this answer is marked as the best.
     *
     * @return bool True if the answer is the best answer, false otherwise
     */
    public function getIsBest(): bool
    {
        return $this->isBest;
    }

    /**
     * Mark this answer as the best answer.
     *
     * @param bool $isBest Whether the answer should be marked as best
     */
    public function setIsBest(bool $isBest): void
    {
        $this->isBest = $isBest;
    }

    /**
     * Get the associated question.
     *
     * @return Question|null The question this answer belongs to
     */
    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    /**
     * Set the associated question.
     *
     * @param Question|null $question The question to associate with this answer
     */
    public function setQuestion(?Question $question): void
    {
        $this->question = $question;
    }

    /**
     * Get the author of the answer.
     *
     * @return User|null The user who authored the answer, or null if anonymous
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Set the author of the answer.
     *
     * @param User|null $author The user to set as author, or null for anonymous
     */
    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    /**
     * Get the anonymous nickname if no author exists.
     *
     * @return string|null The nickname used for anonymous answers
     */
    public function getAuthorNickname(): ?string
    {
        return $this->authorNickname;
    }

    /**
     * Set the anonymous nickname.
     *
     * @param string|null $authorNickname The nickname for an anonymous answer
     */
    public function setAuthorNickname(?string $authorNickname): void
    {
        $this->authorNickname = $authorNickname;
    }

    /**
     * Get the anonymous email if no author exists.
     *
     * @return string|null The email of an anonymous answer author
     */
    public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }

    /**
     * Set the anonymous email.
     *
     * @param string|null $authorEmail The email of an anonymous answer author
     */
    public function setAuthorEmail(?string $authorEmail): void
    {
        $this->authorEmail = $authorEmail;
    }

    /**
     * Check if the answer is from an anonymous user.
     *
     * @return bool True if no registered user authored the answer, false otherwise
     */
    public function isFromAnonymous(): bool
    {
        return null === $this->author;
    }

    /**
     * Get the display name of the answer author.
     *
     * @return string|null Returns the registered author's nickname, or the anonymous nickname
     */
    public function getDisplayName(): ?string
    {
        return $this->author ? $this->author->getNickname() : $this->authorNickname;
    }
}
