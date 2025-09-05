<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Service;

use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Entity\User;

/**
 * Interface UserServiceInterface.
 *
 * Defines the contract for managing User entities: creation, update, deletion, and retrieval.
 */
interface UserServiceInterface
{
    /**
     * Creates a new user from the provided DTO.
     *
     * @param CreateUserDto $dto Data transfer object containing user data
     *
     * @return User The created user entity
     */
    public function createUser(CreateUserDto $dto): User;

    /**
     * Updates an existing user with values from the provided DTO.
     *
     * @param User          $user User entity to update
     * @param UpdateUserDto $dto  Data transfer object containing updated values
     *
     * @return User The updated user entity
     */
    public function updateUser(User $user, UpdateUserDto $dto): User;

    /**
     * Deletes a user entity.
     *
     * @param User $user User entity to delete
     */
    public function deleteUser(User $user): void;

    /**
     * Finds a user by email or throws an exception if not found.
     *
     * @param string $email Email of the user to find
     *
     * @return User The found user entity
     */
    public function findUserByEmailOrFail(string $email): User;

    /**
     *  Gets all of users.
     *
     * @param int         $page   pagination
     * @param int         $limit  page limit
     * @param string|null $search search query
     *
     * @return array Array of found users
     */
    public function getUsers(int $page = 1, int $limit = 20, ?string $search = null): array;
}
