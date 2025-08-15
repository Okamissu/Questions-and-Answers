<?php

namespace App\Tests\Resolver;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Resolver\UserValueResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserValueResolverTest extends TestCase
{
    private UserRepository $repository;
    private UserValueResolver $resolver;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepository::class);
        $this->resolver = new UserValueResolver($this->repository);
    }

    public function testResolveReturnsUser()
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

    public function testResolveReturnsEmptyIfTypeDoesNotMatch()
    {
        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn('SomeOtherClass');

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    public function testResolveReturnsEmptyIfNoIdProvided()
    {
        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(User::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    public function testResolveThrowsNotFoundExceptionIfUserNotFound()
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
