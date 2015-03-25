<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use OroCRM\Bundle\MagentoBundle\Datagrid\NewsletterSubscriberPermissionProvider;

class NewsletterSubscriberPermissionProviderTest extends AbstractTwoWaySyncActionPermissionProviderTest
{
    /**
     * @var NewsletterSubscriberPermissionProvider
     */
    protected $provider;

    protected function setUp()
    {
        parent::setUp();

        $this->provider = new NewsletterSubscriberPermissionProvider($this->doctrineHelper, '\stdClass');
    }

    /**
     * @param ResultRecordInterface $record
     * @param array $actions
     * @param array $expected
     *
     * @dataProvider permissionsDataProvider
     */
    public function testGetActionsPermissions(ResultRecordInterface $record, array $actions, array $expected)
    {
        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('find')
            ->with($this->isType('integer'))
            ->will($this->returnValue($this->getChannel(true)));

        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));

        $this->assertEquals(
            $expected,
            $this->provider->getActionsPermissions($record, $actions)
        );
    }

    /**
     * @return array
     */
    public function permissionsDataProvider()
    {
        return [
            [
                new ResultRecord(['channelId' => 1]),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => true, 'unsubscribe' => false]
            ],
            [
                new ResultRecord(['channelId' => 1, 'newsletterSubscriberStatusId' => 2]),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => true, 'unsubscribe' => false]
            ],
            [
                new ResultRecord(['channelId' => 1, 'newsletterSubscriberStatusId' => 1]),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => false, 'unsubscribe' => true]
            ]
        ];
    }
}
