<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class UserRepositoryTest.
 *
 * Tests core repository functions of UserRepository.
 */
class UserRepositoryTest extends TestCase
{
    private EntityManagerInterface $em;
    private ManagerRegistry $registry;
    private UserRepository $repository;

    /**
     * Set up mocks and repository.
     *
     * This method initializes the mocks for the EntityManager, ManagerRegistry,
     * and UserRepository to be used in the tests.
     *
     * @test
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->name = User::class;

        $this->em->method('getClassMetadata')->willReturn($metadata);
        $this->registry->method('getManagerForClass')->willReturn($this->em);

        $this->repository = $this->getMockBuilder(UserRepository::class)
            ->setConstructorArgs([$this->registry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $this->setProtectedProperty($this->repository, $this->em);
    }

    /**
     * Test saving a User entity.
     *
     * This test ensures that the save method correctly persists and flushes the User entity.
     *
     * @test
     */
    public function testSave(): void
    {
        $user = new User();

        $this->em->expects($this->once())->method('persist')->with($user);
        $this->em->expects($this->once())->method('flush');

        $this->repository->save($user);
    }

    /**
     * Test deleting a User entity.
     *
     * This test ensures that the delete method correctly removes and flushes the User entity.
     *
     * @test
     */
    public function testDelete(): void
    {
        $user = new User();

        $this->em->expects($this->once())->method('remove')->with($user);
        $this->em->expects($this->once())->method('flush');

        $this->repository->delete($user);
    }

    /**
     * Test upgrading a User password.
     *
     * This test ensures that the password upgrade method correctly persists and flushes the User entity
     * with the new password.
     *
     * @test
     */
    public function testUpgradePassword(): void
    {
        $user = new User();

        $this->em->expects($this->once())->method('persist')->with($user);
        $this->em->expects($this->once())->method('flush');

        $this->repository->upgradePassword($user, 'newHashedPassword');

        $this->assertSame('newHashedPassword', $user->getPassword());
    }

    /**
     * Test finding a user by email.
     *
     * This test ensures that the findOneByEmail method correctly queries the database
     * and returns the result.
     *
     * @test
     *
     * @throws Exception
     */
    public function testFindOneByEmail(): void
    {
        $email = 'test@example.com';

        $query = $this->createMock(Query::class);
        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn(null);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects($this->once())
            ->method('andWhere')
            ->with('u.email = :email')
            ->willReturnSelf();
        $qb->expects($this->once())
            ->method('setParameter')
            ->with('email', $email)
            ->willReturnSelf();
        $qb->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->repository->method('createQueryBuilder')->willReturn($qb);

        $this->repository->findOneByEmail($email);
    }

    /**
     * Test findAllPaginated with search term.
     *
     * This test ensures that the paginated user search functionality is working
     * correctly by testing the search term, pagination, and total count.
     *
     * @test
     *
     * @throws Exception
     */
    public function testFindAllPaginatedWithSearch(): void
    {
        $search = 'term';
        $page = 2;
        $limit = 10;

        // Mock query for items
        $query = $this->createMock(Query::class);
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn(['user1', 'user2']);

        $qbItems = $this->createMock(QueryBuilder::class);
        $qbItems->expects($this->once())
            ->method('andWhere')
            ->with('u.email LIKE :search OR u.nickname LIKE :search')
            ->willReturnSelf();
        $qbItems->expects($this->once())
            ->method('setParameter')
            ->with('search', '%'.$search.'%')
            ->willReturnSelf();
        $qbItems->expects($this->once())
            ->method('orderBy')
            ->with('u.createdAt', 'DESC')
            ->willReturnSelf();
        $qbItems->expects($this->once())
            ->method('setFirstResult')
            ->with(($page - 1) * $limit)
            ->willReturnSelf();
        $qbItems->expects($this->once())
            ->method('setMaxResults')
            ->with($limit)
            ->willReturnSelf();
        $qbItems->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        // Mock query for count
        $countQuery = $this->createMock(Query::class);
        $countQuery->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn(25);

        $qbCount = $this->createMock(QueryBuilder::class);
        $qbCount->expects($this->once())
            ->method('select')
            ->with('COUNT(u.id)')
            ->willReturnSelf();
        $qbCount->expects($this->once())
            ->method('andWhere')
            ->with('u.email LIKE :search OR u.nickname LIKE :search')
            ->willReturnSelf();
        $qbCount->expects($this->once())
            ->method('setParameter')
            ->with('search', '%'.$search.'%')
            ->willReturnSelf();
        $qbCount->expects($this->once())
            ->method('getQuery')
            ->willReturn($countQuery);

        // Repository returns different QB for items vs count
        $this->repository->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($qbItems, $qbCount);

        $result = $this->repository->findAllPaginated($page, $limit, $search);

        $this->assertSame(['user1', 'user2'], $result['items']);
        $this->assertSame($page, $result['pagination']['currentPage']);
        $this->assertSame(3, $result['pagination']['totalPages']); // ceil(25/10)
        $this->assertSame(25, $result['pagination']['totalItems']);
        $this->assertSame($limit, $result['pagination']['limit']);
    }

    /**
     * Helper to set a protected property via reflection.
     *
     * Sets the protected property on the object to the provided value.
     *
     * @param object $object the object to modify
     * @param mixed  $value  the value to set
     */
    private function setProtectedProperty(object $object, $value): void
    {
        $refObject = new \ReflectionObject($object);

        while (!$refObject->hasProperty('em')) {
            $parent = $refObject->getParentClass();
            if (!$parent) {
                throw new \RuntimeException('Property em not found');
            }
            $refObject = $parent;
        }

        $refProperty = $refObject->getProperty('em');
        $refProperty->setValue($object, $value);
    }
}
