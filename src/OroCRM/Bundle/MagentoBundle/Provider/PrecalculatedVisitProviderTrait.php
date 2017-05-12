<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\TrackingBundle\Entity\UniqueTrackingVisit;

/**
 * @method AclHelper getAclHelper()
 * @method ManagerRegistry getManagerRegistry()
 */
trait PrecalculatedVisitProviderTrait
{
    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return int
     */
    protected function getSingleIntegerResult(QueryBuilder $queryBuilder)
    {
        try {
            return (int)$this->getAclHelper()->apply($queryBuilder)->getSingleScalarResult();
        } catch (NoResultException $ex) {
            return 0;
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param \DateTime $from
     * @param \DateTime $to
     */
    protected function applyDateLimit(QueryBuilder $queryBuilder, \DateTime $from, \DateTime $to)
    {
        if ($from && $to && $this->getDate($from) === $this->getDate($to)) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('t.firstActionTime', ':date'))
                ->setParameter('date', $this->getDate($from));
        } else {
            if ($from) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->gte('t.firstActionTime', ':from'))
                    ->setParameter('from', $this->getDate($from));
            }
            if ($to) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->lte('t.firstActionTime', ':to'))
                    ->setParameter('to', $this->getDate($to));
            }
        }
    }

    /**
     * @return QueryBuilder
     */
    protected function createUniqueVisitQueryBuilder()
    {
        $queryBuilder = $this
            ->getUniqueTrackingVisitRepository()
            ->createQueryBuilder('t');

        return $queryBuilder;
    }

    /**
     * @return bool
     */
    protected function isPrecalculatedStatisticEnabled()
    {
        return $this->configManager->get('oro_tracking.precalculated_statistic_enabled');
    }

    /**
     * @return EntityRepository
     */
    private function getUniqueTrackingVisitRepository()
    {
        return $this->getManagerRegistry()->getManagerForClass(UniqueTrackingVisit::class)
            ->getRepository(UniqueTrackingVisit::class);
    }

    /**
     * @param \DateTime $dateTime
     * @return string
     */
    private function getDate(\DateTime $dateTime)
    {
        /** @var Connection $connection */
        $connection = $this->getManagerRegistry()->getConnection();
        $dateFormat = $connection->getDatabasePlatform()->getDateFormatString();

        return $dateTime->format($dateFormat);
    }
}
