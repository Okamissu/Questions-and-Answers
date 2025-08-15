<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetSetEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testGetSetPassword(): void
    {
        $user = new User();
        $user->setPassword('hashed_password');
        $this->assertSame('hashed_password', $user->getPassword());
    }

    public function testGetSetPlainPassword(): void
    {
        $user = new User();
        $user->setPlainPassword('plain123');
        $this->assertSame('plain123', $user->getPlainPassword());

        $user->eraseCredentials();
        $this->assertNull($user->getPlainPassword());
    }

    public function testGetSetNickname(): void
    {
        $user = new User();
        $user->setNickname('symfan');
        $this->assertSame('symfan', $user->getNickname());
    }

    public function testRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testTimestampsInitiallyNull(): void
    {
        $user = new User();
        $this->assertNull($user->getCreatedAt());
        $this->assertNull($user->getUpdatedAt());
    }

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
