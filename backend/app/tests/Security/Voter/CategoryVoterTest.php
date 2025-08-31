<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Security\Voter;

use App\Entity\Category;
use App\Entity\User;
use App\Security\Voter\CategoryVoter;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class CategoryVoterTest.
 *
 * Tests the CategoryVoter behavior for admin and non-admin users.
 */
class CategoryVoterTest extends TestCase
{
    private CategoryVoter $voter;
    private TokenInterface $token;

    /**
     * Setup the voter and token before each test.
     *
     * @test
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->voter = new CategoryVoter();
        $this->token = $this->createMock(TokenInterface::class);
    }

    /**
     * Test that an unauthenticated user cannot create, update, or delete categories.
     *
     * @test
     */
    public function testNotLoggedIn(): void
    {
        $this->token->method('getUser')->willReturn(null);
        $category = new Category();

        $this->assertFalse($this->voter->voteOnAttribute(CategoryVoter::CREATE, $category, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(CategoryVoter::UPDATE, $category, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(CategoryVoter::DELETE, $category, $this->token));
    }

    /**
     * Test that an admin user can create, update, and delete categories.
     *
     * @test
     *
     * @throws Exception
     */
    public function testAdminUser(): void
    {
        $admin = $this->createMock(User::class);
        $admin->method('getRoles')->willReturn(['ROLE_ADMIN']);
        $this->token->method('getUser')->willReturn($admin);

        $category = new Category();

        $this->assertTrue($this->voter->voteOnAttribute(CategoryVoter::CREATE, $category, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(CategoryVoter::UPDATE, $category, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(CategoryVoter::DELETE, $category, $this->token));
    }

    /**
     * Test that a non-admin user cannot create, update, or delete categories.
     *
     * @test
     *
     * @throws Exception
     */
    public function testNonAdminUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn(['ROLE_USER']);
        $this->token->method('getUser')->willReturn($user);

        $category = new Category();

        $this->assertFalse($this->voter->voteOnAttribute(CategoryVoter::CREATE, $category, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(CategoryVoter::UPDATE, $category, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(CategoryVoter::DELETE, $category, $this->token));
    }

    /**
     * Test the supports() method for supported and unsupported attributes and subjects.
     *
     * @test
     */
    public function testSupports(): void
    {
        $category = new Category();

        // Supported attributes with Category
        $this->assertTrue($this->voter->supports(CategoryVoter::CREATE, $category));
        $this->assertTrue($this->voter->supports(CategoryVoter::UPDATE, $category));
        $this->assertTrue($this->voter->supports(CategoryVoter::DELETE, $category));

        // CREATE without subject (null allowed)
        $this->assertTrue($this->voter->supports(CategoryVoter::CREATE, null));

        // Unsupported attribute
        $this->assertFalse($this->voter->supports('random_action', $category));

        // Unsupported subject
        $this->assertFalse($this->voter->supports(CategoryVoter::UPDATE, new \stdClass()));
    }
}
