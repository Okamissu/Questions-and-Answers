<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AnswerFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        // Tworzymy np. 50 odpowiedzi
        $this->createMany(50, 'answer', function (int $i) {
            $answer = new Answer();

            // Losujemy pytanie, do którego przypiszemy odpowiedź
            /** @var Question $question */
            $question = $this->getRandomReference('question', Question::class);
            $answer->setQuestion($question);

            // Przypisujemy losową treść odpowiedzi
            $answer->setContent($this->faker->paragraphs($this->faker->numberBetween(1, 3), true));

            // Opcjonalnie przypisujemy autora (czasem anonim)
            if ($this->faker->boolean(70)) { // 70% szans, że będzie autor
                /** @var User $user */
                $user = $this->getRandomReference('user', User::class);
                $answer->setAuthor($user);
                $answer->setAuthorNickname(null);
                $answer->setAuthorEmail(null);
            } else {
                // Anonim - ustawiamy nickname i email fikcyjny
                $answer->setAuthor(null);
                $answer->setAuthorNickname($this->faker->userName());
                $answer->setAuthorEmail($this->faker->optional(0.8)->email()); // 80% szans na email anonimowy
            }

            // Nie ustawiamy isBest - domyślnie false
            // createdAt ustawi się automatycznie przez Gedmo Timestampable

            return $answer;
        });
    }

    public function getDependencies(): array
    {
        return [
            QuestionFixtures::class,
            UserFixtures::class,
        ];
    }
}
