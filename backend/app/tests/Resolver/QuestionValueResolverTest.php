<?php

namespace App\Tests\Resolver;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use App\Resolver\QuestionValueResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuestionValueResolverTest extends TestCase
{
    private QuestionRepository $repository;
    private QuestionValueResolver $resolver;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(QuestionRepository::class);
        $this->resolver = new QuestionValueResolver($this->repository);
    }

    public function testResolveReturnsQuestion()
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
        $argument->method('getType')->willReturn(Question::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    public function testResolveThrowsNotFoundExceptionIfQuestionNotFound()
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
