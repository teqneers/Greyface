<?php


namespace App\Test;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webmozart\Assert\Assert;

trait DatabaseTestTrait
{
    /**
     * @param callable $init
     */
    public static function initializeDatabase(callable $init): void
    {
        $container = static::getContainer();
        $em        = $container->get(EntityManagerInterface::class);
        $em->transactional(
            static function (EntityManagerInterface $entityManager) use ($init, $container): void {
                $init($container, $entityManager);
            }
        );
        $em->clear();
    }

    /**
     * @param object[] $entities
     */
    public static function initializeDatabaseWithEntities(object ...$entities): void
    {
        self::initializeDatabase(
            static function (ContainerInterface $container, EntityManagerInterface $em) use ($entities): void {
                foreach ($entities as $entity) {
                    $repository = $em->getRepository(get_class($entity));
                    if (method_exists($repository, 'save')) {
                        $repository->save($entity);
                    } else {
                        $em->persist($entity);
                    }
                }
            }
        );
    }

    /**
     * @param object[] $entities
     * @return object[]
     */
    public static function reloadDatabaseEntities(object ...$entities): array
    {
        $container = static::getContainer();
        $em        = $container->get(EntityManagerInterface::class);
        $reloaded  = [];
        foreach ($entities as $entity) {
            $entityClass = get_class($entity);
            $id          = $em->getClassMetadata($entityClass)
                ->getIdentifierValues($entity);
            $repository  = $em->getRepository($entityClass);
            $reloaded[]  = $repository->find($id);
        }
        return $reloaded;
    }

    /**
     * @param string $entityClass
     * @param mixed  $identifier
     * @return object|null
     */
    public static function loadDatabaseEntity(string $entityClass, $identifier): ?object
    {
        $container  = static::getContainer();
        $em         = $container->get(EntityManagerInterface::class);
        $repository = $em->getRepository($entityClass);
        if (is_scalar($identifier) && method_exists($repository, 'findById')) {
            return $repository->findById($identifier);
        }
        return $em->find($entityClass, $identifier);
    }

    /**
     * @param array[] $entityIdentifiers
     * @return object[]
     */
    public static function loadDatabaseEntities(array ...$entityIdentifiers): array
    {
        $entities = [];
        Assert::allIsArray($entityIdentifiers);
        Assert::allCount($entityIdentifiers, 2);
        foreach ($entityIdentifiers as [$entityClass, $identifier]) {
            $entities[] = self::loadDatabaseEntity($entityClass, $identifier);
        }
        return $entities;
    }

    public static function clearEntityManager(): void
    {
        $container = static::getContainer();
        $em        = $container->get(EntityManagerInterface::class);
        $em->clear();
    }
}
