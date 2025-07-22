<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends AbstractBaseFixtures
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    protected function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        // Tworzymy 10 zwykłych użytkowników
        $this->createMany(10, 'user', function (int $i) {
            $user = new User();
            $user->setEmail(sprintf('user%d@example.com', $i));
            $user->setNickname(sprintf('usernick%d', $i));
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    'user1234'
                )
            );

            return $user;
        });

        // Tworzymy 3 adminów
        $this->createMany(3, 'admin', function (int $i) {
            $user = new User();
            $user->setEmail(sprintf('admin%d@example.com', $i));
            $user->setNickname(sprintf('adminnick%d', $i));
            $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    'admin1234'
                )
            );

            return $user;
        });
    }
}