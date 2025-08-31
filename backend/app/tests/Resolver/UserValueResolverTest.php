<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Resolver;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Resolver\UserValueResolver;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class UserValueResolverTest.
 *
 * Tests the UserValueResolver behavior.
 */
class UserValueResolverTest extends TestCase
{
    private UserRepository $repository;
    private UserValueResolver $resolver;

    /**
     * Setup mocks and resolver.
     *
     * @test
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepository::class);
        $this->resolver = new UserValueResolver($this->repository);
    }

    /**
     * Test resolving a user by ID.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveReturnsUser(): void
    {
        $user = new User();

        $this->repository
            ->method('find')
            ->with(42)
            ->willReturn($user);

        $request = new Request([], [], ['userId' => 42]);
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(User::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertSame($user, $result[0]);
    }

    /**
     * Test resolver returns empty if argument type does not match.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveReturnsEmptyIfTypeDoesNotMatch(): void
    {
        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn('SomeOtherClass');

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    /**
     * Test resolver returns empty if no user ID is provided.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveReturnsEmptyIfNoIdProvided(): void
    {
        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(User::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    /**
     * Test resolver throws NotFoundHttpException if user not found.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveThrowsNotFoundExceptionIfUserNotFound(): void
    {
        $this->repository
            ->method('find')
            ->with(42)
            ->willReturn(null);

        $request = new Request([], [], ['userId' => 42]);
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(User::class);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('User not found');

        iterator_to_array($this->resolver->resolve($request, $argument));
    }
}
