<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Service;

use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use OroCRM\Bundle\MagentoBundle\Service\InformationLoader;

class InformationLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ConnectorInterface
     */
    protected $connector;

    /**
     * @var string
     */
    protected $processorAlias;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecutor
     */
    protected $jobExecutor;

    /**
     * @var InformationLoader
     */
    protected $loader;

    protected function setUp()
    {
        $this->jobExecutor = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Job\JobExecutor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->connector = $this->getMock('Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface');
        $this->processorAlias = 'test';

        $this->loader = new InformationLoader($this->jobExecutor, $this->connector, $this->processorAlias);
    }

    protected function tearDown()
    {
        unset($this->loader, $this->jobExecutor, $this->connector);
    }

    public function testLoad()
    {
        $config = ['test' => true, ProcessorRegistry::TYPE_IMPORT => ['additional_config' => true]];
        $expectedConfig = [
            ProcessorRegistry::TYPE_IMPORT => [
                'processorAlias' => $this->processorAlias,
                'entityName' => '\stdClass',
                'channel' => 1,
                'channelType' => 'mage',
                'additional_config' => true,
            ],
            'test' => true,
        ];

        $this->connector->expects($this->once())
            ->method('getImportEntityFQCN')
            ->will($this->returnValue('\stdClass'));
        $this->connector->expects($this->once())
            ->method('getImportJobName')
            ->will($this->returnValue('test_import'));

        $jobResult = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Job\JobResult')
            ->disableOriginalConstructor()
            ->getMock();
        $jobResult->expects($this->once())
            ->method('isSuccessful')
            ->will($this->returnValue(true));
        $this->jobExecutor->expects($this->once())
            ->method('executeJob')
            ->with(ProcessorRegistry::TYPE_IMPORT, 'test_import', $expectedConfig)
            ->will($this->returnValue($jobResult));

        /** @var \PHPUnit_Framework_MockObject_MockObject|Channel $channel */
        $channel = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $channel->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $channel->expects($this->once())
            ->method('getType')
            ->will($this->returnValue('mage'));

        $this->assertTrue($this->loader->load($channel, $config));
    }
}
