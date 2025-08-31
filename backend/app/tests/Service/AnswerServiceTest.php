<?php

namespace App\Tests\Service;

use App\Dto\CreateAnswerDto;
use App\Dto\UpdateAnswerDto;
use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\AnswerRepository;
use App\Service\AnswerService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AnswerServiceTest extends TestCase
{
    private AnswerRepository|MockObject $answerRepository;
    private AnswerService $answerService;

    protected function setUp(): void
    {
        $this->answerRepository = $this->createMock(AnswerRepository::class);
        $this->answerService = new AnswerService(
            $this->answerRepository
        );
    }

    public function testGetPaginatedList(): void
    {
        $mockQb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setFirstResult', 'setMaxResults', 'getQuery'])
            ->getMock();

        $mockQb->method('setFirstResult')->willReturnSelf();
        $mockQb->method('setMaxResults')->willReturnSelf();


        $mockQuery = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResult'])
            ->getMock();

        $mockQuery->method('getResult')->willReturn(['a1', 'a2']);
        $mockQb->method('getQuery')->willReturn($mockQuery);

        $this->answerRepository
            ->method('queryWithFilters')
            ->willReturn($mockQb);

        $mockPaginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['count'])
            ->getMock();

        $mockPaginator->method('count')->willReturn(2);

        $service = $this->getMockBuilder(AnswerService::class)
            ->setConstructorArgs([$this->answerRepository])
            ->onlyMethods(['createPaginator'])
            ->getMock();

        $service->method('createPaginator')->willReturn($mockPaginator);

        $result = $service->getPaginatedList(1, 10);

        $this->assertEquals(['a1', 'a2'], $result['items']);
        $this->assertEquals(2, $result['totalItems']);
    }

    public function testCreateSavesAndReturnsAnswer(): void
    {
        $dto = new CreateAnswerDto();
        $dto->content = 'Treść odpowiedzi musi mieć więcej niż 10 znaków';
        $dto->question = $this->createMock(Question::class);
        $dto->author = $this->createMock(User::class);
        $dto->authorNickname = 'nick123';
        $dto->authorEmail = 'mail@example.com';
        $dto->isBest = true;

        $this->answerRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($arg) use ($dto) {
                if (!$arg instanceof Answer) {
                    return false;
                }
                $this->assertSame($dto->content, $arg->getContent());
                $this->assertEquals($dto->question, $arg->getQuestion());
                $this->assertEquals($dto->author, $arg->getAuthor());
                $this->assertSame($dto->authorNickname, $arg->getAuthorNickname());
                $this->assertSame($dto->authorEmail, $arg->getAuthorEmail());

                return true;
            }));

        $result = $this->answerService->create($dto);

        $this->assertSame($dto->content, $result->getContent());
        $this->assertEquals($dto->question, $result->getQuestion());
        $this->assertEquals($dto->author, $result->getAuthor());
        $this->assertSame($dto->authorNickname, $result->getAuthorNickname());
        $this->assertSame($dto->authorEmail, $result->getAuthorEmail());
    }

    public function testUpdateWithContentUpdatesContentAndIsBest(): void
    {
        $answer = new Answer();
        $answer->setContent('stara treść');

        $dto = new UpdateAnswerDto();
        $dto->content = 'nowa treść, więcej niż 10 znaków';
        $dto->isBest = true;

        $this->answerRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($arg) use ($dto) {
                $this->assertInstanceOf(Answer::class, $arg);
                $this->assertSame($dto->content, $arg->getContent());
                if (method_exists($arg, 'GetIsBest')) {
                    $this->assertTrue($arg->GetIsBest());
                }

                return true;
            }));

        $result = $this->answerService->update($answer, $dto);

        $this->assertSame($answer, $result);
        $this->assertSame('nowa treść, więcej niż 10 znaków', $result->getContent());
    }

    public function testUpdateWithoutContentKeepsOldContentButSetsIsBest(): void
    {
        $answer = new Answer();
        $answer->setContent('pierwotna treść');

        $dto = new UpdateAnswerDto();
        $dto->content = null;
        $dto->isBest = true;

        $this->answerRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($arg) {
                $this->assertInstanceOf(Answer::class, $arg);
                $this->assertSame('pierwotna treść', $arg->getContent());
                if (method_exists($arg, 'GetIsBest')) {
                    $this->assertTrue($arg->GetIsBest());
                }

                return true;
            }));

        $result = $this->answerService->update($answer, $dto);

        $this->assertSame($answer, $result);
        $this->assertSame('pierwotna treść', $result->getContent());
    }

    public function testDeleteCallsRepositoryDelete(): void
    {
        $answer = new Answer();

        $this->answerRepository
            ->expects($this->once())
            ->method('delete')
            ->with($answer);

        $this->answerService->delete($answer);
    }

    public function testMarkAsBestUnmarksOtherBestAnswersAndSavesBoth(): void
    {
        $question = $this->createMock(Question::class);

        $mainAnswer = $this->createMock(Answer::class);
        $mainAnswer->expects($this->once())
            ->method('getQuestion')
            ->willReturn($question);
        $mainAnswer->expects($this->once())
            ->method('getId')
            ->willReturn(2);
        $mainAnswer->expects($this->once())
            ->method('setIsBest')
            ->with(true);

        $oldBest = $this->createMock(Answer::class);
        $oldBest->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $oldBest->expects($this->once())
            ->method('setIsBest')
            ->with(false);

        $this->answerRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['question' => $question, 'isBest' => true])
            ->willReturn([$oldBest]);

        $this->answerRepository
            ->expects($this->exactly(2))
            ->method('save')
            ->with($this->logicalOr(
                $this->identicalTo($oldBest),
                $this->identicalTo($mainAnswer)
            ));

        $result = $this->answerService->markAsBest($mainAnswer);

        $this->assertSame($mainAnswer, $result);
    }
}
