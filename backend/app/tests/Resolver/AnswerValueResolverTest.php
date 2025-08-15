<?php

namespace App\Tests\Resolver;

use App\Entity\Answer;
use App\Repository\AnswerRepository;
use App\Resolver\AnswerValueResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AnswerValueResolverTest extends TestCase
{
    private AnswerRepository $repository;
    private AnswerValueResolver $resolver;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AnswerRepository::class);
        $this->resolver = new AnswerValueResolver($this->repository);
    }

    public function testResolveReturnsAnswer(): void
    {
        $answer = new Answer();

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(42)
            ->willReturn($answer);

        $request = new Request([], [], ['answerId' => 42]);
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Answer::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertSame($answer, $result[0]);
    }

    public function testResolveReturnsEmptyIfTypeDoesNotMatch(): void
    {
        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn('SomeOtherClass');

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    public function testResolveReturnsEmptyIfNoIdProvided(): void
    {
        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Answer::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    public function testResolveThrowsNotFoundExceptionIfAnswerNotFound(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(42)
            ->willReturn(null);

        $request = new Request([], [], ['answerId' => 42]);
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Answer::class);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Answer not found');

        iterator_to_array($this->resolver->resolve($request, $argument));
    }
}
