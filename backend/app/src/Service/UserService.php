<?php

namespace App\Service;

use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * Tworzy nowego użytkownika na podstawie DTO.
     * Haszuje hasło i zapisuje użytkownika.
     */
    public function createUser(CreateUserDto $dto): User
    {
        $user = new User();
        $user->setEmail($dto->email);
        $user->setNickname($dto->nickname);

        // Hashowanie hasła
        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->plainPassword);
        $user->setPassword($hashedPassword);

        // Walidacja encji przed zapisem (opcjonalne)
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            // możesz rzucić wyjątek lub zwrócić błędy w inny sposób
            throw new \InvalidArgumentException((string) $errors);
        }

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Aktualizuje istniejącego użytkownika na podstawie DTO.
     */
    public function updateUser(User $user, UpdateUserDto $dto): User
    {
        if (null !== $dto->email) {
            $user->setEmail($dto->email);
        }

        if (null !== $dto->nickname) {
            $user->setNickname($dto->nickname);
        }

        if (null !== $dto->plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->plainPassword);
            $user->setPassword($hashedPassword);
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Usuwa użytkownika.
     */
    public function deleteUser(User $user): void
    {
        $this->userRepository->delete($user);
    }

    /**
     * Znajduje użytkownika po emailu albo rzuca wyjątek jeśli nie ma.
     */
    public function findUserByEmailOrFail(string $email): User
    {
        $user = $this->userRepository->findOneByEmail($email);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        return $user;
    }
}
