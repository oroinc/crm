<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper;

abstract class AbstractExistingCustomerStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ImportStrategyHelper
     */
    protected $strategyHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DefaultOwnerHelper
     */
    protected $ownerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManager
     */
    protected $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecution
     */
    protected $stepExecution;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecution
     */
    protected $jobExecution;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->strategyHelper = $this
            ->getMockBuilder('Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->strategyHelper->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em));
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->ownerHelper = $this
            ->getMockBuilder('Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->jobExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stepExecution->expects($this->any())->method('getJobExecution')
            ->will($this->returnValue($this->jobExecution));
    }

    protected function tearDown()
    {
        unset(
            $this->em,
            $this->strategyHelper,
            $this->managerRegistry,
            $this->ownerHelper,
            $this->strategy,
            $this->stepExecution,
            $this->jobExecution
        );
    }

    /**
     * @return StrategyInterface
     */
    abstract protected function getStrategy();
}
