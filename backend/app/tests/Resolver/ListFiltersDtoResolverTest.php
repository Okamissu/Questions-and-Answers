<?php

namespace App\Tests\Resolver;

use App\Dto\ListFiltersDto;
use App\Resolver\ListFiltersDtoResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ListFiltersDtoResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $request = new Request([
            'search' => 'test',
            'sort' => 'asc',
            'limit' => 25,
        ]);

        $argumentMetadata = $this->createMock(ArgumentMetadata::class);

        $resolver = new ListFiltersDtoResolver();
        $generator = $resolver->resolve($request, $argumentMetadata);
        $result = [];
        foreach ($generator as $dto) {
            $result[] = $dto;
        }


        $this->assertCount(1, $result);
        $dto = $result[0];

        $this->assertInstanceOf(ListFiltersDto::class, $dto);
        $this->assertEquals('test', $dto->search);
        $this->assertEquals('asc', $dto->sort);
        $this->assertEquals(25, $dto->limit);
    }

    public function testResolveWithDefaults(): void
    {
        $request = new Request(); // no query params
        $argumentMetadata = $this->createMock(ArgumentMetadata::class);

        $resolver = new ListFiltersDtoResolver();
        $result = iterator_to_array($resolver->resolve($request, $argumentMetadata));

        $dto = $result[0];
        $this->assertEquals(null, $dto->search);
        $this->assertEquals(null, $dto->sort);
        $this->assertEquals(10, $dto->limit); // default
    }
}
