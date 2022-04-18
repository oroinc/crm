<?php
declare(strict_types=1);

namespace Oro\Bundle\CRMBundle\CacheWarmer;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CRMBundle\Migration\CleanupMagentoOneConnectorEntities;
use Oro\Bundle\EntityBundle\Tools\SafeDatabaseChecker;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveAssociationQuery;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Ensures that extend entity cache can be built after entity removals and renaming.
 */
class ExtendEntityCacheWarmer implements CacheWarmerInterface
{
    private ManagerRegistry $managerRegistry;

    private LoggerInterface $logger;

    private bool $applicationInstalled;

    public function __construct(
        ManagerRegistry $managerRegistry,
        LoggerInterface $logger,
        ?bool $applicationInstalled
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger;
        $this->applicationInstalled = (bool)$applicationInstalled;
    }

    public function isOptional()
    {
        return false;
    }

    /**
     * @throws ReflectionException
     */
    public function warmUp($cacheDir)
    {
        if (\class_exists('Oro\Bundle\MagentoBundle\OroMagentoBundle', false)) {
            return;
        }

        if (!$this->applicationInstalled) {
            return;
        }

        /** @var Connection $configConnection */
        $configConnection = $this->managerRegistry->getConnection('config');

        if (!SafeDatabaseChecker::tablesExist($configConnection, 'oro_entity_config')) {
            return;
        }

        foreach ($this->getMeaningfulQueriesToCleanup($configConnection) as $query) {
            $query->setConnection($configConnection);
            $query->execute($this->logger);
        }
    }

    private function getMeaningfulQueriesToCleanup(Connection $connection): \Generator
    {
        foreach (CleanupMagentoOneConnectorEntities::getQueries(false) as $query) {
            $classReflection = new \ReflectionObject($query);

            if ($query instanceof RemoveAssociationQuery
                && $classReflection->hasProperty('sourceEntityClass')
            ) {
                $activityClass = $classReflection->getProperty('sourceEntityClass');
                $activityClass->setAccessible(true);
                $activityClassValue = $activityClass->getValue($query);
                $activityClass->setAccessible(false);

                $tableName = $this->getEntityTableName($activityClassValue);

                if ($tableName && !SafeDatabaseChecker::tablesExist($connection, $tableName)) {
                    continue;
                }
            }

            yield $query;
        }
    }

    private function getEntityTableName(string $activityClassValue): string
    {
        /** @var EntityManager $manager */
        $manager = $this->managerRegistry->getManagerForClass($activityClassValue);
        $namingStrategy = $manager->getConfiguration()->getNamingStrategy();
        $metadata = new ClassMetadata($activityClassValue, $namingStrategy);
        $metadataDrive = $manager->getConfiguration() ? $manager->getConfiguration()->getMetadataDriverImpl() : null;
        if ($metadataDrive) {
            $metadataDrive->loadMetadataForClass($activityClassValue, $metadata);
        }

        return $metadata->getTableName();
    }
}
