<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Resolver;

use App\Dto\ListFiltersDto;
use App\Resolver\ListFiltersDtoResolver;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Class ListFiltersDtoResolverTest.
 *
 * Tests the ListFiltersDtoResolver behavior.
 */
class ListFiltersDtoResolverTest extends TestCase
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
        $request = new Request([
            'search' => 'test',
            'sort' => 'asc',
            'limit' => 25,
        ]);

        $argumentMetadata = $this->createMock(ArgumentMetadata::class);

        $resolver = new ListFiltersDtoResolver();
        $result = iterator_to_array($resolver->resolve($request, $argumentMetadata));

        $this->assertCount(1, $result);

        $dto = $result[0];
        $this->assertInstanceOf(ListFiltersDto::class, $dto);
        $this->assertEquals('test', $dto->search);
        $this->assertEquals('asc', $dto->sort);
        $this->assertEquals(25, $dto->limit);
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

        $resolver = new ListFiltersDtoResolver();
        $result = iterator_to_array($resolver->resolve($request, $argumentMetadata));

        $this->assertCount(1, $result);

        $dto = $result[0];
        $this->assertInstanceOf(ListFiltersDto::class, $dto);
        $this->assertNull($dto->search);
        $this->assertNull($dto->sort);
        $this->assertEquals(10, $dto->limit); // default limit
    }
}
