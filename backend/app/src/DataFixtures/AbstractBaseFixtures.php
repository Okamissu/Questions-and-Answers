<?php

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
     * Faker.
     */
    protected ?Generator $faker = null;

    /**
     * Persistence object manager.
     */
    protected ?ObjectManager $manager = null;

    /**
     * Load.
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
     * Load data.
     */
    abstract protected function loadData(): void;

    /**
     * Create many objects at once:.
     *
     *      $this->createMany(10, 'user', function(int $i) {
     *          $user = new User();
     *          $user->setFirstName('Ryan');
     *
     *           return $user;
     *      });
     *
     * @param int      $count              Number of object to create
     * @param string   $referenceGroupName Tag these created objects with this group name,
     *                                     and use this later with getRandomReference(s)
     *                                     to fetch only from this specific group
     * @param callable $factory            Defines method of creating objects
     */
    protected function createMany(int $count, string $referenceGroupName, callable $factory): void
    {
        for ($i = 0; $i < $count; ++$i) {
            /** @var object|null $entity */
            $entity = $factory($i);

            if (null === $entity) {
                throw new \LogicException('Did you forget to return the entity object from your callback to BaseFixture::createMany()?');
            }

            $this->manager->persist($entity);

            // store for usage later than groupName_#COUNT#
            $this->addReference(sprintf('%s_%d', $referenceGroupName, $i), $entity);
        }

        $this->manager->flush();
    }

    /**
     * Set random reference to the object.
     *
     * @param string $referenceGroupName Objects reference group name
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
     * Get array of objects references based on count.
     *
     * @param string $referenceGroupName Objects reference group name
     * @param string $className          Objects class name
     * @param int    $count              Number of references
     *
     * @return object[] Result
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
     * Get reference name list by class name.
     *
     * @param string $referenceGroupName Objects reference group name
     * @param string $className          Objects class name
     *
     * @return array Reference name list
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
            fn ($referenceName) => preg_match_all("/^{$referenceGroupName}_\\d+\$/", $referenceName)
        );

        if ([] === $referenceNameList) {
            throw new \InvalidArgumentException(sprintf('Did not find any references saved with the group name "%s" and class name "%s"', $referenceGroupName, $className));
        }

        return $referenceNameList;
    }
}
