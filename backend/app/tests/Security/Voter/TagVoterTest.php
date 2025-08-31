<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Security\Voter;

use App\Entity\Tag;
use App\Entity\User;
use App\Security\Voter\TagVoter;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class TagVoterTest.
 *
 * Tests TagVoter for admin and non-admin users, supports() behavior, and null subject handling.
 */
class TagVoterTest extends TestCase
{
    private TagVoter $voter;
    private TokenInterface $token;

    /**
     * Setup voter and token before each test.
     *
     * @test
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->voter = new TagVoter();
        $this->token = $this->createMock(TokenInterface::class);
    }

    // ----------------------
    // voteOnAttribute()
    // ----------------------

    /**
     * Test that an unauthenticated user cannot create, update, or delete a tag.
     *
     * @test
     */
    public function testNotLoggedIn(): void
    {
        $this->token->method('getUser')->willReturn(null);
        $tag = new Tag();

        $this->assertFalse($this->voter->voteOnAttribute(TagVoter::CREATE, $tag, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(TagVoter::UPDATE, $tag, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(TagVoter::DELETE, $tag, $this->token));
    }

    /**
     * Test that an admin user can create, update, delete tags, including CREATE with null subject.
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

        $tag = new Tag();

        $this->assertTrue($this->voter->voteOnAttribute(TagVoter::CREATE, $tag, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(TagVoter::UPDATE, $tag, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(TagVoter::DELETE, $tag, $this->token));

        // CREATE with null subject
        $this->assertTrue($this->voter->voteOnAttribute(TagVoter::CREATE, null, $this->token));
    }

    /**
     * Test that a non-admin user cannot create, update, or delete tags, including CREATE with null subject.
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

        $tag = new Tag();

        $this->assertFalse($this->voter->voteOnAttribute(TagVoter::CREATE, $tag, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(TagVoter::UPDATE, $tag, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(TagVoter::DELETE, $tag, $this->token));

        // CREATE with null subject
        $this->assertFalse($this->voter->voteOnAttribute(TagVoter::CREATE, null, $this->token));
    }

    // ----------------------
    // supports()
    // ----------------------

    /**
     * Test supports() for supported and unsupported attributes and subjects.
     *
     * @test
     */
    public function testSupports(): void
    {
        $tag = new Tag();

        // Supported attributes with Tag
        $this->assertTrue($this->voter->supports(TagVoter::CREATE, $tag));
        $this->assertTrue($this->voter->supports(TagVoter::UPDATE, $tag));
        $this->assertTrue($this->voter->supports(TagVoter::DELETE, $tag));

        // CREATE without subject
        $this->assertTrue($this->voter->supports(TagVoter::CREATE, null));

        // Unsupported attribute
        $this->assertFalse($this->voter->supports('random_action', $tag));

        // Unsupported subject
        $this->assertFalse($this->voter->supports(TagVoter::UPDATE, new \stdClass()));
    }
}
