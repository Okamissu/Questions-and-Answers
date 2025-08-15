<?php

namespace App\Service;

use App\Dto\CreateUserDto;
use App\Dto\UpdateUserDto;
use App\Entity\User;

interface UserServiceInterface
{
    public function createUser(CreateUserDto $dto): User;

    public function updateUser(User $user, UpdateUserDto $dto): User;

    public function deleteUser(User $user): void;

    /**
     * Finds a user by email or throws an exception if not found.
     */
    public function findUserByEmailOrFail(string $email): User;
}
