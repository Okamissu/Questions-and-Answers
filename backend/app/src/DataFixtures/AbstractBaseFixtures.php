<?php

/*
 * (c) 2025 Kamil Kobylarz (Uniwersytet Jagielloński, Elektroniczne Przetwarzanie Informacji)
 */

/**
 * Base fixtures.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

/**
 * Class AbstractBaseFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
abstract class AbstractBaseFixtures extends Fixture
{
    /**
     * Faker generator.
     */
    protected ?Generator $faker = null;

    /**
     * Doctrine object manager.
     */
    protected ?ObjectManager $manager = null;

    /**
     * Load fixtures.
     *
     * @param ObjectManager $manager Persistence object manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker = Factory::create();
        $this->loadData();
    }

    /**
     * Load data for fixtures.
     */
    abstract protected function loadData(): void;

    /**
     * Create many objects at once.
     *
     * Example usage:
     *      $this->createMany(10, 'user', function(int $i) {
     *          $user = new User();
     *          $user->setFirstName('Ryan');
     *          return $user;
     *      });
     *
     * @param int      $count              Number of objects to create
     * @param string   $referenceGroupName Group name for references
     * @param callable $factory            Callback to create each object
     */
    protected function createMany(int $count, string $referenceGroupName, callable $factory): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $entity = $factory($i);

            if (null === $entity) {
                throw new \LogicException('Did you forget to return the entity object from your callback to BaseFixture::createMany()?');
            }

            $this->manager->persist($entity);
            $this->addReference(sprintf('%s_%d', $referenceGroupName, $i), $entity);
        }

        $this->manager->flush();
    }

    /**
     * Get a random reference object by group and class.
     *
     * @param string $referenceGroupName Reference group name
     * @param string $className          Class name
     *
     * @return object Random object reference
     */
    protected function getRandomReference(string $referenceGroupName, string $className): object
    {
        $referenceNameList = $this->getReferenceNameListByClassName($referenceGroupName, $className);
        $randomReferenceName = (string) $this->faker->randomElement($referenceNameList);

        return $this->getReference($randomReferenceName, $className);
    }

    /**
     * Get multiple random reference objects by group and class.
     *
     * @param string $referenceGroupName Reference group name
     * @param string $className          Class name
     * @param int    $count              Number of objects
     *
     * @return object[] Array of random objects
     */
    protected function getRandomReferenceList(string $referenceGroupName, string $className, int $count): array
    {
        $referenceNameList = $this->getReferenceNameListByClassName($referenceGroupName, $className);
        $references = [];
        while (count($references) < $count) {
            $randomReferenceName = (string) $this->faker->randomElement($referenceNameList);
            $references[] = $this->getReference($randomReferenceName, $className);
        }

        return $references;
    }

    /**
     * Get list of reference names for a given group and class.
     *
     * @param string $referenceGroupName Reference group name
     * @param string $className          Class name
     *
     * @return string[] Array of reference names
     */
    private function getReferenceNameListByClassName(string $referenceGroupName, string $className): array
    {
        if (!array_key_exists($className, $this->referenceRepository->getIdentitiesByClass())) {
            throw new \InvalidArgumentException(sprintf('Did not find any references saved with the name "%s"', $className));
        }

        $referenceNameListByClass = array_keys($this->referenceRepository->getIdentitiesByClass()[$className]);

        if ([] === $referenceNameListByClass) {
            throw new \InvalidArgumentException(sprintf('Did not find any references saved with the name "%s"', $className));
        }

        $referenceNameList = array_filter(
            $referenceNameListByClass,
            fn ($referenceName) => preg_match_all("/^{$referenceGroupName}_\\d+$/", $referenceName)
        );

        if ([] === $referenceNameList) {
            throw new \InvalidArgumentException(sprintf('Did not find any references saved with the group name "%s" and class name "%s"', $referenceGroupName, $className));
        }

        return $referenceNameList;
    }
}
