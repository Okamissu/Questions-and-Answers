<?php

namespace App\Tests\Service;

use App\Dto\CreateAnswerDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Repository\AnswerRepository;
use App\Service\AnswerService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AnswerServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private AnswerRepository $answerRepository;
    private AnswerService $answerService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->answerRepository = $this->createMock(AnswerRepository::class);

        $this->answerService = new AnswerService(
            $this->entityManager,
            $this->answerRepository,
        );
    }

    public function testCreate(): void
    {
        $dto = new CreateAnswerDto();
        $dto->content = 'Test answer content';
        $dto->question = $this->createMock(\App\Entity\Question::class);
        $dto->author = $this->createMock(\App\Entity\User::class);
        $dto->authorNickname = 'nickname';
        $dto->authorEmail = 'author@example.com';
        $dto->isBest = false;

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(fn(Answer $answer) =>
                $answer->getContent() === $dto->content &&
                $answer->getQuestion() === $dto->question &&
                $answer->getAuthor() === $dto->author &&
                $answer->getAuthorNickname() === $dto->authorNickname &&
                $answer->getAuthorEmail() === $dto->authorEmail &&
                $answer->getIsBest() === $dto->isBest
            ));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $answer = $this->answerService->create($dto);

        $this->assertInstanceOf(Answer::class, $answer);
        $this->assertSame($dto->content, $answer->getContent());
        $this->assertSame($dto->question, $answer->getQuestion());
        $this->assertSame($dto->author, $answer->getAuthor());
        $this->assertSame($dto->authorNickname, $answer->getAuthorNickname());
        $this->assertSame($dto->authorEmail, $answer->getAuthorEmail());
        $this->assertSame($dto->isBest, $answer->getIsBest());
    }

    public function testUpdate(): void
    {
        $answer = new Answer();
        $answer->setContent('Old content');
        $answer->setIsBest(false);

        $dto = new UpdateAnswerDto();
        $dto->content = 'New content';
        $dto->isBest = true;

        $this->entityManager->expects($this->once())
            ->method('flush');

        $updatedAnswer = $this->answerService->update($answer, $dto);

        $this->assertSame('New content', $updatedAnswer->getContent());
        $this->assertTrue($updatedAnswer->getIsBest());
    }

    public function testDelete(): void
    {
        $answer = new Answer();

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($answer);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->answerService->delete($answer);
    }

    public function testMarkAsBest(): void
    {
        $answer = new Answer();
        $answer->setIsBest(false);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->answerService->markAsBest($answer);

        $this->assertTrue($result->getIsBest());
        $this->assertSame($answer, $result);
    }
}
