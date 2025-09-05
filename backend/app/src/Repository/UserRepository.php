<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * Repository for User entity.
 *
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /**
     * UserRepository constructor.
     *
     * @param ManagerRegistry $registry Doctrine manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Finds all users with pagination and optional search.
     *
     * @param int         $page   The current page for pagination (default is 1)
     * @param int         $limit  The number of users per page (default is 20)
     * @param string|null $search The search query to filter by email or nickname (optional)
     *
     * @return array The list of users and pagination info
     */
    public function findAllPaginated(int $page = 1, int $limit = 20, ?string $search = null): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($search) {
            $qb->andWhere('u.email LIKE :search OR u.nickname LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        $qb->orderBy('u.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        // Get total count for pagination
        $countQb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)');
        if ($search) {
            $countQb->andWhere('u.email LIKE :search OR u.nickname LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }
        $totalItems = (int) $countQb->getQuery()->getSingleScalarResult();
        $totalPages = (int) ceil($totalItems / $limit);

        return [
            'items' => $items,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'limit' => $limit,
            ],
        ];
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     *
     * @param PasswordAuthenticatedUserInterface $user              The user whose password is upgraded
     * @param string                             $newHashedPassword The new hashed password
     *
     * @throws UnsupportedUserException When the user is not an instance of User
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Persists a User entity.
     *
     * @param User $user The user entity to save
     */
    public function save(User $user): void
    {
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();
    }

    /**
     * Removes a User entity.
     *
     * @param User $user The user entity to delete
     */
    public function delete(User $user): void
    {
        $em = $this->getEntityManager();
        $em->remove($user);
        $em->flush();
    }

    /**
     * Finds a single user by email.
     *
     * @param string $email The email of the user to find
     *
     * @return User|null The found user or null if not found
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
