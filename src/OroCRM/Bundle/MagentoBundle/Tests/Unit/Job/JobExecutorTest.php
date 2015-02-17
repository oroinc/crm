<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Job;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use OroCRM\Bundle\MagentoBundle\Job\JobExecutor;

class JobExecutorTest extends \PHPUnit_Framework_TestCase
{
    /** @var JobExecutor */
    protected $executor;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ConnectorRegistry */
    protected $batchJobRegistry;

    /** @var \PHPUnit_Framework_MockObject_MockObject|DoctrineJobRepository */
    protected $batchJobRepository;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ContextRegistry */
    protected $contextRegistry;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry */
    protected $managerRegistry;

    protected function setUp()
    {
        $this->batchJobRegistry = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->batchJobRepository = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextRegistry = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->managerRegistry = $this->getMockBuilder('Symfony\Bridge\Doctrine\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->executor = new JobExecutor(
            $this->batchJobRegistry,
            $this->batchJobRepository,
            $this->contextRegistry,
            $this->managerRegistry
        );
    }

    /**
     * @param string $jobType
     * @param string $jobName
     * @param bool $expected
     * @param array $connectors
     *
     * @dataProvider applicableDataConverter
     */
    public function testIsApplicable($jobType, $jobName, $expected, array $connectors = [])
    {
        foreach ($connectors as $connector) {
            $this->executor->addConnector($connector);
        }

        $this->assertEquals(
            $expected,
            $this->executor->isApplicable($jobType, $jobName)
        );
    }

    /**
     * @return array
     */
    public function applicableDataConverter()
    {
        return [
            'empty' => ['jobType1', 'jobName1', false],
            'not matched name' => ['jobType1', 'jobName1', false, [$this->getConnector('jobType1', 'jobName2')]],
            'not matched type' => ['jobType1', 'jobName1', false, [$this->getConnector('jobType2', 'jobName1')]],
            'matched' => ['jobType1', 'jobName1', true, [$this->getConnector('jobType1', 'jobName1')]],
            'second matched' => [
                'jobType1',
                'jobName1',
                true,
                [$this->getConnector('jobType1', 'jobName2'), $this->getConnector('jobType1', 'jobName1')]
            ]
        ];
    }

    /**
     * @param string $jobType
     * @param string $jobName
     * @return \PHPUnit_Framework_MockObject_MockObject|ConnectorInterface
     */
    protected function getConnector($jobType, $jobName)
    {
        $connector = $this->getMock('Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface');
        $connector->expects($this->once())->method('getImportJobName')->will($this->returnValue($jobName));
        $connector->expects($this->once())->method('getType')->will($this->returnValue($jobType));

        return $connector;
    }

    public function testDoJob()
    {
        $this->markTestIncomplete();
    }
}
