<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    private function createValidUser(): User
    {
        $user = new User();
        $user->setEmail('valid@example.com');
        $user->setNickname('validnick');
        $user->setPlainPassword('validpass');

        return $user;
    }

    public function testValidUserPassesValidation(): void
    {
        $user = $this->createValidUser();
        $errors = $this->validator->validate($user);
        $this->assertCount(0, $errors);
    }

    public function testEmailNotBlank(): void
    {
        $user = $this->createValidUser();
        $user->setEmail('');

        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testEmailInvalidFormat(): void
    {
        $user = $this->createValidUser();
        $user->setEmail('not-an-email');

        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testPlainPasswordTooShort(): void
    {
        $user = $this->createValidUser();
        $user->setPlainPassword('123');

        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testNicknameCanBeNull(): void
    {
        $user = $this->createValidUser();
        $user->setNickname(null);

        $errors = $this->validator->validate($user);
        $this->assertCount(0, $errors);
    }
}
