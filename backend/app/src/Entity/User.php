<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Represents a user of the system.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_NICKNAME', fields: ['nickname'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['nickname'], message: 'This nickname is already taken')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'question:read', 'answer:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    #[Groups(['user:read', 'user:write', 'question:read', 'answer:read'])]
    private ?string $nickname = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    private ?string $plainPassword = null;

    /**
     * Get the user ID.
     *
     * @return int|null The unique identifier of the user
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the user email.
     *
     * @return string|null Email address of the user
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the user email.
     *
     * @param string $email Email address to set
     *
     * @return static Returns the current User instance for method chaining
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Returns the identifier used to authenticate the user (email).
     *
     * @return string Email identifier of the user
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Get user roles.
     *
     * Always includes ROLE_USER.
     *
     * @return list<string> Array of role strings
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Set user roles.
     *
     * @param list<string> $roles Array of roles to assign
     *
     * @return static Returns the current User instance for method chaining
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get hashed password.
     *
     * @return string|null Returns the hashed password of the user
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set hashed password.
     *
     * @param string $password The hashed password to store
     *
     * @return static Returns the current User instance for method chaining
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get nickname.
     *
     * @return string|null User's display nickname
     */
    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    /**
     * Set nickname.
     *
     * @param string|null $nickname Nickname to set for the user
     *
     * @return static Returns the current User instance for method chaining
     */
    public function setNickname(?string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Get account creation timestamp.
     *
     * @return \DateTimeImmutable|null Timestamp of account creation
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get last update timestamp.
     *
     * @return \DateTimeImmutable|null Timestamp of last account update
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Get temporary plain password.
     *
     * @return string|null Plain password (not persisted)
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Set temporary plain password.
     *
     * @param string|null $plainPassword Plain password to temporarily store
     *
     * @return static Returns the current User instance for method chaining
     */
    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Removes sensitive data from the user.
     *
     * @return void This method clears any temporary sensitive data
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }
}
