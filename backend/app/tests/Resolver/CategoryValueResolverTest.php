<?php

namespace App\Tests\Resolver;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Resolver\CategoryValueResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryValueResolverTest extends TestCase
{
    private CategoryRepository $repository;
    private CategoryValueResolver $resolver;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CategoryRepository::class);
        $this->resolver = new CategoryValueResolver($this->repository);
    }

    public function testResolveReturnsCategory()
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
        $argument->method('getType')->willReturn(Category::class);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    public function testResolveThrowsNotFoundExceptionIfCategoryNotFound()
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
