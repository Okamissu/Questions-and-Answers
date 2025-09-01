<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Resolver;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Resolver\CategoryValueResolver;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CategoryValueResolverTest.
 *
 * Tests the CategoryValueResolver behavior.
 */
class CategoryValueResolverTest extends TestCase
{
    private CategoryRepository $repository;
    private CategoryValueResolver $resolver;

    /**
     * Set up repository mock and resolver.
     *
     * @test
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->repository = $this->createMock(CategoryRepository::class);
        $this->resolver = new CategoryValueResolver($this->repository);
    }

    /**
     * Test resolving an existing Category entity by ID.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveReturnsCategory(): void
    {
        $category = new Category();

        $this->repository
            ->method('find')
            ->with(42)
            ->willReturn($category);

        $request = new Request([], [], ['categoryId' => 42]);
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Category::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertSame($category, $result[0]);
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
     * Test resolving returns empty if no categoryId is provided in request.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveReturnsEmptyIfNoIdProvided(): void
    {
        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Category::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    /**
     * Test resolving throws NotFoundHttpException if category not found.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveThrowsNotFoundExceptionIfCategoryNotFound(): void
    {
        $this->repository
            ->method('find')
            ->with(42)
            ->willReturn(null);

        $request = new Request([], [], ['categoryId' => 42]);
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(Category::class);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Category not found');

        iterator_to_array($this->resolver->resolve($request, $argument));
    }
}
