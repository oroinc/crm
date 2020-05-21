<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

abstract class AbstractExportWriterTest extends WebTestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|MagentoTransportInterface
     */
    protected $transport;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();

        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel']);

        $this->transport = $this->createMock('Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
    }

    /**
     * @param string $alias
     * @param string $status
     *
     * @return JobExecution[]
     */
    protected function getJobs($alias, $status)
    {
        $qb = $this->getContainer()->get('oro_entity.doctrine_helper')
            ->getEntityRepository('AkeneoBatchBundle:JobInstance')
            ->createQueryBuilder('job');

        $qb
            ->select('job')
            ->leftJoin('job.jobExecutions', 'jobExecutions')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('job.alias', ':alias'),
                    $qb->expr()->eq('jobExecutions.status', ':status')
                )
            )
            ->setParameter('alias', $alias)
            ->setParameter('status', $status);

        return $qb->getQuery()->getResult();
    }
}
