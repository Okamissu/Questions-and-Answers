<?php

namespace App\Tests\Security\Voter;

use App\Entity\Category;
use App\Entity\User;
use App\Security\Voter\CategoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CategoryVoterTest extends TestCase
{
    private CategoryVoter $voter;
    private TokenInterface $token;

    protected function setUp(): void
    {
        $this->voter = new CategoryVoter();
        $this->token = $this->createMock(TokenInterface::class);
    }

    public function testNotLoggedIn(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $category = new Category();

        $this->assertFalse($this->voter->voteOnAttribute(CategoryVoter::CREATE, $category, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(CategoryVoter::UPDATE, $category, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(CategoryVoter::DELETE, $category, $this->token));
    }

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

    public function testSupports(): void
    {
        $category = new Category();

        // Supported attributes with Category
        $this->assertTrue($this->voter->supports(CategoryVoter::CREATE, $category));
        $this->assertTrue($this->voter->supports(CategoryVoter::UPDATE, $category));
        $this->assertTrue($this->voter->supports(CategoryVoter::DELETE, $category));

        // CREATE without subject
        $this->assertTrue($this->voter->supports(CategoryVoter::CREATE, null));

        // Unsupported attribute
        $this->assertFalse($this->voter->supports('random_action', $category));

        // Unsupported subject
        $this->assertFalse($this->voter->supports(CategoryVoter::UPDATE, new \stdClass()));
    }
}
