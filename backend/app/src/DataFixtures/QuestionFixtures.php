<?php

namespace App\DataFixtures;

use App\Entity\Question;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Tag;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class QuestionFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{

    public function loadData(): void
    {
        $this->createMany(30, 'question', function (int $i) {
            $question = new Question();
            $question->setTitle($this->faker->sentence(6));
            $question->setContent($this->faker->paragraphs(3, true));

            // Autor
            /** @var User $author */
            $author = $this->getRandomReference('user', User::class);
            $question->setAuthor($author);

            // Kategoria
            /** @var Category $category */
            $category = $this->getRandomReference('category', Category::class);
            $question->setCategory($category);

            // Tagowanie: od 0 do 3 tagów
            $tags = $this->getRandomReferenceList('tag', Tag::class, $this->faker->numberBetween(0, 3));
            foreach ($tags as $tag) {
                $question->addTag($tag);
            }

            return $question;
        });
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
            TagFixtures::class,
        ];
    }
}