<?php
declare(strict_types=1);

namespace Oro\Bundle\CRMBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CRMBundle\Migration\CleanupMagentoOneConnectorEntityConfigsQuery;
use Oro\Bundle\ReportBundle\Entity\Report;
use Oro\Bundle\SegmentBundle\Entity\Segment;

/**
 * Remove reports and segments associated with Magento 1 connector entities if the connector is not present anymore.
 */
class RemoveMagentoReportsAndSegments extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $qbReports = $manager->createQueryBuilder();
        $qbReports->delete(Report::class, 'report')
            ->where($qbReports->expr()->eq('report.entity', ':entity'));

        $qbSegments = $manager->createQueryBuilder();
        $qbSegments->delete(Segment::class, 'segment')
            ->where($qbSegments->expr()->eq('segment.entity', ':entity'));

        foreach (CleanupMagentoOneConnectorEntityConfigsQuery::ENTITY_CLASSES as $className) {
            if (!\class_exists($className, false)) {
                $qbReports->setParameter('entity', $className);
                $qbReports->getQuery()->execute();
                $qbSegments->setParameter('entity', $className);
                $qbSegments->getQuery()->execute();
            }
        }
    }
}
