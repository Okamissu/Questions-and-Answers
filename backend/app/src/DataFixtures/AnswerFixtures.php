<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class AnswerFixtures.
 *
 * Creates sample Answer entities for testing.
 */
class AnswerFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Specify fixture dependencies.
     *
     * @return array List of fixture classes this fixture depends on
     */
    public function getDependencies(): array
    {
        return [
            QuestionFixtures::class,
            UserFixtures::class,
        ];
    }

    /**
     * Load fixture data.
     */
    protected function loadData(): void
    {
        // Create 50 answers
        $this->createMany(50, 'answer', function (int $i) {
            $answer = new Answer();

            /** @var Question $question */
            $question = $this->getRandomReference('question', Question::class);
            $answer->setQuestion($question);

            $answer->setContent($this->faker->paragraphs($this->faker->numberBetween(1, 3), true));

            // Optionally assign author (70% chance)
            if ($this->faker->boolean(70)) {
                /** @var User $user */
                $user = $this->getRandomReference('user', User::class);
                $answer->setAuthor($user);
                $answer->setAuthorNickname(null);
                $answer->setAuthorEmail(null);
            } else {
                $answer->setAuthor(null);
                $answer->setAuthorNickname($this->faker->userName());
                $answer->setAuthorEmail($this->faker->optional(0.8)->email());
            }

            // isBest defaults to false; createdAt handled automatically

            return $answer;
        });
    }
}
