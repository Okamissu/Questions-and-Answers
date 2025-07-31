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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class QuestionServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private QuestionRepository $questionRepository;
    private QuestionService $questionService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->questionRepository = $this->createMock(QuestionRepository::class);

        $this->questionService = new QuestionService(
            $this->entityManager,
            $this->questionRepository,
        );
    }

    public function testCreate(): void
    {
        $author = new User();
        $author->setEmail('author@example.com');
        $category = new Category();
        $category->setName('Category 1');

        $tag1 = new Tag();
        $tag1->setName('tag1');
        $tag2 = new Tag();
        $tag2->setName('tag2');

        $dto = new CreateQuestionDto();
        $dto->title = 'Test Question';
        $dto->content = 'Question content';
        $dto->category = $category;
        $dto->tags = [$tag1, $tag2];

        // EntityManager expects persist and flush once
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Question::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $question = $this->questionService->create($dto, $author);

        $this->assertInstanceOf(Question::class, $question);
        $this->assertSame('Test Question', $question->getTitle());
        $this->assertSame('Question content', $question->getContent());
        $this->assertSame($author, $question->getAuthor());
        $this->assertSame($category, $question->getCategory());

        $tags = $question->getTags();
        $this->assertCount(2, $tags);
        $this->assertTrue($tags->contains($tag1));
        $this->assertTrue($tags->contains($tag2));
    }

    public function testCreateWithoutTags(): void
    {
        $author = new User();
        $category = new Category();

        $dto = new CreateQuestionDto();
        $dto->title = 'No tags question';
        $dto->content = 'Content';
        $dto->category = $category;
        $dto->tags = null;

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Question::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $question = $this->questionService->create($dto, $author);

        $this->assertInstanceOf(Question::class, $question);
        $this->assertSame(0, $question->getTags()->count());
    }

    public function testUpdate(): void
    {
        $categoryOld = new Category();
        $categoryOld->setName('Old category');

        $categoryNew = new Category();
        $categoryNew->setName('New category');

        $tagOld = new Tag();
        $tagOld->setName('oldtag');

        $tagNew1 = new Tag();
        $tagNew1->setName('newtag1');
        $tagNew2 = new Tag();
        $tagNew2->setName('newtag2');

        $question = new Question();
        $question->setTitle('Old Title');
        $question->setContent('Old Content');
        $question->setCategory($categoryOld);
        $question->addTag($tagOld);

        $dto = new UpdateQuestionDto();
        $dto->title = 'New Title';
        $dto->content = 'New Content';
        $dto->category = $categoryNew;
        $dto->tags = [$tagNew1, $tagNew2];

        $this->entityManager->expects($this->once())
            ->method('flush');

        $updatedQuestion = $this->questionService->update($question, $dto);

        $this->assertSame('New Title', $updatedQuestion->getTitle());
        $this->assertSame('New Content', $updatedQuestion->getContent());
        $this->assertSame($categoryNew, $updatedQuestion->getCategory());

        $tags = $updatedQuestion->getTags();
        $this->assertCount(2, $tags);
        $this->assertTrue($tags->contains($tagNew1));
        $this->assertTrue($tags->contains($tagNew2));
        $this->assertFalse($tags->contains($tagOld));
    }

    public function testUpdatePartial(): void
    {
        $category = new Category();
        $category->setName('Category');

        $question = new Question();
        $question->setTitle('Title');
        $question->setContent('Content');
        $question->setCategory($category);

        $dto = new UpdateQuestionDto();
        $dto->title = null;
        $dto->content = 'Updated Content';
        $dto->category = null;
        $dto->tags = null;

        $this->entityManager->expects($this->once())
            ->method('flush');

        $updatedQuestion = $this->questionService->update($question, $dto);

        $this->assertSame('Title', $updatedQuestion->getTitle());
        $this->assertSame('Updated Content', $updatedQuestion->getContent());
        $this->assertSame($category, $updatedQuestion->getCategory());
        $this->assertCount(0, $updatedQuestion->getTags()); // no tags change
    }

    public function testDelete(): void
    {
        $question = new Question();

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($question);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->questionService->delete($question);
    }
}
