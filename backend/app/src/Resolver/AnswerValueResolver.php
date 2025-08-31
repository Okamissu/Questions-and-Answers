<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Resolver;

use App\Entity\Answer;
use App\Repository\AnswerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resolves Answer entities from request parameters for controller arguments.
 */
class AnswerValueResolver implements ValueResolverInterface
{
    /**
     * AnswerValueResolver constructor.
     *
     * @param AnswerRepository $answerRepository Repository used to fetch Answer entities
     */
    public function __construct(private readonly AnswerRepository $answerRepository)
    {
    }

    /**
     * Resolves an Answer entity from the request.
     *
     * @param Request          $request  The current HTTP request
     * @param ArgumentMetadata $argument Metadata for the controller argument
     *
     * @return \Traversable<Answer> Yields the resolved Answer entity
     *
     * @throws NotFoundHttpException If the answer with the given ID does not exist
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Answer::class !== $argument->getType()) {
            return [];
        }

        $answerId = $request->attributes->get('answerId') ?? $request->get('answerId');

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
