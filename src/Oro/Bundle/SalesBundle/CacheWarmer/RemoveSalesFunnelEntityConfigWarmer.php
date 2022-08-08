<?php

namespace Oro\Bundle\SalesBundle\CacheWarmer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\EntityBundle\Tools\SafeDatabaseChecker;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Ensures that extend entity cache can be built after SalesFunnel entity removals.
 */
class RemoveSalesFunnelEntityConfigWarmer implements CacheWarmerInterface
{
    private ManagerRegistry $managerRegistry;
    private LoggerInterface $logger;
    private ApplicationState $applicationState;

    public function __construct(
        ManagerRegistry $managerRegistry,
        LoggerInterface $logger,
        ApplicationState $applicationState
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger;
        $this->applicationState = $applicationState;
    }

    /**
     * {@inheritDoc}
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function warmUp($cacheDir)
    {
        if (!$this->applicationState->isInstalled()) {
            return;
        }

        /** @var Connection $configConnection */
        $configConnection = $this->managerRegistry->getConnection('config');

        if (!SafeDatabaseChecker::tablesExist($configConnection, 'oro_entity_config')) {
            return;
        }

        $className = 'Oro\Bundle\SalesBundle\Entity\SalesFunnel';
        if (class_exists($className, false)) {
            return;
        }

        $query = new ParametrizedSqlMigrationQuery(
            'DELETE FROM oro_entity_config WHERE class_name = :class_name',
            ['class_name' => $className],
            ['class_name' => Types::STRING]
        );

        $query->setConnection($configConnection);
        $query->execute($this->logger);
    }
}
