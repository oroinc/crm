<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\DataGridBundle\Common\Object;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Datagrid\CustomerActionPermissionProvider;

class CustomerActionPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerActionPermissionProvider
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new CustomerActionPermissionProvider($this->doctrineHelper, '\stdClass');
    }

    /**
     * @param ResultRecordInterface $record
     * @param array $actions
     * @param \PHPUnit_Framework_MockObject_MockObject|Channel $channel
     * @param array $expected
     *
     * @dataProvider permissionsDataProvider
     */
    public function testGetCustomerActionsPermissions(
        ResultRecordInterface $record,
        array $actions,
        array $expected,
        $channel = null
    ) {
        if ($channel) {
            $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
                ->disableOriginalConstructor()
                ->getMock();

            $repository
                ->expects($this->once())
                ->method('find')
                ->with($this->isType('integer'))
                ->will($this->returnValue($channel));

            $this->doctrineHelper
                ->expects($this->once())
                ->method('getEntityRepository')
                ->will($this->returnValue($repository));
        }

        $this->assertEquals($expected, $this->provider->getCustomerActionsPermissions($record, $actions));
    }

    /**
     * @return array
     */
    public function permissionsDataProvider()
    {
        return [
            'no channel id' => [
                new ResultRecord([]),
                ['create' => [], 'update' => []],
                ['create' => true, 'update' => false],
                null
            ],
            'actions are empty' => [
                new ResultRecord([]),
                [],
                [],
                null
            ],
            'two way sync disabled' => [
                new ResultRecord(['channelId' => 1]),
                ['create' => [], 'update' => []],
                ['create' => true, 'update' => false],
                $this->getChannel()

            ],
            'two way sync enabled' => [
                new ResultRecord(['channelId' => 1]),
                ['create' => [], 'update' => []],
                ['create' => true, 'update' => true],
                $this->getChannel(true)
            ],
            'missing update action' => [
                new ResultRecord(['channelId' => 1]),
                ['create' => []],
                ['create' => true],
                null
            ]
        ];
    }

    /**
     * @param bool $isTwoWaySyncEnabled
     *
     * @return Channel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getChannel($isTwoWaySyncEnabled = false)
    {
        $channel = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel');

        $settings = Object::create(['isTwoWaySyncEnabled' => $isTwoWaySyncEnabled]);
        $channel->expects($this->any())
            ->method('getSynchronizationSettings')
            ->will($this->returnValue($settings));

        return $channel;
    }
}
