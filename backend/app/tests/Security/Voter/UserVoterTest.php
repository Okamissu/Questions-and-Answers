<?php

namespace App\Tests\Security\Voter;

use App\Entity\User;
use App\Security\Voter\UserVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoterTest extends TestCase
{
    private UserVoter $voter;
    private TokenInterface $token;

    protected function setUp(): void
    {
        $this->voter = new UserVoter();
        $this->token = $this->createMock(TokenInterface::class);
    }

    public function testNotLoggedIn(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $user = new User();

        $this->assertFalse($this->voter->voteOnAttribute(UserVoter::VIEW, $user, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(UserVoter::UPDATE, $user, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(UserVoter::DELETE, $user, $this->token));
    }

    public function testAdminUser(): void
    {
        $admin = $this->createMock(User::class);
        $admin->method('getRoles')->willReturn(['ROLE_ADMIN']);
        $this->token->method('getUser')->willReturn($admin);

        $otherUser = new User();

        $this->assertTrue($this->voter->voteOnAttribute(UserVoter::VIEW, $otherUser, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(UserVoter::UPDATE, $otherUser, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(UserVoter::DELETE, $otherUser, $this->token));
    }

    public function testSelfUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $this->token->method('getUser')->willReturn($user);

        $otherUser = $this->createMock(User::class);
        $otherUser->method('getId')->willReturn(1);

        $this->assertTrue($this->voter->voteOnAttribute(UserVoter::VIEW, $otherUser, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(UserVoter::UPDATE, $otherUser, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(UserVoter::DELETE, $otherUser, $this->token));
    }

    public function testOtherUser(): void
    {
        $currentUser = $this->createMock(User::class);
        $currentUser->method('getId')->willReturn(1);
        $this->token->method('getUser')->willReturn($currentUser);

        $otherUser = $this->createMock(User::class);
        $otherUser->method('getId')->willReturn(2);

        $this->assertFalse($this->voter->voteOnAttribute(UserVoter::VIEW, $otherUser, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(UserVoter::UPDATE, $otherUser, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(UserVoter::DELETE, $otherUser, $this->token));
    }

    public function testSupports(): void
    {
        $user = new User();

        $this->assertTrue($this->voter->supports(UserVoter::VIEW, $user));
        $this->assertTrue($this->voter->supports(UserVoter::UPDATE, $user));
        $this->assertTrue($this->voter->supports(UserVoter::DELETE, $user));

        // Unsupported attribute
        $this->assertFalse($this->voter->supports('random_action', $user));

        // Unsupported subject
        $this->assertFalse($this->voter->supports(UserVoter::VIEW, new \stdClass()));
    }

    public function testVoteOnAttributeWithUnsupportedAttribute(): void
    {
        $currentUser = $this->createMock(User::class);
        $currentUser->method('getId')->willReturn(1);
        $this->token->method('getUser')->willReturn($currentUser);

        $targetUser = $this->createMock(User::class);
        $targetUser->method('getId')->willReturn(1);

        // Use an unsupported attribute
        $this->assertFalse($this->voter->voteOnAttribute('random_action', $targetUser, $this->token));
    }
}
