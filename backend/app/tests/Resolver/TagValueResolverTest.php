<?php

namespace App\Tests\Resolver;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Resolver\TagValueResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TagValueResolverTest extends TestCase
{
    private TagRepository $repository;
    private TagValueResolver $resolver;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TagRepository::class);
        $this->resolver = new TagValueResolver($this->repository);
    }

    public function testResolveReturnsTag()
    {
        $tag = new Tag();

        $this->repository
            ->method('find')
            ->with(42)
            ->willReturn($tag);

        $request = new Request([], [], ['tagId' => 42]);
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Tag::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertSame($tag, $result[0]);
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
        $argument->method('getType')->willReturn(Tag::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    public function testResolveThrowsNotFoundExceptionIfTagNotFound()
    {
        $this->repository
            ->method('find')
            ->with(42)
            ->willReturn(null);

        $request = new Request([], [], ['tagId' => 42]);
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Tag::class);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Tag not found');

        iterator_to_array($this->resolver->resolve($request, $argument));
    }
}
