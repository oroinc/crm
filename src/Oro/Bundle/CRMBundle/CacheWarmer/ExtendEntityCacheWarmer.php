<?php
declare(strict_types=1);

namespace Oro\Bundle\CRMBundle\CacheWarmer;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CRMBundle\Migration\CleanupMagentoOneConnectorEntities;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\EntityBundle\Tools\SafeDatabaseChecker;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Ensures that extend entity cache can be built after entity removals and renaming.
 */
class ExtendEntityCacheWarmer implements CacheWarmerInterface
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

    public function isOptional()
    {
        return false;
    }

    public function warmUp($cacheDir)
    {
        if (\class_exists('Oro\Bundle\MagentoBundle\OroMagentoBundle', false)) {
            return;
        }

        if (!$this->applicationState->isInstalled()) {
            return;
        }

        /** @var \Doctrine\DBAL\Connection $configConnection */
        $configConnection = $this->managerRegistry->getConnection('config');

        if (!SafeDatabaseChecker::tablesExist($configConnection, 'oro_entity_config')) {
            return;
        }

        foreach (CleanupMagentoOneConnectorEntities::getQueries(false) as $query) {
            $query->setConnection($configConnection);
            $query->execute($this->logger);
        }
    }
}
