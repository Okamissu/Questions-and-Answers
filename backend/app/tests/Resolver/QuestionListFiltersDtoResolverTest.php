<?php

namespace App\Tests\Resolver;

use App\Dto\QuestionListFiltersDto;
use App\Resolver\QuestionListFiltersDtoResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class QuestionListFiltersDtoResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $request = new Request([
            'search' => 'question',
            'sort' => 'desc',
            'limit' => 15,
            'category' => 5
        ]);

        $argumentMetadata = $this->createMock(ArgumentMetadata::class);

        $resolver = new QuestionListFiltersDtoResolver();
        $generator = $resolver->resolve($request, $argumentMetadata);
        $result = [];
        foreach ($generator as $dto) {
            $result[] = $dto;
        }


        $this->assertCount(1, $result);
        $dto = $result[0];

        $this->assertInstanceOf(QuestionListFiltersDto::class, $dto);
        $this->assertEquals('question', $dto->search);
        $this->assertEquals('desc', $dto->sort);
        $this->assertEquals(15, $dto->limit);
        $this->assertEquals(5, $dto->categoryId);
    }

    public function testResolveWithDefaults(): void
    {
        $request = new Request(); // no query params
        $argumentMetadata = $this->createMock(ArgumentMetadata::class);

        $resolver = new QuestionListFiltersDtoResolver();
        $result = iterator_to_array($resolver->resolve($request, $argumentMetadata));

        $dto = $result[0];
        $this->assertEquals(null, $dto->search);
        $this->assertEquals(null, $dto->sort);
        $this->assertEquals(10, $dto->limit); // default
        $this->assertEquals(null, $dto->categoryId); // default
    }
}
