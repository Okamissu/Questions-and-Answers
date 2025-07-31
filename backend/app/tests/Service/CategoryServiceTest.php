<?php

namespace App\Tests\Service;

use App\Dto\CreateCategoryDto;
use App\Dto\UpdateCategoryDto;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CategoryServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private CategoryRepository $categoryRepository;
    private CategoryService $categoryService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);

        $this->categoryService = new CategoryService(
            $this->entityManager,
            $this->categoryRepository,
        );
    }

    public function testCreate(): void
    {
        $dto = new CreateCategoryDto();
        $dto->name = 'Test Category';

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Category $category) use ($dto) {
                return $category->getName() === $dto->name;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $category = $this->categoryService->create($dto);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame($dto->name, $category->getName());
    }

    public function testUpdate(): void
    {
        $category = new Category();
        $category->setName('Old Name');

        $dto = new UpdateCategoryDto();
        $dto->name = 'New Name';

        $this->entityManager->expects($this->once())
            ->method('flush');

        $updatedCategory = $this->categoryService->update($category, $dto);

        $this->assertSame('New Name', $updatedCategory->getName());
    }

    public function testUpdateWithNullNameDoesNotChange(): void
    {
        $category = new Category();
        $category->setName('Old Name');

        $dto = new UpdateCategoryDto();
        $dto->name = null;

        $this->entityManager->expects($this->once())
            ->method('flush');

        $updatedCategory = $this->categoryService->update($category, $dto);

        $this->assertSame('Old Name', $updatedCategory->getName());
    }

    public function testDelete(): void
    {
        $category = new Category();

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($category);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->categoryService->delete($category);
    }

    public function testFindBySlug(): void
    {
        $slug = 'test-slug';
        $category = new Category();
        $category->setName('Test Category');

        $this->categoryRepository->expects($this->once())
            ->method('findBySlug')
            ->with($slug)
            ->willReturn($category);

        $result = $this->categoryService->findBySlug($slug);

        $this->assertSame($category, $result);
    }
}
