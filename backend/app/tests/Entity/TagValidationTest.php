<?php

namespace App\Tests\Entity;

use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TagValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    private function createValidTag(): Tag
    {
        $tag = new Tag();
        $tag->setName('Valid Tag');

        return $tag;
    }

    public function testValidTagPassesValidation(): void
    {
        $tag = $this->createValidTag();
        $errors = $this->validator->validate($tag);
        $this->assertCount(0, $errors);
    }

    public function testNameIsRequired(): void
    {
        $tag = $this->createValidTag();
        $tag->setName('');

        $errors = $this->validator->validate($tag);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testNameTooShort(): void
    {
        $tag = $this->createValidTag();
        $tag->setName('a'); // 1 znak, poniżej min 2

        $errors = $this->validator->validate($tag);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testNameTooLong(): void
    {
        $tag = $this->createValidTag();
        $tag->setName(str_repeat('a', 51)); // 51 znaków, powyżej max 50

        $errors = $this->validator->validate($tag);
        $this->assertGreaterThan(0, count($errors));
    }
}
