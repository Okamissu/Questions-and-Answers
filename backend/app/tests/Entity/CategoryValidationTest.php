<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    private function createValidCategory(): Category
    {
        $category = new Category();
        $category->setName('Valid Category Name');

        return $category;
    }

    public function testValidCategoryPassesValidation(): void
    {
        $category = $this->createValidCategory();
        $errors = $this->validator->validate($category);
        $this->assertCount(0, $errors);
    }

    public function testNameIsRequired(): void
    {
        $category = $this->createValidCategory();
        $category->setName('');

        $errors = $this->validator->validate($category);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testNameTooShort(): void
    {
        $category = $this->createValidCategory();
        $category->setName('ab'); // 2 znaki, poniżej min 3

        $errors = $this->validator->validate($category);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testNameTooLong(): void
    {
        $category = $this->createValidCategory();
        $category->setName(str_repeat('a', 256)); // 256 znaków, powyżej max 255

        $errors = $this->validator->validate($category);
        $this->assertGreaterThan(0, count($errors));
    }
}
