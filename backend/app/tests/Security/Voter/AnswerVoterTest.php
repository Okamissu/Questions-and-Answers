<?php

namespace App\Tests\Security\Voter;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Security\Voter\AnswerVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AnswerVoterTest extends TestCase
{
    private AnswerVoter $voter;
    private TokenInterface $token;

    protected function setUp(): void
    {
        $this->voter = new AnswerVoter();
        $this->token = $this->createMock(TokenInterface::class);
    }

    public function testNotLoggedIn(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $answer = $this->createMock(Answer::class);

        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::UPDATE, $answer, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::DELETE, $answer, $this->token));
        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::MARK_BEST, $answer, $this->token));
    }

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

    public function testAuthorOfAnswer(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $answer = $this->createMock(Answer::class);
        $answer->method('getAuthor')->willReturn($user);

        $this->token->method('getUser')->willReturn($user);

        // author can update/delete
        $this->assertTrue($this->voter->voteOnAttribute(AnswerVoter::UPDATE, $answer, $this->token));
        $this->assertTrue($this->voter->voteOnAttribute(AnswerVoter::DELETE, $answer, $this->token));

        // author cannot mark_best unless question author
        $questionAuthor = $this->createMock(User::class);
        $questionAuthor->method('getId')->willReturn(2); // different user

        $question = $this->createMock(Question::class);
        $question->method('getAuthor')->willReturn($questionAuthor);

        $answer->method('getQuestion')->willReturn($question);

        $this->assertFalse($this->voter->voteOnAttribute(AnswerVoter::MARK_BEST, $answer, $this->token));
    }


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

    public function testUnsupportedAttributeReturnsFalse(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $this->token->method('getUser')->willReturn($user);

        $answer = $this->createMock(Answer::class);

        // Use a random unsupported attribute
        $unsupportedAttribute = 'some_random_action';

        $this->assertFalse($this->voter->voteOnAttribute($unsupportedAttribute, $answer, $this->token));
    }

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
