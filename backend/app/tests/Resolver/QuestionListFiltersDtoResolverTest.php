<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Resolver;

use App\Dto\QuestionListFiltersDto;
use App\Resolver\QuestionListFiltersDtoResolver;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Class QuestionListFiltersDtoResolverTest.
 *
 * Tests the QuestionListFiltersDtoResolver behavior.
 */
class QuestionListFiltersDtoResolverTest extends TestCase
{
    /**
     * Test resolving DTO from request with parameters.
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolve(): void
    {
        // Pass parameters as query parameters
        $request = new Request(query: [
            'search'   => 'question',
            'sort'     => 'desc',
            'limit'    => 15,
            'categoryId' => 5,
        ]);

        $argumentMetadata = $this->createMock(ArgumentMetadata::class);

        $resolver = new QuestionListFiltersDtoResolver();
        $result = iterator_to_array($resolver->resolve($request, $argumentMetadata));

        $this->assertCount(1, $result);

        $dto = $result[0];
        $this->assertInstanceOf(QuestionListFiltersDto::class, $dto);
        $this->assertEquals('question', $dto->search);
        $this->assertEquals('desc', $dto->sort);
        $this->assertEquals(15, $dto->limit);
        $this->assertEquals(5, $dto->categoryId);
    }

    /**
     * Test resolving DTO from request without parameters (defaults applied).
     *
     * @test
     *
     * @throws Exception
     */
    public function testResolveWithDefaults(): void
    {
        $request = new Request(); // no query params
        $argumentMetadata = $this->createMock(ArgumentMetadata::class);

        $resolver = new QuestionListFiltersDtoResolver();
        $result = iterator_to_array($resolver->resolve($request, $argumentMetadata));

        $this->assertCount(1, $result);

        $dto = $result[0];
        $this->assertInstanceOf(QuestionListFiltersDto::class, $dto);
        $this->assertNull($dto->search);
        $this->assertNull($dto->sort);
        $this->assertEquals(10, $dto->limit); // default
        $this->assertNull($dto->categoryId);  // default
    }
}
