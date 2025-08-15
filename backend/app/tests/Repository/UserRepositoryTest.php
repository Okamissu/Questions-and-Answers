<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends TestCase
{
    private EntityManagerInterface $em;
    private ManagerRegistry $registry;
    private UserRepository $repository;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->name = User::class;

        $this->em->method('getClassMetadata')->willReturn($metadata);
        $this->registry->method('getManagerForClass')->willReturn($this->em);

        // Create partial mock for repository to override createQueryBuilder
        $this->repository = $this->getMockBuilder(UserRepository::class)
            ->setConstructorArgs([$this->registry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        // Inject mocked EM
        $this->setProtectedProperty($this->repository, 'em', $this->em);
    }

    public function testSave(): void
    {
        $user = new User();

        $this->em->expects($this->once())->method('persist')->with($user);
        $this->em->expects($this->once())->method('flush');

        $this->repository->save($user);
    }

    public function testDelete(): void
    {
        $user = new User();

        $this->em->expects($this->once())->method('remove')->with($user);
        $this->em->expects($this->once())->method('flush');

        $this->repository->delete($user);
    }

    public function testUpgradePassword(): void
    {
        $user = new User();

        $this->em->expects($this->once())->method('persist')->with($user);
        $this->em->expects($this->once())->method('flush');

        $this->repository->upgradePassword($user, 'newHashedPassword');

        $this->assertEquals('newHashedPassword', $user->getPassword());
    }

    public function testUpgradePasswordThrowsExceptionForWrongUser(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $this->repository->upgradePassword(new class implements PasswordAuthenticatedUserInterface {
            public function getPassword(): ?string { return null; }
            public function getSalt(): ?string { return null; }
            public function eraseCredentials(): void {}
        }, 'hash');
    }

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

    private function setProtectedProperty(object $object, string $property, $value): void
    {
        $refObject = new \ReflectionObject($object);
        while (!$refObject->hasProperty($property)) {
            $parent = $refObject->getParentClass();
            if (!$parent) {
                throw new \RuntimeException("Property {$property} not found");
            }
            $refObject = $parent;
        }
        $refProperty = $refObject->getProperty($property);
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, $value);
    }
}
