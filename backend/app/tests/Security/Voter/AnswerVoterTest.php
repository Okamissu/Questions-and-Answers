<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Tests\Security\Voter;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Security\Voter\AnswerVoter;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class AnswerVoterTest.
 *
 * Tests the AnswerVoter behavior for different users and attributes.
 */
class AnswerVoterTest extends TestCase
{
    private AnswerVoter $voter;
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
        $this->voter = new AnswerVoter();
        $this->token = $this->createMock(TokenInterface::class);
    }

    /**
     * Test that an unauthenticated user cannot perform any actions.
     *
     * @test
     *
     * @throws Exception
     */
    public function testNotLoggedIn(): void
    {
        $this->token->method('getUser')->willReturn(null);
        $answer = $this->createMock(Answer::class);

        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::UPDATE, $answer, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::DELETE, $answer, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::MARK_BEST, $answer, $this->token));
    }

    /**
     * Test that an admin user can perform all actions.
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

        $answer = $this->createMock(Answer::class);

        $this->assertTrue($this->voter->voteOnAttribute(AnswerVoter::UPDATE, $answer, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(AnswerVoter::DELETE, $answer, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(AnswerVoter::MARK_BEST, $answer, $this->token));
    }

    /**
     * Test that the author of the answer can update and delete it,
     * but cannot mark it as best unless they are also the question author.
     *
     * @test
     *
     * @throws Exception
     */
    public function testAuthorOfAnswer(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $answer = $this->createMock(Answer::class);
        $answer->method('getAuthor')->willReturn($user);

        $this->token->method('getUser')->willReturn($user);

        // Author can update and delete
        $this->assertTrue($this->voter->voteOnAttribute(AnswerVoter::UPDATE, $answer, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(AnswerVoter::DELETE, $answer, $this->token));

        // Author cannot mark best unless they are question author
        $questionAuthor = $this->createMock(User::class);
        $questionAuthor->method('getId')->willReturn(2);

        $question = $this->createMock(Question::class);
        $question->method('getAuthor')->willReturn($questionAuthor);

        $answer->method('getQuestion')->willReturn($question);

        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::MARK_BEST, $answer, $this->token));
    }

    /**
     * Test that a user who is not the author cannot update or delete the answer.
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

        $answer = $this->createMock(Answer::class);
        $answer->method('getAuthor')->willReturn($author);

        $this->token->method('getUser')->willReturn($otherUser);

        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::UPDATE, $answer, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::DELETE, $answer, $this->token));
    }

    /**
     * Test that the question author can mark an answer as best.
     *
     * @test
     *
     * @throws Exception
     */
    public function testMarkBestByQuestionAuthor(): void
    {
        $questionAuthor = $this->createMock(User::class);
        $questionAuthor->method('getId')->willReturn(1);

        $question = $this->createMock(Question::class);
        $question->method('getAuthor')->willReturn($questionAuthor);

        $answer = $this->createMock(Answer::class);
        $answer->method('getQuestion')->willReturn($question);

        $this->token->method('getUser')->willReturn($questionAuthor);

        $this->assertTrue($this->voter->voteOnAttribute(AnswerVoter::MARK_BEST, $answer, $this->token));
    }

    /**
     * Test that a user who is not the question author cannot mark an answer as best.
     *
     * @test
     *
     * @throws Exception
     */
    public function testMarkBestByOtherUser(): void
    {
        $questionAuthor = $this->createMock(User::class);
        $questionAuthor->method('getId')->willReturn(1);

        $otherUser = $this->createMock(User::class);
        $otherUser->method('getId')->willReturn(2);

        $question = $this->createMock(Question::class);
        $question->method('getAuthor')->willReturn($questionAuthor);

        $answer = $this->createMock(Answer::class);
        $answer->method('getQuestion')->willReturn($question);

        $this->token->method('getUser')->willReturn($otherUser);

        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::MARK_BEST, $answer, $this->token));
    }

    /**
     * Test that an answer without an author cannot be updated or deleted.
     *
     * @test
     *
     * @throws Exception
     */
    public function testAnswerWithoutAuthor(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $answer = $this->createMock(Answer::class);
        $answer->method('getAuthor')->willReturn(null);

        $this->token->method('getUser')->willReturn($user);

        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::UPDATE, $answer, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::DELETE, $answer, $this->token));
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
        $user->method('getId')->willReturn(1);

        $this->token->method('getUser')->willReturn($user);

        $answer = $this->createMock(Answer::class);
        $unsupportedAttribute = 'some_random_action';

        $this->assertFalse($this->voter->voteOnAttribute($unsupportedAttribute, $answer, $this->token));
    }

    /**
     * Test the supports() method for supported and unsupported attributes and subjects.
     *
     * @test
     *
     * @throws Exception
     */
    public function testSupports(): void
    {
        $answer = $this->createMock(Answer::class);

        // Supported attributes
        $this->assertTrue($this->voter->supports(AnswerVoter::UPDATE, $answer));
        $this->assertTrue($this->voter->supports(AnswerVoter::DELETE, $answer));
        $this->assertTrue($this->voter->supports(AnswerVoter::MARK_BEST, $answer));

        // Unsupported attribute
        $this->assertFalse($this->voter->supports('random_action', $answer));

        // Unsupported subject
        $this->assertFalse($this->voter->supports(AnswerVoter::UPDATE, new \stdClass()));
    }
}
