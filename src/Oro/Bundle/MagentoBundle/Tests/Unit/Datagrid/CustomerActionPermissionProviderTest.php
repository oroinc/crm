<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\MagentoBundle\Datagrid\CustomerActionPermissionProvider;

class CustomerActionPermissionProviderTest extends AbstractTwoWaySyncActionPermissionProviderTest
{
    /**
     * @var CustomerActionPermissionProvider
     */
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new CustomerActionPermissionProvider($this->settingsProvider, '\stdClass');
    }

    /**
     * @param ResultRecordInterface $record
     * @param array $actions
     * @param bool $isChannelApplicable
     * @param array $expected
     *
     * @dataProvider permissionsDataProvider
     */
    public function testGetCustomerActionsPermissions(
        ResultRecordInterface $record,
        array $actions,
        array $expected,
        $isChannelApplicable = false
    ) {
        $this->settingsProvider
            ->expects($this->any())
            ->method('isChannelApplicable')
            ->will($this->returnValue($isChannelApplicable));

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
                ['create' => true, 'update' => false]
            ],
            'actions are empty' => [new ResultRecord([]), [], []],
            'two way sync disabled' => [
                new ResultRecord(['channelId' => 1]),
                ['create' => [], 'update' => []],
                ['create' => true, 'update' => false],
                false

            ],
            'missing update action' => [
                new ResultRecord(['channelId' => 1]),
                ['create' => []],
                ['create' => true],
                true
            ],
            'channel applicable' => [
                new ResultRecord(['channelId' => 1]),
                ['create' => [], 'update' => []],
                ['create' => true, 'update' => true],
                true
            ]
        ];
    }
}
