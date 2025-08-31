<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Resolver;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Resolver\TagValueResolver;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TagValueResolverTest.
 *
 * Tests the TagValueResolver behavior.
 */
class TagValueResolverTest extends TestCase
{
    private TagRepository $repository;
    private TagValueResolver $resolver;

    /**
     * Setup mocks and resolver.
     *
     * @test
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->repository = $this->createMock(TagRepository::class);
        $this->resolver = new TagValueResolver($this->repository);
    }

    /**
     * Test resolving a tag by ID.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveReturnsTag(): void
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
     * Test resolver returns empty if no tag ID is provided.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveReturnsEmptyIfNoIdProvided(): void
    {
        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Tag::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    /**
     * Test resolver throws NotFoundHttpException if tag not found.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveThrowsNotFoundExceptionIfTagNotFound(): void
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
