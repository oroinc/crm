<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Job;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;

use Doctrine\ORM\EntityManager;

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

    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManager */
    protected $batchJobManager;

    protected function setUp()
    {
        $this->batchJobRegistry = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->batchJobManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->batchJobRepository = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->batchJobRepository->expects($this->any())
            ->method('getJobManager')
            ->will($this->returnValue($this->batchJobManager));

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
            $this->managerRegistry,
            [
                'sync_settings' => [
                    'initial_import_step_interval' => '1 day'
                ]
            ]
        );
    }

    /**
     * @param string $jobType
     * @param string $jobName
     * @param bool $expected
     *
     * @dataProvider applicableDataConverter
     */
    public function testIsApplicable($jobType, $jobName, $expected)
    {
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
            'not matched name' => ['import', 'jobName1', false],
            'not matched type' => ['jobType1', 'mage_customer_import', false],
            'matched' => ['import', 'mage_customer_import', true]
        ];
    }

    /**
     * @param array $configuration
     * @param mixed $expectedExecutions
     *
     * @dataProvider configurationDataProvider
     */
    public function testExecuteJob(array $configuration = [], $expectedExecutions)
    {
        $this->batchJobRepository->expects($this->exactly($expectedExecutions))
            ->method('createJobExecution')
            ->willReturnCallback(
                function ($instance) {
                    $execution = new JobExecution();
                    $execution->setJobInstance($instance);

                    return $execution;
                }
            );

        $this->executor->executeJob('jobType', 'jobName', $configuration);
    }

    /**
     * @return array
     */
    public function configurationDataProvider()
    {
        $date2 = new \DateTime();
        $date2->modify('-5 days');
        $date3 = new \DateTime();
        $date3->modify('-90 days');

        return [
            'empty' => [[], 1],
            'initial synced to defined' => [['import' => ['initialSyncedTo' => new \DateTime()]], 1],
            'start sync date defined' => [
                [
                    'import' => [
                        'initialSyncedTo' => new \DateTime(),
                        'start_sync_date' => $date2
                    ]
                ],
                5
            ],
            'start sync date another option' => [
                [
                    'import' => [
                        'initialSyncedTo' => new \DateTime(),
                        'start_sync_date' => $date3
                    ]
                ],
                90
            ]
        ];
    }
}
