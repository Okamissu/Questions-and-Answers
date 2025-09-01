<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\Resolver;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resolves Question entities from request parameters for controller arguments.
 */
class QuestionValueResolver implements ValueResolverInterface
{
    /**
     * QuestionValueResolver constructor.
     *
     * @param QuestionRepository $questionRepository Repository used to fetch Question entities
     */
    public function __construct(private readonly QuestionRepository $questionRepository)
    {
    }

    /**
     * Resolves a Question entity from the request.
     *
     * @param Request          $request  The current HTTP request
     * @param ArgumentMetadata $argument Metadata for the controller argument
     *
     * @return \Traversable<Question> Yields the resolved Question entity
     *
     * @throws NotFoundHttpException If the question with the given ID does not exist
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Question::class !== $argument->getType()) {
            return [];
        }

        $questionId = $request->attributes->get('questionId') ?? $request->get('questionId');

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
