<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Service;

use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserService.
 *
 * Service responsible for managing User entities: creation, update, deletion, and retrieval.
 */
class UserService implements UserServiceInterface
{
    /**
     * UserService constructor.
     *
     * @param UserRepository              $userRepository Repository for User entities
     * @param UserPasswordHasherInterface $passwordHasher Service for hashing user passwords
     * @param ValidatorInterface          $validator      Service for validating entities
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly UserPasswordHasherInterface $passwordHasher, private readonly ValidatorInterface $validator)
    {
    }

    /**
     * Creates a new user from the provided DTO, hashes the password, validates, and saves it.
     *
     * @param CreateUserDto $dto Data transfer object containing user data
     *
     * @return User The created user entity
     *
     * @throws \InvalidArgumentException if validation fails
     */
    public function createUser(CreateUserDto $dto): User
    {
        $user = new User();
        $user->setEmail($dto->email);
        $user->setNickname($dto->nickname);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->plainPassword);
        $user->setPassword($hashedPassword);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Updates an existing user with the provided DTO, hashes password if changed, validates, and saves it.
     *
     * @param User          $user User entity to update
     * @param UpdateUserDto $dto  Data transfer object containing updated values
     *
     * @return User The updated user entity
     *
     * @throws \InvalidArgumentException if validation fails
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
     * Deletes a user entity.
     *
     * @param User $user User entity to delete
     */
    public function deleteUser(User $user): void
    {
        $this->userRepository->delete($user);
    }

    /**
     * Finds a user by email or throws a NotFoundHttpException if not found.
     *
     * @param string $email Email of the user to find
     *
     * @return User The found user entity
     *
     * @throws NotFoundHttpException if no user is found with the given email
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
