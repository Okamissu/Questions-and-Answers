<?php

namespace App\Resolver;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuestionValueResolver implements ValueResolverInterface
{
    public function __construct(
        private QuestionRepository $questionRepository
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== Question::class) {
            return [];
        }

        // Najpierw szukamy parametru "questionId" (np. w URL lub body)
        $questionId = $request->attributes->get('questionId')
            ?? $request->get('questionId')
            ?? null;

        if (!$questionId) {
            return [];
        }

        $question = $this->questionRepository->find($questionId);

        if (!$question) {
            throw new NotFoundHttpException('Question not found');
        }

        yield $question;
    }
}
