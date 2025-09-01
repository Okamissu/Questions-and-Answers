<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Security\Voter;

use App\Entity\Question;
use App\Entity\User;
use App\Security\Voter\QuestionVoter;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class QuestionVoterTest.
 *
 * Tests QuestionVoter for various user roles and conditions.
 */
class QuestionVoterTest extends TestCase
{
    private QuestionVoter $voter;
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
        $this->voter = new QuestionVoter();
        $this->token = $this->createMock(TokenInterface::class);
    }

    // ----------------------
    // Test supports()
    // ----------------------

    /**
     * Test the supports() method for supported and unsupported attributes and subjects.
     *
     * @test
     */
    public function testSupports(): void
    {
        $question = new Question();

        // Supported attributes
        $this->assertTrue($this->voter->supports(QuestionVoter::UPDATE, $question));
        $this->assertTrue($this->voter->supports(QuestionVoter::DELETE, $question));

        // Unsupported attribute
        $this->assertFalse($this->voter->supports('random_action', $question));

        // Unsupported subject
        $this->assertFalse($this->voter->supports(QuestionVoter::UPDATE, new \stdClass()));
    }

    // ----------------------
    // voteOnAttribute()
    // ----------------------

    /**
     * Test that an unauthenticated user cannot update or delete a question.
     *
     * @test
     */
    public function testNotLoggedIn(): void
    {
        $this->token->method('getUser')->willReturn(null);
        $question = new Question();

        $this->assertFalse($this->voter->voteOnAttribute(QuestionVoter::UPDATE, $question, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(QuestionVoter::DELETE, $question, $this->token));
    }

    /**
     * Test that an admin user can update and delete any question.
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

        $question = new Question();

        $this->assertTrue($this->voter->voteOnAttribute(QuestionVoter::UPDATE, $question, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(QuestionVoter::DELETE, $question, $this->token));
    }

    /**
     * Test that the author of the question can update and delete it.
     *
     * @test
     *
     * @throws Exception
     */
    public function testAuthorUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $question = $this->createMock(Question::class);
        $question->method('getAuthor')->willReturn($user);

        $this->token->method('getUser')->willReturn($user);

        $this->assertTrue($this->voter->voteOnAttribute(QuestionVoter::UPDATE, $question, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(QuestionVoter::DELETE, $question, $this->token));
    }

    /**
     * Test that a non-author non-admin user cannot update or delete a question.
     *
     * @test
     *
     * @throws Exception
     */
    public function testOtherUser(): void
    {
        $author = $this->createMock(User::class);
        $author->method('getId')->willReturn(1);

        $otherUser = $this->createMock(User::class);
        $otherUser->method('getId')->willReturn(2);

        $question = $this->createMock(Question::class);
        $question->method('getAuthor')->willReturn($author);

        $this->token->method('getUser')->willReturn($otherUser);

        $this->assertFalse($this->voter->voteOnAttribute(QuestionVoter::UPDATE, $question, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(QuestionVoter::DELETE, $question, $this->token));
    }

    /**
     * Test behavior when the question has no author.
     *
     * @test
     *
     * @throws Exception
     */
    public function testQuestionWithoutAuthor(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $question = $this->createMock(Question::class);
        $question->method('getAuthor')->willReturn(null);

        $this->token->method('getUser')->willReturn($user);

        $this->assertFalse($this->voter->voteOnAttribute(QuestionVoter::UPDATE, $question, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(QuestionVoter::DELETE, $question, $this->token));
    }

    /**
     * Test that unsupported attributes return false.
     *
     * @test
     *
     * @throws Exception
     */
    public function testUnsupportedAttributeReturnsFalse(): void
    {
        $user = $this->createMock(User::class);
        $this->token->method('getUser')->willReturn($user);

        $question = new Question();
        $unsupportedAttribute = 'unsupported';

        $this->assertFalse($this->voter->voteOnAttribute($unsupportedAttribute, $question, $this->token));
    }
}
