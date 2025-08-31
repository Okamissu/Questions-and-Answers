<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Resolver;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use App\Resolver\QuestionValueResolver;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class QuestionValueResolverTest.
 *
 * Tests the QuestionValueResolver behavior.
 */
class QuestionValueResolverTest extends TestCase
{
    private QuestionRepository $repository;
    private QuestionValueResolver $resolver;

    /**
     * Setup mocks and resolver.
     *
     * @test
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->repository = $this->createMock(QuestionRepository::class);
        $this->resolver = new QuestionValueResolver($this->repository);
    }

    /**
     * Test resolving a question by ID.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveReturnsQuestion(): void
    {
        $question = new Question();

        $this->repository
            ->method('find')
            ->with(42)
            ->willReturn($question);

        $request = new Request([], [], ['questionId' => 42]);
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Question::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertSame($question, $result[0]);
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
     * Test resolver returns empty if no question ID is provided.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveReturnsEmptyIfNoIdProvided(): void
    {
        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Question::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    /**
     * Test resolver throws NotFoundHttpException if question not found.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveThrowsNotFoundExceptionIfQuestionNotFound(): void
    {
        $this->repository
            ->method('find')
            ->with(42)
            ->willReturn(null);

        $request = new Request([], [], ['questionId' => 42]);
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Question::class);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Question not found');

        iterator_to_array($this->resolver->resolve($request, $argument));
    }
}
