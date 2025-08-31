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
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

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
     * Test that upgrading password throws for unsupported user.
     *
     * @test
     */
    public function testUpgradePasswordThrowsExceptionForWrongUser(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $unsupportedUser = new class () implements PasswordAuthenticatedUserInterface {
            /**
             * Returns the hashed password used to authenticate the user.
             *
             * Usually on authentication, a plain-text password will be compared to this value.
             */
            public function getPassword(): ?string
            {
                return null;
            }

            public function getSalt(): ?string
            {
                return null;
            }

            public function eraseCredentials(): void
            {
            }
        };

        $this->repository->upgradePassword($unsupportedUser, 'hash');
    }

    /**
     * Test finding a user by email.
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
     * Helper to set a protected property via reflection.
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
