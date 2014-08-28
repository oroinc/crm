<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Datagrid;

use OroCRM\Bundle\MarketingListBundle\Datagrid\ActionPermissionProvider;

class ActionPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ActionPermissionProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new ActionPermissionProvider();
    }

    /**
     * @param bool  $isSubscribed
     * @param array $actions
     * @param array $expected
     *
     * @dataProvider permissionsDataProvider
     */
    public function testGetMarketingListItemPermissions($isSubscribed, array $actions, array $expected)
    {
        $record = $this->getMock('Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface');

        $record
            ->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo('subscribed'))
            ->will($this->returnValue($isSubscribed));

        $this->assertEquals(
            $expected,
            $this->provider->getMarketingListItemPermissions($record, $actions)
        );
    }

    /**
     * @return array
     */
    public function permissionsDataProvider()
    {
        return [
            [false, [], ['subscribe' => true, 'unsubscribe' => false]],
            [true, [], ['subscribe' => false, 'unsubscribe' => true]],
            [true, ['view' => []], ['view' => true, 'subscribe' => false, 'unsubscribe' => true]]
        ];
    }
}
