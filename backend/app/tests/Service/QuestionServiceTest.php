<?php

namespace App\Tests\Service;

use App\Dto\CreateQuestionDto;
use App\Dto\UpdateQuestionDto;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Service\QuestionService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\TestCase;

class QuestionServiceTest extends TestCase
{
    private $questionRepository;
    private QuestionService $service;

    protected function setUp(): void
    {
        $this->questionRepository = $this->createMock(QuestionRepository::class);
        $this->service = new QuestionService($this->questionRepository);
    }

    public function testCreateQuestion(): void
    {
        $dto = new CreateQuestionDto();
        $dto->title = 'Test title';
        $dto->content = 'This is a sample content for testing';
        $dto->category = new Category();
        $dto->tags = [new Tag(), new Tag()];

        $author = new User();

        $this->questionRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Question::class));

        $question = $this->service->create($dto, $author);

        $this->assertSame('Test title', $question->getTitle());
        $this->assertSame('This is a sample content for testing', $question->getContent());
        $this->assertSame($author, $question->getAuthor());
        $this->assertSame($dto->category, $question->getCategory());
        $this->assertCount(2, $question->getTags());
    }

    public function testUpdateQuestion(): void
    {
        $question = new Question();
        $question->setTitle('Old title');
        $question->setContent('Old content');
        $question->setCategory(new Category());

        $dto = new UpdateQuestionDto();
        $dto->title = 'New title';
        $dto->content = 'New content that is longer';
        $dto->category = new Category();
        $dto->tags = [new Tag()];

        $this->questionRepository
            ->expects($this->once())
            ->method('save')
            ->with($question);

        $updated = $this->service->update($question, $dto);

        $this->assertSame('New title', $updated->getTitle());
        $this->assertSame('New content that is longer', $updated->getContent());
        $this->assertSame($dto->category, $updated->getCategory());
        $this->assertCount(1, $updated->getTags());
    }

    public function testDeleteQuestion(): void
    {
        $question = new Question();

        $this->questionRepository
            ->expects($this->once())
            ->method('delete')
            ->with($question);

        $this->service->delete($question);
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
        $mockQuery->method('getResult')->willReturn(['q1', 'q2']);

        $mockQb->method('getQuery')->willReturn($mockQuery);

        $mockRepo = $this->createMock(QuestionRepository::class);
        $mockRepo->method('queryWithFilters')->willReturn($mockQb);

        $mockPaginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['count'])
            ->getMock();
        $mockPaginator->method('count')->willReturn(2);

        $service = $this->getMockBuilder(QuestionService::class)
            ->setConstructorArgs([$mockRepo])
            ->onlyMethods(['createPaginator'])
            ->getMock();

        $service->method('createPaginator')->willReturn($mockPaginator);

        $result = $service->getPaginatedList(1, 10);

        $this->assertEquals(['q1', 'q2'], $result['items']);
        $this->assertEquals(2, $result['totalItems']);
    }
}
