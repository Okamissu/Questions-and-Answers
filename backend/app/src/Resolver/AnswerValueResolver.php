<?php

namespace App\Resolver;

use App\Entity\Answer;
use App\Repository\AnswerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AnswerValueResolver implements ValueResolverInterface
{
    public function __construct(
        private AnswerRepository $answerRepository
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== Answer::class) {
            return [];
        }

        $answerId = $request->attributes->get('answerId')
            ?? $request->get('answerId')
            ?? null;

        if (!$answerId) {
            return [];
        }

        $answer = $this->answerRepository->find($answerId);

        if (!$answer) {
            throw new NotFoundHttpException('Answer not found');
        }

        yield $answer;
    }
}
