<?php

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

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
    #[Assert\NotBlank]
    #[Assert\Type('string')]
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
    #[Assert\Type('string')]
    #[Groups(['answer:read', 'answer:write'])]
    private ?string $authorNickname = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email]
    #[Groups(['answer:read', 'answer:write'])]
    private ?string $authorEmail = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isBest(): bool
    {
        return $this->isBest;
    }

    public function setIsBest(bool $isBest): void
    {
        $this->isBest = $isBest;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): void
    {
        $this->question = $question;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    public function getAuthorNickname(): ?string
    {
        return $this->authorNickname;
    }

    public function setAuthorNickname(?string $authorNickname): void
    {
        $this->authorNickname = $authorNickname;
    }

    public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail(?string $authorEmail): void
    {
        $this->authorEmail = $authorEmail;
    }

    public function isFromAnonymous(): bool
    {
        return $this->author === null;
    }

    public function getDisplayName(): ?string
    {
        return $this->author !== null
            ? $this->author->getNickname()
            : $this->authorNickname;
    }
}
