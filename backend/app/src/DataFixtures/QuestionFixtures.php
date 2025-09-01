<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

namespace App\DataFixtures;

use App\Entity\Question;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Tag;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class QuestionFixtures.
 *
 * Fixture for creating sample Question entities.
 */
class QuestionFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Returns dependencies for this fixture.
     *
     * @return array<int, class-string>
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
            TagFixtures::class,
        ];
    }

    /**
     * Load data for questions.
     */
    protected function loadData(): void
    {
        $this->createMany(30, 'question', function (int $i) {
            $question = new Question();
            $question->setTitle($this->faker->sentence(6));
            $question->setContent($this->faker->paragraphs(3, true));

            // Author
            /** @var User $author */
            $author = $this->getRandomReference('user', User::class);
            $question->setAuthor($author);

            // Category
            /** @var Category $category */
            $category = $this->getRandomReference('category', Category::class);
            $question->setCategory($category);

            // Tags: 0 to 3 random tags
            $tags = $this->getRandomReferenceList('tag', Tag::class, $this->faker->numberBetween(0, 3));
            foreach ($tags as $tag) {
                $question->addTag($tag);
            }

            return $question;
        });
    }
}
