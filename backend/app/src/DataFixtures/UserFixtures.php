<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserFixtures.
 *
 * Fixture for creating sample User entities (regular users and admins).
 */
class UserFixtures extends AbstractBaseFixtures
{
    /**
     * UserFixtures constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher Password hasher service
     */
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * Load sample users data.
     */
    protected function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        // Create 10 regular users
        $this->createMany(10, 'user', function (int $i) {
            $user = new User();
            $user->setEmail(sprintf('user%d@example.com', $i));
            $user->setNickname(sprintf('usernick%d', $i));
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'user1234'));

            return $user;
        });

        // Create 3 admin users
        $this->createMany(3, 'admin', function (int $i) {
            $user = new User();
            $user->setEmail(sprintf('admin%d@example.com', $i));
            $user->setNickname(sprintf('adminnick%d', $i));
            $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'admin1234'));

            return $user;
        });
    }
}
