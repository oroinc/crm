<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Datagrid\CustomerActionPermissionProvider;

class CustomerActionPermissionProviderTest extends AbstractTwoWaySyncActionPermissionProviderTest
{
    /**
     * @var CustomerActionPermissionProvider
     */
    protected $provider;

    protected function setUp()
    {
        parent::setUp();

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
        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('find')
            ->with($this->isType('integer'))
            ->will($this->returnValue($channel));

        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));

        $this->assertEquals($expected, $this->provider->getCustomerActionsPermissions($record, $actions));
    }

    /**
     * @return array
     */
    public function permissionsDataProvider()
    {
        return [
            'no channel id' => [
                new ResultRecord([false]),
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
            ],
            'empty settings' => [
                new ResultRecord(['channelId' => 1]),
                ['create' => [], 'update' => []],
                ['create' => true, 'update' => false],
                $this->getChannel(null)
            ]
        ];
    }
}
