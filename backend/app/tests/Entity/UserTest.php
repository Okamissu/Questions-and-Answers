<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Class UserTest.
 *
 * Tests basic getters and setters of the User entity.
 */
class UserTest extends TestCase
{
    /**
     * Test setting and getting email.
     *
     * @test
     */
    public function testGetSetEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    /**
     * Test setting and getting password.
     *
     * @test
     */
    public function testGetSetPassword(): void
    {
        $user = new User();
        $user->setPassword('hashed_password');
        $this->assertSame('hashed_password', $user->getPassword());
    }

    /**
     * Test setting and erasing plain password.
     *
     * @test
     */
    public function testGetSetPlainPassword(): void
    {
        $user = new User();
        $user->setPlainPassword('plain123');
        $this->assertSame('plain123', $user->getPlainPassword());

        $user->eraseCredentials();
        $this->assertNull($user->getPlainPassword());
    }

    /**
     * Test setting and getting nickname.
     *
     * @test
     */
    public function testGetSetNickname(): void
    {
        $user = new User();
        $user->setNickname('symfan');
        $this->assertSame('symfan', $user->getNickname());
    }

    /**
     * Test setting and getting roles.
     *
     * @test
     */
    public function testRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    /**
     * Test that timestamps are initially null.
     *
     * @test
     */
    public function testTimestampsInitiallyNull(): void
    {
        $user = new User();
        $this->assertNull($user->getCreatedAt());
        $this->assertNull($user->getUpdatedAt());
    }

    /**
     * Test getting the ID property.
     *
     * @test
     */
    public function testGetId(): void
    {
        $user = new User();
        $this->assertNull($user->getId());

        // Simulate DB-assigned ID
        $reflection = new \ReflectionProperty(User::class, 'id');
        $reflection->setValue($user, 42);

        $this->assertSame(42, $user->getId());
    }
}
