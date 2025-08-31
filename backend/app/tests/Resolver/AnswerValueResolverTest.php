<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Resolver;

use App\Entity\Answer;
use App\Repository\AnswerRepository;
use App\Resolver\AnswerValueResolver;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AnswerValueResolverTest.
 *
 * Tests the AnswerValueResolver behavior.
 */
class AnswerValueResolverTest extends TestCase
{
    private AnswerRepository $repository;
    private AnswerValueResolver $resolver;

    /**
     * Set up repository mock and resolver.
     *
     * @test
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->repository = $this->createMock(AnswerRepository::class);
        $this->resolver = new AnswerValueResolver($this->repository);
    }

    /**
     * Test resolving an existing Answer entity by ID.
     *
     * @test
     *
     * @throws Exception
     */
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

    /**
     * Test resolving returns empty if type does not match.
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
     * Test resolving returns empty if no answerId is provided in request.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveReturnsEmptyIfNoIdProvided(): void
    {
        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Answer::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    /**
     * Test resolving throws NotFoundHttpException if answer not found.
     *
     * @test
     *
     * @throws Exception
     */
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
