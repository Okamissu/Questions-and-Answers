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
    /**
     * @var int|null the unique identifier of the answer
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['answer:read'])]
    private ?int $id = null;

    /**
     * @var string|null the content of the answer
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['answer:read', 'answer:write'])]
    private ?string $content = null;

    /**
     * @var \DateTimeImmutable|null the creation date of the answer
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['answer:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var bool whether this answer is marked as the best answer
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['answer:read', 'answer:write'])]
    private bool $isBest = false;

    /**
     * @var Question|null the question associated with this answer
     */
    #[ORM\ManyToOne(targetEntity: Question::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['answer:read'])]
    private ?Question $question = null;

    /**
     * @var User|null The author of the answer. Null if anonymous.
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['answer:read'])]
    private ?User $author = null;

    /**
     * @var string|null nickname for anonymous authors
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['answer:read', 'answer:write'])]
    private ?string $authorNickname = null;

    /**
     * @var string|null email for anonymous authors
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['answer:read', 'answer:write'])]
    private ?string $authorEmail = null;

    /**
     * Get the answer ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the content of the answer.
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the content of the answer.
     *
     * @param string|null $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * Get the creation timestamp.
     *
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Check if this answer is the best.
     *
     * @return bool
     */
    public function getIsBest(): bool
    {
        return $this->isBest;
    }

    /**
     * Mark this answer as the best.
     *
     * @param bool $isBest
     */
    public function setIsBest(bool $isBest): void
    {
        $this->isBest = $isBest;
    }

    /**
     * Get the associated question.
     *
     * @return Question|null
     */
    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    /**
     * Set the associated question.
     *
     * @param Question|null $question
     */
    public function setQuestion(?Question $question): void
    {
        $this->question = $question;
    }

    /**
     * Get the author of the answer.
     *
     * @return User|null
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Set the author of the answer.
     *
     * @param User|null $author
     */
    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    /**
     * Get the anonymous nickname if no author.
     *
     * @return string|null
     */
    public function getAuthorNickname(): ?string
    {
        return $this->authorNickname;
    }

    /**
     * Set the anonymous nickname.
     *
     * @param string|null $authorNickname
     */
    public function setAuthorNickname(?string $authorNickname): void
    {
        $this->authorNickname = $authorNickname;
    }

    /**
     * Get the anonymous email if no author.
     *
     * @return string|null
     */
    public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }

    /**
     * Set the anonymous email.
     *
     * @param string|null $authorEmail
     */
    public function setAuthorEmail(?string $authorEmail): void
    {
        $this->authorEmail = $authorEmail;
    }

    /**
     * Check if the answer is from an anonymous user.
     *
     * @return bool
     */
    public function isFromAnonymous(): bool
    {
        return null === $this->author;
    }

    /**
     * Get the display name: author nickname or anonymous nickname.
     *
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->author ? $this->author->getNickname() : $this->authorNickname;
    }
}
